-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS kontratos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE kontratos;

-- Crear tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'usuario') DEFAULT 'usuario',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
);

-- Insertar usuario administrador por defecto
INSERT INTO usuarios (nombre, email, password, rol) VALUES 
('Administrador', 'admin@contratos.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- La contraseña del admin es 'password'
