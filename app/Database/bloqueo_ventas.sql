-- ============================================================
-- Bloqueo de ventas mientras se cobran o modifican
-- Ejecutar UNA vez en la base de datos (phpMyAdmin > SQL).
-- Es idempotente: se puede volver a correr sin romper nada.
-- Solo agrega columnas; no modifica datos existentes.
-- ============================================================

ALTER TABLE `ventas_cabecera`
  ADD COLUMN IF NOT EXISTS `estado_previo` varchar(15) DEFAULT NULL,   -- estado al que vuelve al cancelar
  ADD COLUMN IF NOT EXISTS `bloqueo_usuario` int(10) DEFAULT NULL,     -- usuario que la tiene abierta
  ADD COLUMN IF NOT EXISTS `bloqueo_token` char(32) DEFAULT NULL,      -- identifica la sesión que la abrió
  ADD COLUMN IF NOT EXISTS `bloqueo_desde` datetime DEFAULT NULL;      -- último uso (se vence a los 60 min)
