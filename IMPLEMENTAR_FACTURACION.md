# Implementar facturación electrónica AFIP/ARCA (empresa única)

## Contexto y objetivo

Este sistema (Laravel + MySQL, sistema de ventas de **una sola empresa**, ya en producción) necesita emitir **facturas electrónicas con CAE** (factura A/B/C) y **notas de crédito** a través de una API externa ya construida y funcionando: un microservicio hermano que habla con AFIP/ARCA por nosotros. Nosotros **nunca** implementamos lógica de AFIP directamente — somos 100% cliente HTTP de esa API.

Esta implementación ya existe, probada en producción, en un proyecto multiempresa hermano (Laravel). Esta guía es la adaptación exacta de esa implementación para un sistema de **una sola empresa** — te doy el diseño completo, verificado contra el código real (no aproximado), para que lo repliques acá.

**Antes de escribir una sola línea**: confirmá con el usuario (o inspeccionando el proyecto) cuál es el stack real de este sistema. Si no es Laravel, todo lo de abajo sigue aplicando como **diseño** (los endpoints de la API externa, el orden de pasos, las reglas de negocio, los nombres de campos) pero tenés que traducir la implementación concreta (migraciones, Eloquent, etc.) a tu stack. Si es Laravel, podés seguir esto casi literal.

**Diferencia clave respecto al proyecto multiempresa original**: ahí, cada tabla (`credenciales_facturacion`, `facturas`, `notas_credito`) tiene `empresa_id` porque conviven muchas empresas. Acá hay **una sola empresa**, así que:
- No hace falta `empresa_id` en ninguna tabla nueva.
- La configuración de facturación (condición fiscal, habilitar/deshabilitar, comprobante por defecto, etc.) no vive en una tabla `empresas` — vive donde ya tengas la configuración general del negocio (una tabla `configuracion`/`settings` de una sola fila, o columnas en tu tabla de "negocio" si ya tenés una). Si no tenés ningún lugar así todavía, creá una tabla `configuracion_facturacion` de una sola fila (singleton) — ver más abajo.
- Las credenciales de la API (`api_key`, `webhook_secret`, `punto_venta`, etc.) van en una tabla `credencial_facturacion` (singular, sin `empresa_id`, puede ser directamente de una sola fila o con un `id` fijo=1).

## 0. Qué te tienen que dar antes de empezar

El dueño de la API de facturación (el otro proyecto, `ModuloAPI_Facturacion_ARCA` o como se llame en tu caso) te tiene que dar:
- `FACTURACION_API_URL` — URL base de esa API (ej. `https://facturacion.tudominio.com` o `http://127.0.0.1:8001` en local).
- `FACTURACION_PLATFORM_KEY` — una "platform key" (`fpk_...`), alto privilegio, server-to-server, **nunca** al navegador. Solo sirve para dar de alta la empresa y pedir el link de onboarding.
- `FACTURACION_ONBOARDING_WEBHOOK_SECRET` — secreto para verificar la firma del webhook de onboarding (Paso 2 más abajo).

La **API Key por empresa** y el **webhook secret de comprobantes** (distinto del de onboarding) **no se piden a mano** — llegan solas por webhook cuando el dueño del negocio termina de subir su certificado AFIP (.crt/.key) del lado de esa otra plataforma.

## 1. Variables de entorno y config

`.env`:
FACTURACION_API_URL=
FACTURACION_PLATFORM_KEY=
FACTURACION_ONBOARDING_WEBHOOK_SECRET=



`config/services.php`:
```php
'facturacion' => [
    'url' => env('FACTURACION_API_URL'),
    'platform_key' => env('FACTURACION_PLATFORM_KEY'),
    'onboarding_webhook_secret' => env('FACTURACION_ONBOARDING_WEBHOOK_SECRET'),
],
Mientras FACTURACION_API_URL esté vacía, todo el módulo tiene que quedar en no-op silencioso (no romper nada, solo no facturar) — es el mismo criterio que ya usa el proyecto original para que el sistema funcione completo sin facturación configurada todavía.

2. Modelo de datos
2.1. Configuración general (si no tenés ya una tabla de settings)

Schema::create('configuracion_facturacion', function (Blueprint $table) {
    $table->id();
    $table->string('condicion_fiscal')->nullable(); // 'responsable_inscripto' | 'monotributista' | null
    $table->boolean('factura_habilitada')->default(true);
    $table->string('comprobante_predeterminado')->default('remito'); // 'remito' | 'A' | 'B' | 'C'
    $table->string('formato_comprobante')->default('ticket'); // 'ticket' | 'a4'
    $table->boolean('mostrar_modal_comprobante')->default(true);
    $table->timestamps();
});
Esta tabla tiene una sola fila siempre (patrón singleton) — creála con un seeder o firstOrCreate() la primera vez que se accede. Si ya tenés una tabla de configuración general del negocio, agregá estas columnas ahí en vez de crear una tabla nueva.

Por qué estos campos, uno por uno:

condicion_fiscal: define si la empresa puede facturar y qué letra le corresponde. responsable_inscripto → puede elegir Factura A o B. monotributista → solo Factura C (no discrimina IVA, ver más abajo). null → no puede facturar, solo Remito.
factura_habilitada: interruptor operativo independiente de condicion_fiscal — permite pausar la facturación temporalmente (ej. un problema puntual con AFIP) sin desarmar la condición fiscal ni el certificado ya configurado. Activado por defecto.
comprobante_predeterminado: qué viene preseleccionado en el formulario de cobro (el cajero siempre puede cambiarlo a mano). Acá es donde configurás "por defecto Factura B": el valor de este campo sería 'B'. Pero OJO — nunca lo uses crudo, siempre a través del método de abajo (comprobantePredeterminadoEfectivo()) que lo valida contra el estado actual.
formato_comprobante: layout de impresión — ticket (80mm, impresora térmica) o a4 (hoja entera). Aplica a factura, remito, nota de crédito y comprobantes de pago de crédito por igual.
mostrar_modal_comprobante: si al confirmar una venta aparece un cartel preguntando si querés descargar el PDF.
Método clave, replicalo tal cual (evita que un valor guardado que dejó de ser válido rompa el formulario de cobro — ej. se cambió la condición fiscal después de configurar "Factura B" por defecto):


public function comprobantePredeterminadoEfectivo(): string
{
    if (! $this->facturacionHabilitada()) { // puedeFacturar() && factura_habilitada
        return 'remito';
    }

    if ($this->esResponsableInscripto() && in_array($this->comprobante_predeterminado, ['A', 'B'], true)) {
        return $this->comprobante_predeterminado;
    }

    if (! $this->esResponsableInscripto() && $this->comprobante_predeterminado === 'C') {
        return $this->comprobante_predeterminado;
    }

    return 'remito';
}

public function puedeFacturar(): bool
{
    return in_array($this->condicion_fiscal, ['responsable_inscripto', 'monotributista'], true);
}

public function facturacionHabilitada(): bool
{
    return $this->puedeFacturar() && $this->factura_habilitada;
}

public function esResponsableInscripto(): bool
{
    return $this->condicion_fiscal === 'responsable_inscripto';
}

public function usaFormatoA4(): bool
{
    return $this->formato_comprobante === 'a4';
}
2.2. credencial_facturacion (credenciales de la API, cifradas)

Schema::create('credencial_facturacion', function (Blueprint $table) {
    $table->id();
    $table->string('ambiente'); // 'homologacion' | 'produccion'
    $table->unsignedInteger('punto_venta')->nullable();
    $table->text('api_key')->nullable();        // cast 'encrypted'
    $table->text('webhook_secret')->nullable();  // cast 'encrypted'
    $table->string('estado')->default('pendiente_onboarding'); // 'pendiente_onboarding' | 'activa'
    $table->string('onboarding_id')->nullable(); // STRING, no numérico — ver nota abajo
    $table->unsignedBigInteger('empresa_externa_id')->nullable();
    $table->timestamps();
});
Por qué onboarding_id es string y no unsignedBigInteger: la spec de la API externa no garantiza que ese id sea siempre numérico. En el proyecto original esto empezó como unsignedBigInteger y causó un 500 real (PDOException) en el webhook de onboarding — justo el webhook que activa la facturación real de una empresa — el día que la API externa mandó un id no numérico. Arrancá directamente con string, no repitas ese error.

Modelo:


class CredencialFacturacion extends Model
{
    public const ESTADO_PENDIENTE_ONBOARDING = 'pendiente_onboarding';
    public const ESTADO_ACTIVA = 'activa';

    protected $table = 'credencial_facturacion';
    protected $fillable = ['ambiente', 'punto_venta', 'api_key', 'webhook_secret', 'estado', 'onboarding_id', 'empresa_externa_id'];
    protected $hidden = ['api_key', 'webhook_secret'];

    protected function casts(): array
    {
        return ['api_key' => 'encrypted', 'webhook_secret' => 'encrypted'];
    }

    public function estaActiva(): bool
    {
        return $this->estado === self::ESTADO_ACTIVA && $this->api_key !== null && $this->punto_venta !== null;
    }
}
También de una sola fila (singleton) — una sola empresa, un solo punto de venta por ahora.

2.3. facturas

Schema::create('facturas', function (Blueprint $table) {
    $table->id();
    $table->foreignId('pedido_id')->nullable()->constrained('pedidos')->nullOnDelete(); // o tu tabla de ventas
    $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
    $table->string('cliente_nombre');
    $table->string('cliente_cuit')->nullable();
    $table->string('tipo_factura'); // 'A' | 'B' | 'C'

    $table->string('numero_comprobante')->nullable();      // "PPPP-NNNNNNNN", llega de la API
    $table->string('comprobante_externo_id')->nullable();  // id interno de la API externa — NO el cae, NO el numero. Se usa para la NC.
    $table->string('cae')->nullable();
    $table->date('cae_vencimiento')->nullable();
    $table->string('estado')->default('pendiente'); // pendiente | pendiente_afip | aprobada | rechazada | error
    $table->text('error_mensaje')->nullable();

    $table->timestamps();
});
Modelo:


class Factura extends Model
{
    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_PENDIENTE_AFIP = 'pendiente_afip'; // esperando que se resuelva un 202 (async)
    public const ESTADO_APROBADA = 'aprobada';
    public const ESTADO_RECHAZADA = 'rechazada';
    public const ESTADO_ERROR = 'error'; // error de ESTE lado (red, config, etc.) — no es rechazo de AFIP

    protected $fillable = ['pedido_id', 'cliente_id', 'cliente_nombre', 'cliente_cuit', 'tipo_factura', 'numero_comprobante', 'comprobante_externo_id', 'cae', 'cae_vencimiento', 'estado', 'error_mensaje'];
    protected function casts(): array { return ['cae_vencimiento' => 'date']; }

    public function notaCredito(): HasOne { return $this->hasOne(NotaCredito::class); }
    public function fallo(): bool { return in_array($this->estado, [self::ESTADO_RECHAZADA, self::ESTADO_ERROR], true); }
    public function estaAprobada(): bool { return $this->estado === self::ESTADO_APROBADA && $this->cae !== null; }
}
2.4. notas_credito

Schema::create('notas_credito', function (Blueprint $table) {
    $table->id();
    // Única por factura: 1 NC por factura, 100% del importe o nada — no hay NC parciales en este diseño.
    $table->foreignId('factura_id')->unique()->constrained('facturas')->cascadeOnDelete();
    $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
    $table->decimal('importe_acreditado', 10, 2);
    $table->text('motivo');

    $table->string('numero_comprobante')->nullable();
    $table->string('comprobante_externo_id')->nullable();
    $table->string('cae')->nullable();
    $table->date('cae_vencimiento')->nullable();
    $table->string('estado')->default('pendiente');
    $table->text('error_mensaje')->nullable();

    $table->timestamps();
});
Modelo: mismos estados y mismo vocabulario que Factura (constantes propias, no acopladas), factura() BelongsTo, creadoPor() BelongsTo a User, fallo() igual.

2.5. Tu tabla de ventas/pedidos necesita

$table->string('tipo_comprobante')->nullable(); // 'remito' | 'factura'
$table->foreignId('factura_id')->nullable()->constrained('facturas')->nullOnDelete();
tipo_comprobante='factura' + factura_id apuntando a la fila de facturas con tipo_factura A/B/C adentro. Si tu venta se puede re-facturar (editar una venta ya facturada), guardá que factura_id pase a apuntar a una factura NUEVA y la vieja quede histórica — no la borres.

3. Servicios
3.1. FacturacionApiClient — capa HTTP pura, sin lógica de negocio

class FacturacionApiClient
{
    public function configurada(): bool
    {
        return filled(config('services.facturacion.url'));
    }

    /**
     * Paso 1: dar de alta la empresa y pedir el link de onboarding.
     * @return array{empresa_id: int, onboarding_id: string, ambiente: string, onboarding_url: string, expira_en: string}
     */
    public function altaEmpresa(array $datos): array
    {
        $this->asegurarConfigurada();

        $response = Http::withToken(config('services.facturacion.platform_key'))
            ->acceptJson()
            ->post($this->url('/api/platform/v1/empresas'), $datos);

        if ($response->status() === 409) {
            throw new CuitYaRegistradoException('Este CUIT ya está registrado con otra integración o cargado a mano del lado de la API de facturación. Contactá al administrador de esa API para resolverlo.');
        }

        return $response->throw()->json();
    }

    /** Corrige razón social/CUIT/email antes de que se suba el certificado. */
    public function corregirEmpresa(int $empresaExternaId, array $datos): array
    {
        $this->asegurarConfigurada();

        $response = Http::withToken(config('services.facturacion.platform_key'))
            ->acceptJson()
            ->patch($this->url("/api/platform/v1/empresas/{$empresaExternaId}"), $datos);

        if ($response->status() === 409) {
            $mensaje = (string) ($response->json('message') ?? '');
            // Los dos casos de 409 se distinguen SOLO por el texto del mensaje, no hay código aparte.
            if (str_contains(mb_strtolower($mensaje), 'certificado')) {
                throw new CertificadoYaValidadoException($mensaje ?: 'Esta empresa ya tiene un certificado AFIP validado — no se puede corregir por acá.');
            }
            throw new CuitYaRegistradoException($mensaje ?: 'Ese CUIT ya pertenece a otra empresa registrada.');
        }
        if ($response->status() === 404) {
            throw new \RuntimeException('No se encontró esa empresa en la API de facturación.');
        }
        if ($response->status() === 422) {
            throw new \RuntimeException((string) ($response->json('message') ?? 'Datos inválidos.'));
        }

        return $response->throw()->json();
    }

    /** Paso 3: emitir. NO tira excepción por 4xx/5xx — el caller mapea el status code. */
    public function emitirComprobante(string $apiKey, string $idempotencyKey, array $body): Response
    {
        $this->asegurarConfigurada();

        return Http::withToken($apiKey)
            ->withHeaders(['Idempotency-Key' => $idempotencyKey])
            ->acceptJson()
            ->timeout(15)
            ->post($this->url('/api/v1/comprobantes'), $body);
    }

    public function consultarComprobante(string $apiKey, int|string $comprobanteId): Response
    {
        $this->asegurarConfigurada();
        return Http::withToken($apiKey)->acceptJson()->timeout(15)->get($this->url("/api/v1/comprobantes/{$comprobanteId}"));
    }

    private function url(string $path): string { return rtrim((string) config('services.facturacion.url'), '/').$path; }
    private function asegurarConfigurada(): void
    {
        if (! $this->configurada()) throw new \RuntimeException('FACTURACION_API_URL no está configurada.');
    }
}
Dos excepciones chiquitas que necesitás (App\Services\Facturacion\Exceptions\):


class CuitYaRegistradoException extends \RuntimeException {}
class CertificadoYaValidadoException extends \RuntimeException {}
3.2. OnboardingFacturacionService — Paso 1 orquestado

class OnboardingFacturacionService
{
    public function __construct(private readonly FacturacionApiClient $client) {}

    public function iniciar(string $razonSocial, string $cuit, string $emailContacto, string $ambiente): string
    {
        $respuesta = $this->client->altaEmpresa([
            'razon_social' => $razonSocial,
            'cuit' => $cuit,
            'email_contacto' => $emailContacto,
            'ambiente' => $ambiente,
            'webhook_url' => route('webhooks.facturacion.comprobantes'),
        ]);

        CredencialFacturacion::query()->updateOrCreate(
            ['id' => 1], // singleton — una sola fila siempre
            [
                'ambiente' => $ambiente,
                'estado' => CredencialFacturacion::ESTADO_PENDIENTE_ONBOARDING,
                'empresa_externa_id' => $respuesta['empresa_id'] ?? null,
                'onboarding_id' => $respuesta['onboarding_id'] ?? null,
            ],
        );

        return $respuesta['onboarding_url'];
    }

    public function corregirDatos(CredencialFacturacion $credencial, array $datos): void
    {
        if (! $credencial->empresa_externa_id) {
            throw new \RuntimeException('Todavía no se inició el alta en la API de facturación.');
        }
        $this->client->corregirEmpresa($credencial->empresa_externa_id, $datos);
    }
}
onboarding_url vence en 30 minutos y es de un solo uso — nunca lo pre-generes ni lo guardes para después: pedilo y redirigí en el mismo request.

Importante — esto es "dar de alta" Y "renovar certificado" al mismo tiempo: no hay un endpoint separado para renovar. Volver a disparar iniciar() con una empresa que ya tiene credencialFacturacion->estaActiva() es la forma soportada de renovar (cambio de dueño, certificado vencido, clave comprometida). La API Key anterior se revoca del otro lado recién cuando se completa el onboarding nuevo — hasta ese momento la vieja sigue funcionando sin cortes. No bloquees este caso en el controller.

3.3. CalculadoraIva

class CalculadoraIva
{
    private const ID_ALICUOTA_GENERAL = 5;

    /** Desarma un total que YA incluye IVA, asumiendo alícuota general 21% para todo. */
    public function calcular(float $importeTotal): array
    {
        $importeNeto = round($importeTotal / 1.21, 2);
        $importeIva = round($importeTotal - $importeNeto, 2);
        return ['importe_neto' => $importeNeto, 'importe_iva' => $importeIva, 'importe_total' => round($importeTotal, 2)];
    }

    public function detalleParaComprobante(float $importeTotal): array
    {
        $c = $this->calcular($importeTotal);
        return [['Id' => self::ID_ALICUOTA_GENERAL, 'BaseImp' => $c['importe_neto'], 'Importe' => $c['importe_iva']]];
    }

    /**
     * Un Monotributista NO discrimina IVA ante AFIP (va incluido en la cuota fija).
     * Factura C: neto = total, iva = 0, sin array de detalle.
     */
    public function sinDiscriminar(float $importeTotal): array
    {
        return ['importe_neto' => round($importeTotal, 2), 'importe_iva' => 0.0, 'importe_total' => round($importeTotal, 2)];
    }
}
⚠️ Esto asume alícuota única 21% para todo. Si en algún momento vendés productos exentos o con otra alícuota, esto no alcanza — hay que discriminar por ítem.

3.4. AplicaRespuestaComprobante — trait compartido Factura/NotaCredito
Ambas emiten contra el mismo endpoint y guardan el mismo set de campos — este trait mapea la respuesta HTTP a estado:


trait AplicaRespuestaComprobante
{
    private function aplicarRespuestaComun(Model $comprobante, Response $response, string $estadoAprobada, string $estadoPendienteAfip, string $estadoRechazada, string $estadoError): void
    {
        $json = $response->json() ?? [];

        match ($response->status()) {
            200 => $comprobante->update([
                'estado' => $estadoAprobada,
                'cae' => $json['cae'] ?? null,
                'cae_vencimiento' => $json['cae_vencimiento'] ?? null,
                'numero_comprobante' => $json['numero_comprobante'] ?? null,
                'comprobante_externo_id' => $json['id'] ?? $json['comprobante_id'] ?? null,
                'error_mensaje' => null,
            ]),
            202 => $comprobante->update([
                'estado' => $estadoPendienteAfip,
                'comprobante_externo_id' => $json['id'] ?? $json['comprobante_id'] ?? null,
                'error_mensaje' => null,
            ]),
            422 => $comprobante->update([
                'estado' => $estadoRechazada,
                'error_mensaje' => ($json['error_mensaje'] ?? null) ?: 'Rechazado por AFIP.',
            ]),
            401 => $comprobante->update([
                'estado' => $estadoError,
                'error_mensaje' => 'La credencial de facturación fue rechazada. Contactá a soporte.',
            ]),
            default => $comprobante->update([
                'estado' => $estadoError,
                'error_mensaje' => "Respuesta inesperada del servicio de facturación (HTTP {$response->status()}).",
            ]),
        };
    }
}
3.5. EmisionComprobanteService — Paso 3, emitir Factura
Contrato no negociable: emitir() NUNCA tira excepción. Una venta ya cobrada es dinero real — la facturación es un paso posterior best-effort. Cualquier falla queda grabada en la propia Factura (estado + error_mensaje) y logueada, nunca revierte ni bloquea la venta.


class EmisionComprobanteService
{
    use AplicaRespuestaComprobante;

    private const MAPA_TIPO_COMPROBANTE = ['A' => 1, 'B' => 6, 'C' => 11];

    public function __construct(private readonly FacturacionApiClient $client, private readonly CalculadoraIva $iva) {}

    public function emitir(Factura $factura, $venta): void // $venta = tu Pedido/Orden
    {
        if ($factura->cae !== null) return; // ya emitida, nada que hacer

        if (! $this->client->configurada()) {
            Log::info("Facturación no configurada: factura #{$factura->id} queda pendiente.");
            return;
        }

        $credencial = CredencialFacturacion::first(); // singleton

        if (! $credencial || ! $credencial->estaActiva()) {
            $factura->update(['estado' => Factura::ESTADO_ERROR, 'error_mensaje' => 'Todavía no se terminó de configurar la facturación electrónica (onboarding).']);
            return;
        }

        $tipoComprobante = self::MAPA_TIPO_COMPROBANTE[$factura->tipo_factura] ?? null;
        if ($tipoComprobante === null) {
            $factura->update(['estado' => Factura::ESTADO_ERROR, 'error_mensaje' => "Tipo de factura desconocido: \"{$factura->tipo_factura}\"."]);
            return;
        }

        $docTipo = 99; $docNro = null; // 99 = Consumidor Final
        if (filled($factura->cliente_cuit)) { $docTipo = 80; $docNro = $factura->cliente_cuit; } // 80 = CUIT

        // El importe que se informa a AFIP es el TOTAL de la venta, no lo que
        // efectivamente entró a caja — si tu sistema tiene "fiado"/crédito parcial,
        // usá el total de la venta, no el monto cobrado, o facturás de menos.
        $importe = (float) $venta->total;
        $esMonotributo = $factura->tipo_factura === 'C';
        $calculo = $esMonotributo ? $this->iva->sinDiscriminar($importe) : $this->iva->calcular($importe);

        $body = [
            'punto_venta' => $credencial->punto_venta,
            'tipo_comprobante' => $tipoComprobante,
            'concepto' => 1, // 1 = Productos
            'cliente_doc_tipo' => $docTipo,
            'cliente_doc_nro' => $docNro,
            'moneda' => 'PES',
            'cotizacion' => 1,
            'importe_neto' => $calculo['importe_neto'],
            'importe_iva' => $calculo['importe_iva'],
            'importe_total' => $calculo['importe_total'],
            'iva_detalle' => $esMonotributo ? [] : $this->iva->detalleParaComprobante($importe),
        ];

        try {
            // Idempotency-Key: algo único y estable por venta — "venta-{id}" alcanza,
            // así un reintento (ej. doble click) nunca duplica el comprobante.
            $response = $this->client->emitirComprobante($credencial->api_key, 'venta-'.$venta->id, $body);
        } catch (\Throwable $e) {
            Log::error("Error de red emitiendo comprobante para factura #{$factura->id}", ['exception' => $e]);
            $factura->update(['estado' => Factura::ESTADO_ERROR, 'error_mensaje' => 'Error de comunicación con el servicio de facturación. Podés reintentar.']);
            return;
        }

        $this->aplicarRespuestaComun($factura, $response, Factura::ESTADO_APROBADA, Factura::ESTADO_PENDIENTE_AFIP, Factura::ESTADO_RECHAZADA, Factura::ESTADO_ERROR);
    }

    /** Aplica el resultado que llega por el webhook de comprobantes, cuando se resuelve un 202 pendiente. */
    public function aplicarResultadoWebhook(Factura $factura, array $payload): void
    {
        $estado = $payload['estado'] ?? null;

        if ($estado === 'aprobado' || filled($payload['cae'] ?? null)) {
            $factura->update([
                'estado' => Factura::ESTADO_APROBADA,
                'cae' => $payload['cae'] ?? $factura->cae,
                'cae_vencimiento' => $payload['cae_vencimiento'] ?? $factura->cae_vencimiento,
                'numero_comprobante' => $payload['numero_comprobante'] ?? $factura->numero_comprobante,
                'comprobante_externo_id' => $payload['comprobante_id'] ?? $payload['id'] ?? $factura->comprobante_externo_id,
                'error_mensaje' => null,
            ]);
            return;
        }

        if ($estado === 'rechazado') {
            $factura->update(['estado' => Factura::ESTADO_RECHAZADA, 'error_mensaje' => ($payload['error_mensaje'] ?? null) ?: 'Rechazado por AFIP.']);
            return;
        }

        Log::warning('Webhook de comprobantes con estado no reconocido.', ['factura_id' => $factura->id, 'payload' => $payload]);
    }
}
Dónde se llama emitir(): justo después de confirmar el cobro de la venta, fuera de la transacción de base de datos que cerró la venta (sin locks activos) — nunca adentro.

3.6. EmisionNotaCreditoService — Paso 3, emitir Nota de Crédito
Mismo endpoint que la factura (POST /api/v1/comprobantes), mismo contrato de "nunca tira excepción". Diferencias:


class EmisionNotaCreditoService
{
    use AplicaRespuestaComprobante;

    // La letra tiene que coincidir con la de la factura que se acredita.
    // AFIP rechaza una NC A contra una Factura B.
    private const MAPA_TIPO_NOTA_CREDITO = ['A' => 3, 'B' => 8, 'C' => 13];

    public function __construct(private readonly FacturacionApiClient $client, private readonly CalculadoraIva $iva) {}

    public function emitir(NotaCredito $notaCredito): void
    {
        if ($notaCredito->cae !== null) return;
        if (! $this->client->configurada()) { Log::info("..."); return; }

        $factura = $notaCredito->factura;
        $credencial = CredencialFacturacion::first();

        if (! $credencial || ! $credencial->estaActiva()) {
            $notaCredito->update(['estado' => NotaCredito::ESTADO_ERROR, 'error_mensaje' => '...']);
            return;
        }
        if (! $factura->comprobante_externo_id) {
            $notaCredito->update(['estado' => NotaCredito::ESTADO_ERROR, 'error_mensaje' => 'La factura original no tiene comprobante_externo_id — no se puede acreditar.']);
            return;
        }
        // El (int) cast trunca silenciosamente cualquier string no numérico a 0 —
        // sin este chequeo, mandarías "comprobante_asociado_id": 0 a AFIP en vez
        // de fallar acá con un error claro.
        if (! is_numeric($factura->comprobante_externo_id)) {
            $notaCredito->update(['estado' => NotaCredito::ESTADO_ERROR, 'error_mensaje' => "comprobante_externo_id no numérico — no se puede acreditar."]);
            return;
        }

        $tipoComprobante = self::MAPA_TIPO_NOTA_CREDITO[$factura->tipo_factura] ?? null;
        // ... mismo chequeo null que la factura ...

        $docTipo = 99; $docNro = null;
        if (filled($factura->cliente_cuit)) { $docTipo = 80; $docNro = $factura->cliente_cuit; }

        $importe = (float) $notaCredito->importe_acreditado;
        $esMonotributo = $factura->tipo_factura === 'C';
        $calculo = $esMonotributo ? $this->iva->sinDiscriminar($importe) : $this->iva->calcular($importe);

        $body = [
            'punto_venta' => $credencial->punto_venta,
            'tipo_comprobante' => $tipoComprobante,
            'concepto' => 1,
            'cliente_doc_tipo' => $docTipo,
            'cliente_doc_nro' => $docNro,
            'moneda' => 'PES',
            'cotizacion' => 1,
            'importe_neto' => $calculo['importe_neto'],
            'importe_iva' => $calculo['importe_iva'],
            'importe_total' => $calculo['importe_total'],
            'iva_detalle' => $esMonotributo ? [] : $this->iva->detalleParaComprobante($importe),
            // El id INTERNO que devolvió la API al emitir la factura original — NO el numero, NO el cae.
            'comprobante_asociado_id' => (int) $factura->comprobante_externo_id,
        ];

        try {
            $response = $this->client->emitirComprobante($credencial->api_key, 'nc-'.$notaCredito->id, $body);
        } catch (\Throwable $e) {
            // ... mismo manejo que la factura ...
        }

        $this->aplicarRespuestaComun($notaCredito, $response, NotaCredito::ESTADO_APROBADA, NotaCredito::ESTADO_PENDIENTE_AFIP, NotaCredito::ESTADO_RECHAZADA, NotaCredito::ESTADO_ERROR);
    }
}
Quién dispara la anulación: tu equivalente a "anular venta" — primero hace la reversión LOCAL (devolver stock si corresponde, crear la NotaCredito en estado pendiente por el 100% del importe facturado), todo eso adentro de una transacción, y recién DESPUÉS (fuera de la transacción) llama a EmisionNotaCreditoService::emitir(). Ejemplo:


public function anularVenta($venta, $motivo): NotaCredito
{
    return DB::transaction(function () use ($venta, $motivo) {
        $ventaActual = Venta::where('id', $venta->id)->lockForUpdate()->first();

        if (! $ventaActual->estaCobrada()) throw new \RuntimeException('Solo se pueden anular ventas cobradas.');

        $factura = $ventaActual->factura;
        if (! $factura || ! $factura->estaAprobada()) throw new \RuntimeException('Solo se puede anular una venta con factura aprobada (con CAE).');
        if ($factura->notaCredito) throw new \RuntimeException('Esta factura ya tiene una nota de crédito.');

        // devolver stock acá si corresponde...

        return NotaCredito::create([
            'factura_id' => $factura->id,
            'creado_por' => $usuario->id,
            'importe_acreditado' => $ventaActual->total, // el total FACTURADO, no lo cobrado
            'motivo' => $motivo,
        ]);
    });
}
Y en el controller: $notaCredito = $procesador->anularVenta(...); $emisorNC->emitir($notaCredito); — el segundo paso fuera de la transacción.

Alcance de este diseño: 100% del importe o nada, no hay notas de crédito parciales. Si necesitás NC parciales, es un cambio de diseño aparte (nuevo campo, nueva validación de monto ≤ saldo de la factura).

4. Controllers y rutas
4.1. Iniciar/renovar el onboarding (dueño del negocio, rol admin)

class FacturacionElectronicaController extends Controller
{
    public function iniciar(Request $request, OnboardingFacturacionService $onboarding): RedirectResponse
    {
        $config = ConfiguracionFacturacion::first(); // o tu equivalente
        if (! $config->puedeFacturar()) abort(403);

        $datos = $request->validate(['ambiente' => ['required', Rule::in(['homologacion', 'produccion'])]]);

        try {
            $onboardingUrl = $onboarding->iniciar(
                razonSocial: /* tu dato de razón social */,
                cuit: /* tu dato de CUIT */,
                emailContacto: /* tu dato de email */,
                ambiente: $datos['ambiente'],
            );
        } catch (CuitYaRegistradoException $e) {
            return back()->withErrors(['facturacion' => $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['facturacion' => 'No se pudo iniciar la configuración. Probá de nuevo en unos minutos.']);
        }

        return redirect()->away($onboardingUrl);
    }
}
Ruta: POST /configuracion/facturacion-electronica, protegida por rol admin.

Importante sobre el formulario del lado del navegador: el <form> que llama a esto necesita target="_blank" — el onboarding se abre en pestaña nueva (ahí es donde el dueño sube el .crt/.key), la pestaña original se queda mostrando la Configuración para que la actualice y vea el estado nuevo cuando vuelva.

4.2. Webhook de onboarding — Paso 2, lo recibimos nosotros
Ruta pública, sin sesión/auth de Laravel, la autenticidad la garantiza la firma HMAC:


// routes/api.php (o donde tengas tus webhooks públicos)
Route::post('/webhooks/facturacion/onboarding', FacturacionOnboardingWebhookController::class);

class FacturacionOnboardingWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $secret = config('services.facturacion.onboarding_webhook_secret');

        if (! $secret || ! $this->firmaValida($request, $secret)) {
            abort(401);
        }

        $payload = $request->validate([
            'onboarding_id' => ['required'],
            'empresa_id' => ['required'],
            'cuit' => ['required', 'string'],
            'ambiente' => ['required', 'string'],
            'punto_venta' => ['required', 'integer'],
            'renovacion' => ['nullable', 'boolean'],
            'api_key' => ['required', 'string'],
            'comprobantes_webhook_secret' => ['nullable', 'string'],
        ]);

        // Una sola empresa acá — no hace falta "localizar" nada, siempre es el singleton.
        CredencialFacturacion::query()->updateOrCreate(
            ['id' => 1],
            [
                'ambiente' => $payload['ambiente'],
                'punto_venta' => $payload['punto_venta'],
                'api_key' => $payload['api_key'],
                'webhook_secret' => $payload['comprobantes_webhook_secret'] ?? null,
                'estado' => CredencialFacturacion::ESTADO_ACTIVA,
                'onboarding_id' => $payload['onboarding_id'],
                'empresa_externa_id' => $payload['empresa_id'],
            ],
        );

        return response()->noContent();
    }

    private function firmaValida(Request $request, string $secret): bool
    {
        $firmaEsperada = hash_hmac('sha256', $request->getContent(), $secret);
        $firmaRecibida = (string) $request->header('X-Signature');
        return $firmaRecibida !== '' && hash_equals($firmaEsperada, $firmaRecibida);
    }
}
Nota importante — esto SÍ es más simple que el proyecto multiempresa original: en el proyecto original, este webhook necesita identificar a cuál de las N empresas locales corresponde (3 niveles de precisión: onboarding_id → empresa_externa_id → CUIT, por la posibilidad de IDs ambiguos entre empresas). Acá, al ser una sola empresa, no hace falta nada de eso — el updateOrCreate(['id' => 1], ...) ya alcanza. Guardá igual onboarding_id/empresa_externa_id para cuando renueves el certificado más adelante (mismo webhook, mismo endpoint).

Renovación de certificado: payload['renovacion'] puede venir en true — no necesita manejo especial del lado nuestro más allá de loguear la distinción si querés; el updateOrCreate ya reemplaza la key vieja por la nueva. La API externa revoca la key anterior recién cuando este webhook llega, nunca antes.

4.3. Webhook de comprobantes — avisa cuando se resuelve un 202 pendiente

Route::post('/webhooks/facturacion/comprobantes', FacturacionComprobantesWebhookController::class);

class FacturacionComprobantesWebhookController extends Controller
{
    public function __invoke(Request $request, EmisionComprobanteService $emisor): Response
    {
        $factura = $this->localizarFactura($request->input('comprobante_id') ?? $request->input('id'), $request->input('idempotency_key'));

        if (! $factura) {
            Log::warning('Webhook de comprobantes: no se pudo identificar la factura.', $request->all());
            return response()->noContent();
        }

        // La firma se verifica DESPUÉS de identificar la factura, con el webhook_secret
        // de la credencial — no antes, porque todavía no tenemos esa credencial.
        $secret = CredencialFacturacion::first()?->webhook_secret;

        if (! $secret || ! $this->firmaValida($request, $secret)) {
            abort(401);
        }

        $emisor->aplicarResultadoWebhook($factura, $request->all());

        return response()->noContent();
    }

    private function localizarFactura(mixed $comprobanteExternoId, ?string $idempotencyKey): ?Factura
    {
        if ($comprobanteExternoId) {
            $factura = Factura::where('comprobante_externo_id', $comprobanteExternoId)->first();
            if ($factura) return $factura;
        }
        if ($idempotencyKey && str_starts_with($idempotencyKey, 'venta-')) {
            $ventaId = (int) str_replace('venta-', '', $idempotencyKey);
            return Venta::find($ventaId)?->factura; // tu modelo de venta
        }
        return null;
    }

    private function firmaValida(Request $request, string $secret): bool
    {
        $firmaEsperada = hash_hmac('sha256', $request->getContent(), $secret);
        $firmaRecibida = (string) $request->header('X-Signature');
        return $firmaRecibida !== '' && hash_equals($firmaEsperada, $firmaRecibida);
    }
}
Este webhook reintenta con backoff (10s/30s/1min/5min/15min, hasta 5 veces) si no le devolvemos 2xx — asegurate de responder rápido y siempre noContent() salvo que la firma falle.

4.4. Acciones sobre una venta ya facturada (en tu controller de ventas)

// Reintentar una emisión que quedó en estado "error" (ej. falló la red)
public function reintentarFacturacion(Request $request, Venta $venta, EmisionComprobanteService $emisor): RedirectResponse
{
    $factura = $venta->factura;
    if (! $factura) abort(404);
    $emisor->emitir($factura, $venta);
    return redirect()->route('ventas.show', $venta)->with('status', 'Se reintentó la facturación electrónica.');
}

// Anular (nota de crédito 100%)
public function anularFactura(Request $request, Venta $venta, ProcesadorDeVentas $procesador, EmisionNotaCreditoService $emisor): RedirectResponse
{
    $motivo = $request->validate(['motivo' => ['required', 'string', 'max:1000']])['motivo'];

    try {
        $notaCredito = $procesador->anularVenta($venta, $request->user(), $motivo);
    } catch (\RuntimeException $e) {
        return back()->withErrors(['anular' => $e->getMessage()]);
    }

    $emisor->emitir($notaCredito);
    return redirect()->route('ventas.show', $venta)->with('status', 'Factura anulada — se emitió la nota de crédito.');
}

// Reintentar la emisión de una NC que quedó en error
public function reintentarNotaCredito(Request $request, Venta $venta, EmisionNotaCreditoService $emisor): RedirectResponse
{
    $notaCredito = $venta->factura?->notaCredito;
    if (! $notaCredito) abort(404);
    $emisor->emitir($notaCredito);
    return redirect()->route('ventas.show', $venta)->with('status', 'Se reintentó la emisión de la nota de crédito.');
}
4.5. Guardar la configuración (pantalla de Configuración)

public function update(Request $request): RedirectResponse
{
    $config = ConfiguracionFacturacion::first();

    $datos = $request->validate([
        'condicion_fiscal' => [
            'nullable',
            Rule::in(['responsable_inscripto', 'monotributista']),
            // El CUIT se exige recién acá, en el único momento que hace falta de verdad.
            function ($attribute, $value, $fail) {
                if ($value && ! /* tu CUIT del negocio */) {
                    $fail('Para configurar la condición fiscal primero necesitás cargar el CUIT de la empresa.');
                }
            },
        ],
        'comprobante_predeterminado' => ['required', Rule::in(['remito', 'A', 'B', 'C'])],
        'formato_comprobante' => ['required', Rule::in(['ticket', 'a4'])],
    ]);

    $config->update([
        ...$datos,
        'factura_habilitada' => $request->boolean('factura_habilitada'),
        'mostrar_modal_comprobante' => $request->boolean('mostrar_modal_comprobante'),
    ]);

    return redirect()->route('configuracion.edit')->with('status', 'Configuración guardada.');
}
No vacíes el CUIT mientras puedeFacturar() sea true — si tu formulario de "datos del negocio" permite editar el CUIT, agregá el mismo chequeo que el proyecto original:


if (! $datos['cuit'] && $config->puedeFacturar()) {
    return back()->withErrors(['cuit' => 'No podés dejar el CUIT vacío mientras tengas una condición fiscal configurada. Cambiala a "Sin configurar" primero.'])->withInput();
}
Y si cambiás razón social/CUIT/email después de haber iniciado el alta (credencialFacturacion->empresa_externa_id ya existe), hay que sincronizarlo con la API externa antes de guardar local:


$credencial = CredencialFacturacion::first();
if ($credencial?->empresa_externa_id && /* razon_social, cuit o email cambiaron */) {
    try {
        $onboarding->corregirDatos($credencial, $camposQueCambiaron);
    } catch (CertificadoYaValidadoException $e) {
        return back()->withErrors(['datos_facturacion' => $e->getMessage()])->withInput();
    } catch (CuitYaRegistradoException $e) {
        return back()->withErrors(['cuit' => $e->getMessage()])->withInput();
    }
}
5. UI de Configuración (lo que el usuario pidió explícitamente)
Sección "Facturación" en la pantalla de Configuración:

1. Habilitar/deshabilitar facturación — checkbox, activado por defecto:


<label>
  <input type="checkbox" name="factura_habilitada" value="1" @checked($config->factura_habilitada)>
  Habilitar facturación
  <!-- "Si lo desmarcás, en Caja solo se puede elegir Remito — no borra tu condición
       fiscal ni el certificado AFIP ya configurado, útil para pausar temporalmente." -->
</label>
2. Condición fiscal — select, controla qué letra de factura está disponible:


<select name="condicion_fiscal">
  <option value="">Sin configurar</option>
  <option value="responsable_inscripto" @selected($config->condicion_fiscal === 'responsable_inscripto')>Responsable Inscripto</option>
  <option value="monotributista" @selected($config->condicion_fiscal === 'monotributista')>Monotributista</option>
</select>
3. Comprobante preseleccionado al cobrar — ACÁ es donde dejás "Factura B" por defecto. El select solo muestra la opción de letra que corresponde a la condición fiscal elegida (nunca dejes elegir "C" si sos Responsable Inscripto, ni "A"/"B" si sos Monotributista):


<select name="comprobante_predeterminado">
  <option value="remito" @selected($config->comprobante_predeterminado === 'remito')>Remito</option>
  @if ($config->condicion_fiscal === 'responsable_inscripto')
    <option value="B" @selected($config->comprobante_predeterminado === 'B')>Factura B</option>
    {{-- si también querés ofrecer A por defecto, agregala igual --}}
  @elseif ($config->condicion_fiscal === 'monotributista')
    <option value="C" @selected($config->comprobante_predeterminado === 'C')>Factura C</option>
  @endif
</select>
Para que quede "por defecto la B": simplemente guardá comprobante_predeterminado = 'B' (seed inicial, o que el usuario lo elija una vez en esta pantalla) — el cajero después puede cambiar el medio al cobrar, esto solo define qué viene marcado.

4. Formato de impresión:


<select name="formato_comprobante">
  <option value="ticket" @selected($config->formato_comprobante === 'ticket')>Ticket (80mm)</option>
  <option value="a4" @selected($config->formato_comprobante === 'a4')>A4 (hoja entera)</option>
</select>
5. El formulario de onboarding (subir certificado) — solo se muestra si $config->puedeFacturar():


@if ($config->puedeFacturar())
  <div>
    @if ($credencial?->estaActiva())
      <p>Configurada — ambiente {{ $credencial->ambiente }}, punto de venta {{ $credencial->punto_venta }}.</p>
      <p>¿Cambió el dueño, venció el certificado, o se comprometió la clave? Podés renovarlo acá abajo.</p>
    @elseif ($credencial?->estado === 'pendiente_onboarding')
      <p>Se inició la configuración pero todavía no se completó. Si el link venció, volvé a iniciar.</p>
    @else
      <p>Conectá tu certificado AFIP para poder emitir comprobantes con CAE automáticamente al cobrar.</p>
    @endif

    <form method="POST" action="{{ route('configuracion.facturacion.iniciar') }}" target="_blank">
      @csrf
      <select name="ambiente">
        <option value="homologacion">Homologación (pruebas)</option>
        <option value="produccion">Producción</option>
      </select>
      <button type="submit">
        {{ $credencial?->estaActiva() ? 'Renovar certificado' : ($credencial ? 'Reintentar configuración' : 'Configurar facturación electrónica') }}
      </button>
    </form>
    <p>Se abre en una pestaña nueva — cuando termines de cargar el certificado ahí, volvé y actualizá esta página.</p>
  </div>
@endif
Ojo: el formulario de subir el .crt/.key en sí no lo construís vos — eso vive del lado de la otra API (onboarding_url), es una pantalla hosteada por ellos. Tu única responsabilidad acá es el botón que pide el link y abre esa pestaña.

6. Generación del PDF del comprobante (opcional pero recomendado)
Si necesitás imprimir el ticket/factura con el QR oficial de AFIP (obligatorio por normativa RG 4892 para comprobantes con CAE), usá endroid/qr-code (ya es dependencia mínima, sin servicios externos):


private function qrAfip(string $cuitEmpresa, int $tipoComprobanteAfip, int $puntoVenta, string $numeroComprobante, string $cae, Carbon $fechaEmision, float $importe, ?string $clienteCuit): string
{
    $payload = [
        'ver' => 1,
        'fecha' => $fechaEmision->format('Y-m-d'),
        'cuit' => (int) $cuitEmpresa,
        'ptoVta' => $puntoVenta,
        'tipoCmp' => $tipoComprobanteAfip, // 1=A, 6=B, 11=C, 3=NC-A, 8=NC-B, 13=NC-C
        'nroCmp' => /* número SIN el punto de venta — "0001-00000900" → 900 */,
        'importe' => round($importe, 2),
        'moneda' => 'PES',
        'ctz' => 1,
        'tipoDocRec' => $clienteCuit ? 80 : 99,
        'nroDocRec' => $clienteCuit ? (int) $clienteCuit : 0,
        'tipoCodAut' => 'E',
        'codAut' => (int) $cae,
    ];

    $url = 'https://www.afip.gob.ar/fe/qr/?p='.base64_encode(json_encode($payload));

    return (new PngWriter())->write(new QrCode(data: $url, size: 150, margin: 4))->getDataUri();
}
numero_comprobante llega de la API en formato "PPPP-NNNNNNNN" — para nroCmp del QR necesitás solo la segunda parte (el número real, sin el punto de venta):


$partes = explode('-', $numeroComprobante);
$nroCmp = (int) end($partes);
Códigos AFIP por tipo: Factura A=1, B=6, C=11. NC A=3, B=8, C=13.

7. Referencia completa de la API externa (contrato ya probado en producción)
Paso 1 — Dar de alta

POST {FACTURACION_API_URL}/api/platform/v1/empresas
Headers: Authorization: Bearer {PLATFORM_KEY}, Content-Type: application/json
Body: { "razon_social": "...", "cuit": "20123456789", "email_contacto": "...", "ambiente": "produccion"|"homologacion", "webhook_url": "{tu URL}/api/webhooks/facturacion/comprobantes" }
→ 201: { "empresa_id": 5, "onboarding_id": "...", "ambiente": "...", "onboarding_url": "https://...", "expira_en": "..." }
→ 409: CUIT ya pertenece a otra integración (CuitYaRegistradoException)
Volver a llamar con el mismo CUIT (misma platform key) reutiliza la misma empresa del otro lado, no duplica ni rechaza — es lo que permite re-disparar esto para renovar certificado.

Paso 1.5 — Corregir datos antes de certificar

PATCH {FACTURACION_API_URL}/api/platform/v1/empresas/{empresa_id}
Body (cualquier subconjunto): { "razon_social", "cuit", "email_contacto" }
→ 200 éxito · 409 (dos casos, se distinguen por texto del message) · 404 no existe · 422 validación
Paso 2 — Webhook de onboarding (lo recibimos)

POST /api/webhooks/facturacion/onboarding
Body: { "onboarding_id", "empresa_id", "cuit", "ambiente", "punto_venta", "renovacion"?, "api_key", "comprobantes_webhook_secret"? }
Firma: header X-Signature = HMAC-SHA256(body crudo, FACTURACION_ONBOARDING_WEBHOOK_SECRET)
Paso 3 — Emitir comprobante

POST {FACTURACION_API_URL}/api/v1/comprobantes
Headers: Authorization: Bearer {api_key}, Idempotency-Key: {único y estable}, Content-Type: application/json
Body: {
  "punto_venta": 4, "tipo_comprobante": 1|6|11|3|8|13, "concepto": 1,
  "cliente_doc_tipo": 80|99, "cliente_doc_nro": "20123456789"|null,
  "moneda": "PES", "cotizacion": 1,
  "importe_neto": 8.26, "importe_iva": 1.74, "importe_total": 10,
  "iva_detalle": [{"Id": 5, "BaseImp": 8.26, "Importe": 1.74}],  // [] si es Factura/NC C
  "comprobante_asociado_id": null  // solo para NC: el id interno de la factura original
}
→ 200: {cae, cae_vencimiento, numero_comprobante, id} — aprobado al toque
→ 202: {id} — pendiente, se resuelve por el webhook de comprobantes (o GET de consulta)
→ 422: {error_mensaje} — rechazado por AFIP. Un rechazo NO consume el número real (se puede reintentar sin límite).
→ 401: api_key inválida/revocada
No mandes CondicionIVAReceptorId — la API lo infiere sola.

Webhook de comprobantes (recibido cuando un 202 se resuelve)

POST /api/webhooks/facturacion/comprobantes
Firma: X-Signature con el webhook_secret DE ESA empresa (no el de onboarding — distinto secreto)
Reintenta con backoff (10s/30s/1min/5min/15min) hasta 5 veces si no respondés 2xx.

Tabla de códigos
tipo_comprobante	letra		cliente_doc_tipo	
1	Factura A		80	CUIT
6	Factura B		99	Consumidor Final
11	Factura C			
3	NC A			
8	NC B			
13	NC C			
8. Checklist de verificación antes de dar por terminado
 Con FACTURACION_API_URL vacía: el sistema factura normal (todo en Remito), nada rompe.
 POST {API}/api/platform/v1/empresas con la platform key real → 201, credencial queda pendiente_onboarding.
 El botón "Configurar facturación electrónica" abre el onboarding_url real en pestaña nueva.
 Simular el webhook de onboarding a mano (firma HMAC correcta) → credencial pasa a activa con api_key/punto_venta.
 Con condición fiscal = Responsable Inscripto: el selector de comprobante predeterminado solo ofrece Remito/A/B (nunca C).
 Con condición fiscal = Monotributista: solo ofrece Remito/C.
 Guardar "Comprobante preseleccionado" = B → al abrir el formulario de cobro, viene Factura B marcada por defecto (y el cajero la puede cambiar).
 Desactivar "Habilitar facturación" → en el formulario de cobro solo aparece Remito, aunque la condición fiscal siga configurada.
 Cobrar una venta con Factura B → se crea la Factura, se llama emitir(), con credenciales reales de homologación debería volver 200 o 202.
 Simular webhook de comprobantes (firma con el webhook_secret de la credencial) para resolver un 202 → la Factura pasa a aprobada con CAE.
 "Anular factura" (con CAE) → crea NotaCredito, llama a emitir(), letra de NC coincide con la de la factura.
 Intentar anular una venta sin factura aprobada → rechazado con mensaje claro, nada se modifica.
 El PDF de la factura muestra el CAE, vencimiento, y el QR de AFIP escaneable.
 php artisan route:list muestra las 2 rutas de webhook como públicas (sin middleware auth).


Eso es todo — un único documento con todo el diseño (modelos, migraciones, servicios, controllers, webhooks, UI, contrato de la API externa y checklist). No falta nada más que agregar por mi parte; lo único que vos tenés que aportar al abrir el otro chat son las credenciales reales y confirmar el stack si no es Laravel.