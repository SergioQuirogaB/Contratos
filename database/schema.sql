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

-- Crear tabla de contratos
CREATE TABLE IF NOT EXISTS contratos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ano INT,
    empresa VARCHAR(255),
    cliente VARCHAR(255),
    no_contrato VARCHAR(100),
    valor_pesos_sin_iva DECIMAL(15,2),
    valor_dolares DECIMAL(15,2),
    descripcion TEXT,
    categoria VARCHAR(100),
    valor_mensual DECIMAL(15,2),
    observaciones TEXT,
    fecha_inicio DATE,
    fecha_vencimiento DATE,
    valor_facturado DECIMAL(15,2),
    porcentaje_ejecucion DECIMAL(5,2),
    valor_pendiente_ejecutar DECIMAL(15,2),
    estado VARCHAR(50),
    no_horas INT,
    factura_no VARCHAR(100),
    no_poliza VARCHAR(100),
    fecha_vencimiento_poliza DATE,
    usuario_id INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Insertar usuario administrador por defecto
INSERT INTO usuarios (nombre, email, password, rol) VALUES 
('Administrador', 'admin@contratos.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- La contraseña del admin es 'password'
