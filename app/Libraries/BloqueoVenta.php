<?php

namespace App\Libraries;

use Config\Database;

/**
 * Bloqueo de una venta mientras un usuario la cobra o la modifica.
 *
 * - En la base, la venta pasa a Cobrando / Modificando / Modificando_SF y guarda
 *   su estado anterior, quién la abrió, un token de esa sesión y la hora del último uso.
 * - El mismo usuario siempre puede volver a abrirla (otra pestaña, otra PC, sesión vencida).
 * - Otro usuario puede tomarla si pasaron 60 minutos sin uso.
 * - Al cancelar, salir o vaciar el carrito se devuelve al estado anterior.
 * - Al guardar se verifica que el bloqueo siga siendo de esta sesión.
 */
class BloqueoVenta
{
    public const MINUTOS_VENCIMIENTO = 60;
    public const ESTADOS_BLOQUEO = ['Cobrando', 'Modificando', 'Modificando_SF'];

    private const SEGUNDOS_ENTRE_TOQUES = 60;

    /** Todo lo que se guarda en la sesión al abrir una venta para cobrarla o modificarla. */
    private const CLAVES_SESION = [
        'estado', 'id_pedido', 'id_vendedor', 'nombre_vendedor', 'id_cliente', 'id_cliente_pedido',
        'nombre_cli', 'nombre_cli_regis', 'fecha_pedido', 'tipo_compra', 'tipo_pago', 'total_venta',
        'total_bonificado', 'total_anterior', 'pago_efec', 'pago_transfer', 'pago_tarjeta',
        'bloqueo_token', 'bloqueo_toque',
    ];

    /**
     * Bloquea la venta para esta sesión.
     *
     * @param string[] $desde Estados desde los que se puede abrir para esta operación.
     *
     * @return string|null null si se bloqueó; si no, el mensaje para el usuario.
     */
    public static function tomar(int $id, array $desde, string $estadoBloqueo): ?string
    {
        $session = session();
        $yo = (int) $session->get('id');

        // Si esta sesión tenía abierta otra venta, se libera antes de abrir la nueva.
        if ($session->get('id_pedido') && (int) $session->get('id_pedido') !== $id) {
            self::liberarDeSesion();
        }

        $fila = self::fila($id);
        if (!$fila) {
            return 'No se encontró la venta #' . $id . '.';
        }

        $estado = $fila['estado'];
        if (in_array($estado, $desde, true)) {
            $previo = $estado;
        } elseif ($estado === $estadoBloqueo
            && in_array(self::estadoPrevio($fila), $desde, true)
            && (($yo > 0 && (int) $fila['bloqueo_usuario'] === $yo) || $fila['vencido'])) {
            // La había abierto este mismo usuario, o quedó abandonada: se retoma.
            $previo = self::estadoPrevio($fila);
        } elseif (in_array($estado, self::ESTADOS_BLOQUEO, true)) {
            return self::mensajeOcupada($fila);
        } else {
            return 'La venta #' . $id . ' no se puede abrir para esta operación (estado: ' . $estado . ').';
        }

        $token = bin2hex(random_bytes(16));
        $db = Database::connect();
        $db->table('ventas_cabecera')
            ->where('id', $id)
            ->where('estado', $estado)
            ->where('bloqueo_token', $fila['bloqueo_token'])
            ->set('estado', $estadoBloqueo)
            ->set('estado_previo', $previo)
            ->set('bloqueo_usuario', $yo > 0 ? $yo : null)
            ->set('bloqueo_token', $token)
            ->set('bloqueo_desde', 'NOW()', false)
            ->update();

        if ($db->affectedRows() !== 1) {
            return 'Otro usuario abrió la venta #' . $id . ' recién. Probá de nuevo en un momento.';
        }

        $session->set(['bloqueo_token' => $token, 'bloqueo_toque' => time()]);

        return null;
    }

    /**
     * Devuelve la venta a su estado anterior, solo si el bloqueo es de este token.
     * Un token null libera solo bloqueos viejos (de antes de este sistema), que no tienen dueño.
     */
    public static function liberar(int $id, ?string $token): bool
    {
        $fila = self::fila($id);
        if (!$fila || !in_array($fila['estado'], self::ESTADOS_BLOQUEO, true)) {
            return false;
        }
        if (($fila['bloqueo_token'] ?: null) !== ($token ?: null)) {
            return false;
        }

        $db = Database::connect();
        $db->table('ventas_cabecera')
            ->where('id', $id)
            ->where('estado', $fila['estado'])
            ->where('bloqueo_token', $token ?: null)
            ->set('estado', self::estadoPrevio($fila))
            ->set('estado_previo', null)
            ->set('bloqueo_usuario', null)
            ->set('bloqueo_token', null)
            ->set('bloqueo_desde', null)
            ->update();

        return $db->affectedRows() === 1;
    }

    /** Libera la venta que tiene abierta esta sesión y limpia la sesión y el carrito. */
    public static function liberarDeSesion(): void
    {
        $session = session();
        $id = (int) $session->get('id_pedido');
        if ($id > 0) {
            self::liberar($id, $session->get('bloqueo_token') ?: null);
        }
        self::limpiarSesion();
        \Config\Services::cart()->destroy();
    }

    /** Botones "Cancelar cobro / modificación". */
    public static function cancelar(int $idUrl): void
    {
        if (session()->get('id_pedido')) {
            self::liberarDeSesion();
            return;
        }
        // Sin datos en la sesión: solo se puede liberar un bloqueo viejo sin dueño.
        self::liberar($idUrl, null);
        self::limpiarSesion();
        \Config\Services::cart()->destroy();
    }

    /** Después de guardar: borra los datos del bloqueo (el estado final lo pone quien guarda). */
    public static function finalizar(int $id): void
    {
        if ($id > 0) {
            Database::connect()->table('ventas_cabecera')
                ->where('id', $id)
                ->set('estado_previo', null)
                ->set('bloqueo_usuario', null)
                ->set('bloqueo_token', null)
                ->set('bloqueo_desde', null)
                ->update();
        }
        self::limpiarSesion();
    }

    /** ¿La venta abierta en esta sesión sigue bloqueada a su nombre? */
    public static function esMio($idFormulario = null): bool
    {
        $session = session();
        $id = (int) $session->get('id_pedido');
        if ($id <= 0) {
            return false;
        }
        if ($idFormulario !== null && $idFormulario !== '' && (int) $idFormulario !== $id) {
            return false;
        }
        $fila = self::fila($id);

        return $fila
            && in_array($fila['estado'], self::ESTADOS_BLOQUEO, true)
            && ($fila['bloqueo_token'] ?: null) === ($session->get('bloqueo_token') ?: null);
    }

    /** Respuesta cuando la venta ya no es de esta sesión: limpia todo y avisa. No toca la venta. */
    public static function perdida(string $destino = 'catalogo')
    {
        $id = session()->get('id_pedido');
        self::limpiarSesion();
        \Config\Services::cart()->destroy();
        session()->setFlashdata('msgEr', 'La venta #' . $id . ' ya no estaba abierta a tu nombre (se canceló, se guardó desde otra pantalla o la tomó otro usuario). No se guardó ningún cambio.');

        return redirect()->to(base_url($destino));
    }

    /**
     * Marca el último uso del bloqueo (como mucho una vez por minuto).
     *
     * @return bool false si esta sesión perdió el bloqueo.
     */
    public static function tocar(): bool
    {
        $session = session();
        $id = (int) $session->get('id_pedido');
        $token = $session->get('bloqueo_token');
        if ($id <= 0 || !$token) {
            return true;
        }
        if (time() - (int) $session->get('bloqueo_toque') < self::SEGUNDOS_ENTRE_TOQUES) {
            return true;
        }

        $db = Database::connect();
        $db->table('ventas_cabecera')
            ->where('id', $id)
            ->where('bloqueo_token', $token)
            ->whereIn('estado', self::ESTADOS_BLOQUEO)
            ->set('bloqueo_desde', 'NOW()', false)
            ->update();
        $session->set('bloqueo_toque', time());

        return $db->affectedRows() > 0 || self::esMio();
    }

    public static function limpiarSesion(): void
    {
        session()->remove(self::CLAVES_SESION);
    }

    private static function fila(int $id): ?array
    {
        $minutos = (int) self::MINUTOS_VENCIMIENTO;
        $fila = Database::connect()->query(
            'SELECT v.id, v.estado, v.tipo_pago, v.total_anterior, v.estado_previo, v.bloqueo_usuario, v.bloqueo_token,
                    (v.bloqueo_desde IS NULL OR v.bloqueo_desde < NOW() - INTERVAL ' . $minutos . ' MINUTE) AS vencido,
                    TIMESTAMPDIFF(MINUTE, v.bloqueo_desde, NOW()) AS minutos,
                    u.nombre AS usuario_nombre
               FROM ventas_cabecera v
               LEFT JOIN usuarios u ON u.id = v.bloqueo_usuario
              WHERE v.id = ?',
            [$id]
        )->getRowArray();

        if (!$fila) {
            return null;
        }
        $fila['vencido'] = (bool) $fila['vencido'];

        return $fila;
    }

    /** Estado al que vuelve la venta al liberarla (deducido si el bloqueo es anterior a este sistema). */
    private static function estadoPrevio(array $fila): string
    {
        if (!empty($fila['estado_previo'])) {
            return $fila['estado_previo'];
        }
        switch ($fila['estado']) {
            case 'Modificando_SF':
                return ((float) $fila['total_anterior'] > 0) ? 'Modificada_SF' : 'Sin_Facturar';
            case 'Cobrando':
            case 'Modificando':
                return $fila['tipo_pago'] === 'Cta_Cte' ? 'Cta_Cte' : 'Pendiente';
            default:
                return $fila['estado'];
        }
    }

    private static function mensajeOcupada(array $fila): string
    {
        $quien = $fila['usuario_nombre'] ? esc($fila['usuario_nombre']) : 'otro usuario';
        $hace = $fila['minutos'] !== null ? ' (último uso hace ' . (int) $fila['minutos'] . ' min)' : '';
        $accion = $fila['estado'] === 'Cobrando' ? 'cobrando' : 'modificando';

        return 'La venta #' . $fila['id'] . ' la está ' . $accion . ' ' . $quien . $hace
            . '. Si quedó abierta sin uso, se libera sola a los ' . self::MINUTOS_VENCIMIENTO . ' minutos.';
    }
}
