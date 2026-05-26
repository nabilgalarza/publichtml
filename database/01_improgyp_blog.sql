-- Tabla de artículos del blog (ejecutar con BD u718580158_improgyp seleccionada)
CREATE TABLE IF NOT EXISTS improgyp_blog (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    categoria VARCHAR(100) DEFAULT 'General',
    tiempo_lectura VARCHAR(50) DEFAULT '5 min',
    resumen TEXT,
    contenido LONGTEXT,
    portada VARCHAR(255) DEFAULT 'favicon-app.png?v=5',
    borrador TINYINT(1) DEFAULT 0,
    visitas INT DEFAULT 0,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_borrador (borrador),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
