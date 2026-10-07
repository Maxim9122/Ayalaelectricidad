-- ============================================================
-- Facturación electrónica AFIP/ARCA vía API externa
-- Ejecutar UNA vez en la base de datos (phpMyAdmin > SQL).
-- Es idempotente: se puede volver a correr sin romper nada.
-- ============================================================

-- Configuración general de facturación (una sola fila, id = 1)
CREATE TABLE IF NOT EXISTS `configuracion_facturacion` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `razon_social` varchar(150) DEFAULT NULL,
  `cuit` varchar(11) DEFAULT NULL,
  `email_contacto` varchar(150) DEFAULT NULL,
  `domicilio` varchar(200) DEFAULT NULL,
  `ingresos_brutos` varchar(50) DEFAULT NULL,
  `inicio_actividades` date DEFAULT NULL,
  `condicion_fiscal` varchar(30) DEFAULT NULL,          -- 'responsable_inscripto' | 'monotributista' | NULL
  `factura_habilitada` tinyint(1) NOT NULL DEFAULT 1,
  `comprobante_predeterminado` varchar(10) NOT NULL DEFAULT 'remito', -- 'remito' | 'A' | 'B' | 'C'
  `formato_comprobante` varchar(10) NOT NULL DEFAULT 'ticket',        -- 'ticket' | 'a4'
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Por defecto Factura B (solo se aplica si la condición fiscal es Responsable Inscripto)
INSERT IGNORE INTO `configuracion_facturacion` (`id`, `razon_social`, `factura_habilitada`, `comprobante_predeterminado`, `formato_comprobante`, `created_at`, `updated_at`)
VALUES (1, 'AYALA ELECTRICIDAD', 1, 'B', 'ticket', NOW(), NOW());

-- Credenciales de la API de facturación (una sola fila, id = 1)
-- api_key y webhook_secret se guardan cifrados (encryption.key del .env)
CREATE TABLE IF NOT EXISTS `credencial_facturacion` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ambiente` varchar(20) NOT NULL,                       -- 'homologacion' | 'produccion'
  `punto_venta` int(10) unsigned DEFAULT NULL,
  `api_key` text DEFAULT NULL,
  `webhook_secret` text DEFAULT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'pendiente_onboarding', -- 'pendiente_onboarding' | 'activa'
  `onboarding_id` varchar(100) DEFAULT NULL,             -- string: la API no garantiza que sea numérico
  `empresa_externa_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `facturas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `venta_id` int(10) DEFAULT NULL,
  `cliente_id` int(10) DEFAULT NULL,
  `cliente_nombre` varchar(150) NOT NULL,
  `cliente_cuit` varchar(11) DEFAULT NULL,
  `tipo_factura` char(1) NOT NULL,                       -- 'A' | 'B' | 'C'
  `importe_total` decimal(12,2) NOT NULL DEFAULT 0,
  `numero_comprobante` varchar(20) DEFAULT NULL,         -- "PPPP-NNNNNNNN", llega de la API
  `comprobante_externo_id` varchar(64) DEFAULT NULL,     -- id interno de la API (se usa para la NC)
  `cae` varchar(20) DEFAULT NULL,
  `cae_vencimiento` date DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'pendiente',     -- pendiente | pendiente_afip | aprobada | rechazada | error
  `error_mensaje` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `facturas_venta_id` (`venta_id`),
  KEY `facturas_comprobante_externo_id` (`comprobante_externo_id`),
  CONSTRAINT `facturas_venta_fk` FOREIGN KEY (`venta_id`) REFERENCES `ventas_cabecera` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Una sola NC por factura, por el 100% del importe
CREATE TABLE IF NOT EXISTS `notas_credito` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `factura_id` int(10) unsigned NOT NULL,
  `creado_por` int(10) DEFAULT NULL,
  `importe_acreditado` decimal(12,2) NOT NULL,
  `motivo` text NOT NULL,
  `numero_comprobante` varchar(20) DEFAULT NULL,
  `comprobante_externo_id` varchar(64) DEFAULT NULL,
  `cae` varchar(20) DEFAULT NULL,
  `cae_vencimiento` date DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'pendiente',
  `error_mensaje` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notas_credito_factura_id` (`factura_id`),
  KEY `notas_credito_comprobante_externo_id` (`comprobante_externo_id`),
  CONSTRAINT `notas_credito_factura_fk` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- La venta sabe con qué comprobante se emitió
ALTER TABLE `ventas_cabecera`
  ADD COLUMN IF NOT EXISTS `tipo_comprobante` varchar(10) DEFAULT NULL,  -- 'remito' | 'factura'
  ADD COLUMN IF NOT EXISTS `factura_id` int(10) unsigned DEFAULT NULL;

ALTER TABLE `ventas_cabecera`
  ADD INDEX IF NOT EXISTS `ventas_cabecera_factura_id` (`factura_id`);

-- ============================================================
-- Ajustes: código de autorización y tope de identificación
-- ============================================================

-- Código de autorización (cancelar/modificar ventas cobradas, anular facturas).
-- Se guarda como hash (password_hash); el valor inicial es el código que ya se usaba: 7559.
ALTER TABLE `configuracion_facturacion`
  ADD COLUMN IF NOT EXISTS `codigo_autorizacion_hash` varchar(255) DEFAULT NULL,
  -- Desde este importe ARCA exige identificar al consumidor final (RG 5866/2026: $10.000.000).
  ADD COLUMN IF NOT EXISTS `monto_identificar_consumidor` decimal(14,2) NOT NULL DEFAULT 10000000.00;

UPDATE `configuracion_facturacion`
SET `codigo_autorizacion_hash` = '$2y$10$9.9P4lU3r1KRrLojmRcU0OXhC6cSeHRZzwMXpUhyaQMFLHLcq62za'
WHERE `codigo_autorizacion_hash` IS NULL;

-- DNI del comprador consumidor final (cuando la operación supera el tope)
ALTER TABLE `facturas`
  ADD COLUMN IF NOT EXISTS `cliente_dni` varchar(8) DEFAULT NULL AFTER `cliente_cuit`;

-- Intento de emisión: la API devuelve siempre el mismo comprobante para la misma
-- Idempotency-Key (aunque haya sido rechazado). Tras un rechazo se usa una key nueva.
ALTER TABLE `facturas`
  ADD COLUMN IF NOT EXISTS `intento` int(10) unsigned NOT NULL DEFAULT 1 AFTER `estado`;
ALTER TABLE `notas_credito`
  ADD COLUMN IF NOT EXISTS `intento` int(10) unsigned NOT NULL DEFAULT 1 AFTER `estado`;
