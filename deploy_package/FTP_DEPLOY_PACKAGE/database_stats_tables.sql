-- ================================================
-- NUEVAS TABLAS PARA ESTADÍSTICAS AVANZADAS
-- Base de datos: rcdomico_rdomiap_db
-- ================================================

-- 1. ESTADÍSTICAS POR HORA
CREATE TABLE IF NOT EXISTS `service_stats_hourly` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `hour_timestamp` timestamp NOT NULL,
  `count` int(11) NOT NULL DEFAULT 0,
  `unique_users` int(11) NOT NULL DEFAULT 0,
  `peak_concurrent` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_service_hour` (`service_id`, `hour_timestamp`),
  KEY `idx_service_hour` (`service_id`, `hour_timestamp`),
  KEY `idx_hour_timestamp` (`hour_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. ESTADÍSTICAS DIARIAS
CREATE TABLE IF NOT EXISTS `service_stats_daily` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `total_count` int(11) NOT NULL DEFAULT 0,
  `unique_users` int(11) NOT NULL DEFAULT 0,
  `peak_hour` int(11) NULL DEFAULT NULL,
  `avg_session_duration` decimal(8,2) NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_service_date` (`service_id`, `date`),
  KEY `idx_service_date` (`service_id`, `date`),
  KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. ESTADÍSTICAS EN TIEMPO REAL (CACHE)
CREATE TABLE IF NOT EXISTS `service_stats_realtime` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `current_listeners` int(11) NOT NULL DEFAULT 0,
  `hourly_growth` decimal(5,2) NOT NULL DEFAULT 0.00,
  `daily_growth` decimal(5,2) NOT NULL DEFAULT 0.00,
  `trending_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `last_increment` timestamp NULL DEFAULT NULL,
  `last_update` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_service` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. SESIONES DETALLADAS (ANTI-ABUSE)
CREATE TABLE IF NOT EXISTS `service_stats_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `session_hash` varchar(32) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent_hash` varchar(32) NOT NULL,
  `first_access` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_access` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `total_requests` int(11) NOT NULL DEFAULT 1,
  `suspicious_score` decimal(3,2) NOT NULL DEFAULT 0.00,
  `is_blocked` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_service_session` (`service_id`, `session_hash`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_suspicious` (`suspicious_score`),
  KEY `idx_last_access` (`last_access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. LOG DE EVENTOS (DEBUGGING Y ANÁLISIS)
CREATE TABLE IF NOT EXISTS `service_stats_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `session_hash` varchar(32) NOT NULL,
  `event_type` enum('increment','error','suspicious','blocked') NOT NULL DEFAULT 'increment',
  `event_value` int(11) NOT NULL DEFAULT 1,
  `ip_address` varchar(45) NOT NULL,
  `user_agent_hash` varchar(32) NOT NULL,
  `is_suspicious` tinyint(1) NOT NULL DEFAULT 0,
  `metadata` json NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_service_type` (`service_id`, `event_type`),
  KEY `idx_session` (`session_hash`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_suspicious` (`is_suspicious`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- VISTAS ÚTILES PARA CONSULTAS RÁPIDAS
-- ================================================

-- Vista para estadísticas de hoy
CREATE OR REPLACE VIEW `v_today_stats` AS
SELECT 
  s.service_id,
  COALESCE(d.total_count, 0) as today_total,
  COALESCE(d.unique_users, 0) as today_unique,
  COALESCE(r.current_listeners, 0) as current_listeners,
  COALESCE(r.trending_score, 0) as trending_score,
  d.updated_at as last_update
FROM (SELECT DISTINCT service_id FROM service_stats WHERE type = 'view') s
LEFT JOIN service_stats_daily d ON d.service_id = s.service_id AND d.date = CURDATE()
LEFT JOIN service_stats_realtime r ON r.service_id = s.service_id;

-- Vista para top estaciones por hora
CREATE OR REPLACE VIEW `v_hourly_top_stations` AS
SELECT 
  service_id,
  SUM(count) as total_hourly,
  SUM(unique_users) as total_unique,
  MAX(peak_concurrent) as max_concurrent,
  hour_timestamp
FROM service_stats_hourly 
WHERE hour_timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY service_id, hour_timestamp
ORDER BY total_hourly DESC;

-- ================================================
-- TRIGGERS PARA LIMPIEZA AUTOMÁTICA
-- ================================================

-- Trigger para limpiar datos antiguos (opcional)
DELIMITER $$
CREATE EVENT IF NOT EXISTS `cleanup_old_hourly_stats`
ON SCHEDULE EVERY 1 DAY STARTS '2024-01-01 02:00:00'
DO
BEGIN
  DELETE FROM service_stats_hourly 
  WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
  
  DELETE FROM service_stats_events 
  WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
END$$
DELIMITER ;

-- ================================================
-- CONSULTAS DE PRUEBA (EJECUTAR DESPUÉS)
-- ================================================

/*
-- Verificar que las tablas se crearon correctamente:
SHOW TABLES LIKE 'service_stats%';

-- Verificar estructura:
DESCRIBE service_stats_hourly;
DESCRIBE service_stats_daily;
DESCRIBE service_stats_realtime;

-- Insertar datos de prueba:
INSERT INTO service_stats_hourly (service_id, hour_timestamp, count, unique_users) 
VALUES (1596, NOW(), 100, 25);

-- Verificar vistas:
SELECT * FROM v_today_stats WHERE service_id = 1596;
*/

-- ================================================
-- NOTAS IMPORTANTES:
-- ================================================
/*
1. Estas tablas son ADICIONALES a la tabla service_stats existente
2. NO modifican ni afectan el sistema actual
3. El sistema original seguirá funcionando normalmente
4. Se pueden agregar índices adicionales según el rendimiento
5. Los eventos de limpieza están configurados para 90 días (hourly) y 30 días (events)
*/