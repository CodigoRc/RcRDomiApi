-- ============================================
-- WHMCS INTEGRATION TABLES
-- ============================================
-- Tablas necesarias para la integración con WHMCS
-- Ejecutar en la base de datos de Laravel API
-- ============================================

-- Tabla 1: Mapeo de sincronización
-- Relaciona entidades de Laravel con entidades de WHMCS
DROP TABLE IF EXISTS `whmcs_sync_map`;
CREATE TABLE `whmcs_sync_map` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  
  -- Información de entidad
  `entity_type` VARCHAR(50) NOT NULL COMMENT 'Tipo: client, station, product, invoice, ticket, etc.',
  `laravel_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID en sistema Laravel',
  `whmcs_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID en WHMCS',
  
  -- Información de sincronización
  `sync_direction` ENUM('laravel_to_whmcs', 'whmcs_to_laravel', 'bidirectional') 
    DEFAULT 'laravel_to_whmcs' NOT NULL,
  `sync_status` ENUM('synced', 'pending', 'error', 'conflict', 'unlinked') 
    DEFAULT 'synced' NOT NULL,
  
  -- Timestamps
  `last_synced_at` TIMESTAMP NULL DEFAULT NULL,
  `last_error_at` TIMESTAMP NULL DEFAULT NULL,
  
  -- Metadata
  `metadata` JSON NULL DEFAULT NULL COMMENT 'Datos extra, mapeos personalizados, etc.',
  `last_error` TEXT NULL DEFAULT NULL,
  `sync_attempts` INT NOT NULL DEFAULT 0,
  
  -- Usuario que realizó la sincronización
  `synced_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  
  -- Control de Laravel
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  
  PRIMARY KEY (`id`),
  
  -- Índices
  INDEX `whmcs_sync_map_entity_type_index` (`entity_type`),
  INDEX `whmcs_sync_map_laravel_id_index` (`laravel_id`),
  INDEX `whmcs_sync_map_whmcs_id_index` (`whmcs_id`),
  INDEX `whmcs_sync_map_sync_status_index` (`sync_status`),
  INDEX `whmcs_sync_map_entity_type_sync_status_index` (`entity_type`, `sync_status`),
  INDEX `whmcs_sync_map_last_synced_at_index` (`last_synced_at`),
  
  -- Constraints únicos
  UNIQUE KEY `unique_laravel_entity` (`entity_type`, `laravel_id`),
  UNIQUE KEY `unique_whmcs_entity` (`entity_type`, `whmcs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Mapeo de sincronización Laravel-WHMCS';


-- Tabla 2: Logs de sincronización
-- Registra todas las operaciones realizadas con WHMCS
DROP TABLE IF EXISTS `whmcs_sync_logs`;
CREATE TABLE `whmcs_sync_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  
  -- Detalles de operación
  `entity_type` VARCHAR(50) NOT NULL COMMENT 'Tipo de entidad',
  `operation` ENUM('push', 'pull', 'update_whmcs', 'update_laravel', 'delete', 'test', 'list', 'get') NOT NULL,
  
  -- IDs de entidades
  `laravel_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `whmcs_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `sync_map_id` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Referencia a whmcs_sync_map',
  
  -- Estado
  `status` ENUM('success', 'error', 'warning') NOT NULL,
  
  -- Datos de request/response
  `request_data` JSON NULL DEFAULT NULL COMMENT 'Datos enviados a WHMCS',
  `response_data` JSON NULL DEFAULT NULL COMMENT 'Respuesta de WHMCS',
  `error_message` TEXT NULL DEFAULT NULL,
  `whmcs_result` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Código de resultado WHMCS',
  
  -- Metadata
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `user_agent` TEXT NULL DEFAULT NULL,
  `user_id` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Usuario que realizó la acción',
  
  -- Performance
  `execution_time_ms` INT NULL DEFAULT NULL COMMENT 'Tiempo de ejecución en milisegundos',
  
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  
  -- Índices
  INDEX `whmcs_sync_logs_entity_type_index` (`entity_type`),
  INDEX `whmcs_sync_logs_operation_index` (`operation`),
  INDEX `whmcs_sync_logs_laravel_id_index` (`laravel_id`),
  INDEX `whmcs_sync_logs_whmcs_id_index` (`whmcs_id`),
  INDEX `whmcs_sync_logs_sync_map_id_index` (`sync_map_id`),
  INDEX `whmcs_sync_logs_status_index` (`status`),
  INDEX `whmcs_sync_logs_user_id_index` (`user_id`),
  INDEX `whmcs_sync_logs_created_at_index` (`created_at`),
  INDEX `whmcs_sync_logs_entity_type_operation_index` (`entity_type`, `operation`),
  INDEX `whmcs_sync_logs_status_created_at_index` (`status`, `created_at`),
  INDEX `whmcs_sync_logs_user_id_created_at_index` (`user_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historial de operaciones WHMCS';


-- ============================================
-- VERIFICACIÓN
-- ============================================
-- Ejecuta estas queries para verificar que las tablas se crearon correctamente:

-- SELECT COUNT(*) as total FROM whmcs_sync_map;
-- SELECT COUNT(*) as total FROM whmcs_sync_logs;
-- SHOW CREATE TABLE whmcs_sync_map;
-- SHOW CREATE TABLE whmcs_sync_logs;

