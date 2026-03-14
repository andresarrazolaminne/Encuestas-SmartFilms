-- Sistema de gestión de encuestas SmartFilms
-- MySQL 8.x / MariaDB 10.2+

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(64) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE forms (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  created_by INT UNSIGNED NOT NULL,
  slug VARCHAR(128) NOT NULL UNIQUE,
  title VARCHAR(255) NOT NULL,
  config JSON NOT NULL COMMENT 'Estilos: fondo, colores, fuentes, página de respuesta',
  definition JSON NOT NULL COMMENT 'Estructura: secciones, campos, opciones',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_forms_slug (slug)
);

CREATE TABLE form_responses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  form_id INT UNSIGNED NOT NULL,
  response_data JSON NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  ip VARCHAR(45) NULL,
  user_agent VARCHAR(512) NULL,
  FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE,
  INDEX idx_responses_form (form_id),
  INDEX idx_responses_created (created_at)
);

-- Usuario inicial: ejecutar en PHP para generar password_hash seguro:
--   php -r "echo password_hash('admin123', PASSWORD_DEFAULT);"
-- Luego reemplazar el valor abajo o insertar desde la app.
-- Ejemplo (hash para 'admin123' – regenerar en producción):
INSERT INTO users (username, password_hash) VALUES (
  'admin',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
);
