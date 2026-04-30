-- Crear base de datos principal si no existe
CREATE DATABASE IF NOT EXISTS `bd_recrea_sys` 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

-- Crear base de datos de pruebas
CREATE DATABASE IF NOT EXISTS `test_bd_recrea_sys` 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

-- Conceder permisos
GRANT ALL PRIVILEGES ON `bd_recrea_sys`.* TO 'recrea_user'@'%';
GRANT ALL PRIVILEGES ON `test_bd_recrea_sys`.* TO 'recrea_user'@'%';
FLUSH PRIVILEGES;

-- Seleccionar base de datos principal
USE `bd_recrea_sys`;
CREATE DATABASE IF NOT EXISTS bd_recrea_sys;
USE bd_recrea_sys;
-- DROP DATABASE bd_recrea_sys;
-- Tabla: usuario (con UUID)
CREATE TABLE usuario (
    ID_Usuario CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    ci VARCHAR(100) NOT NULL UNIQUE,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    tipo ENUM('Administrador', 'Logistica', 'Tecnico', 'Contabilidad', 'Usuario') NOT NULL,
    usuario_asignado VARCHAR(25) NOT NULL DEFAULT 'Aun no tiene',
    contrasena VARCHAR(255) NOT NULL DEFAULT 'Aun no tiene',
    estado ENUM('Pendiente de asignacion', 'Activo', 'Inhabilitado') DEFAULT 'Pendiente de asignacion' NOT NULL
);

-- Tabla: inicio_sesion (con UUID)
CREATE TABLE inicio_sesion (
    ID_Inicio_Sesion CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    ID_Usuario CHAR(36) NOT NULL,
    usuario_asignado VARCHAR(100) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    fecha_inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_ultima_sesion DATETIME NULL,
    FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario)
);

-- Tabla para técnicos (con UUID)
CREATE TABLE Tecnico(
    ID_Tecnico CHAR(36) PRIMARY KEY,
    Especialidad ENUM('Ensamblador', 'Comprobador', 'Mantenimiento') NOT NULL,
    Cantidad_Actividades INT DEFAULT 0 NOT NULL,
    FOREIGN KEY (ID_Tecnico) REFERENCES usuario(ID_Usuario)
);

-- Tabla para logística (con UUID)
CREATE TABLE Logistica(
    ID_Logistica CHAR(36) PRIMARY KEY,
    FOREIGN KEY (ID_Logistica) REFERENCES usuario(ID_Usuario)
);

-- HISTORIAL DE ACTIVIDADES (con UUID)
CREATE TABLE historial_actividades (
    ID_Historial_Actividades CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    ID_Usuario CHAR(36) NOT NULL,
    descripcion TEXT NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario)
);

-- Tabla de para comercios (con UUID)
CREATE TABLE Comercio (
    ID_Comercio CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    Nombre VARCHAR(100) NOT NULL UNIQUE,
    Tipo ENUM('Minorista', 'Mayorista') NOT NULL,
    Direccion TEXT NOT NULL,
    Telefono VARCHAR(15) NOT NULL UNIQUE,
    Cantidad_Maquinas INT DEFAULT 0 NOT NULL,
    Fecha_Registro DATE NOT NULL
);

-- Tabla para máquinas recreativas (con UUID)
CREATE TABLE MaquinaRecreativa (
    ID_Maquina CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    Nombre_Maquina VARCHAR(100) NOT NULL,
    Tipo VARCHAR(50) NOT NULL,
    Etapa ENUM('Montaje', 'Distribucion', 'Recaudacion') DEFAULT 'Montaje' NOT NULL,
    Estado ENUM('Ensamblandose', 'Comprobandose', 'Reensamblandose', 'Distribuyendose', 'Operativa', 'No operativa', 'Retirada') DEFAULT 'Ensamblandose' NOT NULL,
    Fecha_Registro DATE NOT NULL,
    ID_Tecnico_Ensamblador CHAR(36) NOT NULL,
    ID_Tecnico_Comprobador CHAR(36) NOT NULL,
    ID_Comercio CHAR(36) NOT NULL,
    ID_Tecnico_Mantenimiento CHAR(36),
    FOREIGN KEY (ID_Tecnico_Ensamblador) REFERENCES Tecnico(ID_Tecnico),
    FOREIGN KEY (ID_Tecnico_Comprobador) REFERENCES Tecnico(ID_Tecnico),
    FOREIGN KEY (ID_Comercio) REFERENCES Comercio(ID_Comercio),
    FOREIGN KEY (ID_Tecnico_Mantenimiento) REFERENCES Tecnico(ID_Tecnico)
);

-- Tabla para notificaciones (con UUID)
CREATE TABLE NotificacionMaquinaRecreativa (
    ID_Notificacion CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    ID_Remitente CHAR(36) NOT NULL,
    ID_Destinatario CHAR(36) NOT NULL,
    ID_Maquina CHAR(36) NOT NULL,
    Tipo ENUM(
        'Nuevo montaje',
        'Comprobar maquina recreativa',
        'Reensamblar maquina recreativa',
        'Distribuir maquina recreativa',
        'Dar mantenimiento a maquina recreativa',
        'Maquina recreativa retirada',
        'Maquina recreativa reparada'
    ) NOT NULL,
    Mensaje TEXT,
    Fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Estado ENUM('Leido', 'No leido') DEFAULT 'No leido' NOT NULL,
    FOREIGN KEY (ID_Remitente) REFERENCES usuario(ID_Usuario),
    FOREIGN KEY (ID_Destinatario) REFERENCES usuario(ID_Usuario),
    FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina)
);

CREATE INDEX idx_notificacion_maquina_estado ON NotificacionMaquinaRecreativa(Estado);

-- Tabla: componente (con UUID)
CREATE TABLE componente (
    ID_Componente CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    tipo ENUM('Logistico', 'Electronico', 'Estructural', 'Accesorio') NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    precio DECIMAL(10,2) DEFAULT 10.00
);

-- Tabla: componente_usuario (con UUID)
CREATE TABLE componente_usuario (
    ID_Registro CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    ID_Componente CHAR(36) NOT NULL,
    ID_Usuario CHAR(36) NOT NULL,
    fecha_asignacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_liberacion DATETIME NULL,
    ID_Maquina CHAR(36) NULL,
    FOREIGN KEY (ID_Componente) REFERENCES componente(ID_Componente),
    FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario),
    FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina)
);

-- Tabla: reporte (con UUID)
CREATE TABLE reporte (
    ID_Reporte CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    ID_Usuario_Emisor CHAR(36) NOT NULL,
    ID_Usuario_Destinatario CHAR(36),
    fecha_hora DATETIME NOT NULL,
    descripcion TEXT NOT NULL,
    estado VARCHAR(15) NOT NULL,
    FOREIGN KEY (ID_Usuario_Emisor) REFERENCES usuario(ID_Usuario),
    FOREIGN KEY (ID_Usuario_Destinatario) REFERENCES usuario(ID_Usuario)
);

-- Tabla: notificaciones (con UUID)
CREATE TABLE notificaciones (
    ID_Notificaciones CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    ID_Reporte CHAR(36) NOT NULL,
    ID_Usuario CHAR(36) NOT NULL,
    fecha_hora DATETIME NOT NULL,
    mensaje TEXT NOT NULL,
    leida BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (ID_Reporte) REFERENCES reporte(ID_Reporte),
    FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario)
);

-- Tabla: comentario (con UUID)
CREATE TABLE comentario (
    ID_Comentario CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    ID_Reporte CHAR(36) NOT NULL,
    ID_Usuario_Emisor CHAR(36) NOT NULL, 
    fecha_hora DATETIME NOT NULL,
    comentario TEXT NOT NULL,
    FOREIGN KEY (ID_Reporte) REFERENCES reporte(ID_Reporte),
    FOREIGN KEY (ID_Usuario_Emisor) REFERENCES usuario(ID_Usuario)
);

-- Tabla: recaudaciones (con UUID)
CREATE TABLE recaudaciones (
   ID_Recaudacion CHAR(36) PRIMARY KEY DEFAULT (UUID()),
   Tipo_Comercio ENUM('Minorista', 'Mayorista') NOT NULL, 
   ID_Maquina CHAR(36) NOT NULL,
   ID_Usuario CHAR(36) NOT NULL,
   Monto_Total DECIMAL(10,2) NOT NULL,
   Monto_Empresa DECIMAL(10,2),
   Monto_Comercio DECIMAL(10,2),
   Porcentaje_Comercio DECIMAL(5,2) DEFAULT 0,
    fecha DATETIME NOT NULL,
    detalle TEXT NOT NULL,
    FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario),
    FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina)
);

-- Tabla: informe (con UUID)
CREATE TABLE IF NOT EXISTS informes_recaudacion (
  ID_Informe CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  ID_Recaudacion CHAR(36) NOT NULL,
  CI_Usuario VARCHAR(100) NOT NULL,
  Nombre_Maquina VARCHAR(100) NOT NULL,
  ID_Comercio CHAR(36) NOT NULL,
  Nombre_Comercio VARCHAR(100) NOT NULL,
  Direccion_Comercio TEXT NOT NULL,
  Telefono_Comercio VARCHAR(15) NOT NULL,
  Pago_Ensamblador DECIMAL(10,2) DEFAULT 400.00,
  Pago_Comprobador DECIMAL(10,2) DEFAULT 400.00,
  Pago_Mantenimiento DECIMAL(10,2) DEFAULT 400.00,
  empresa_nombre VARCHAR(100) DEFAULT 'recreasys.s.a',
  empresa_descripcion VARCHAR(255) DEFAULT 'Una empresa encargada en el ciclo de vida de las maquinas recreativas',
  FOREIGN KEY (ID_Recaudacion) REFERENCES recaudaciones(ID_Recaudacion),
  FOREIGN KEY (ID_Comercio) REFERENCES Comercio(ID_Comercio)
);

-- Tabla: informe_detalle (con UUID)
CREATE TABLE IF NOT EXISTS informe_detalle (
  ID_Informe_Detalle CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  ID_Informe CHAR(36) NOT NULL,
  ID_Componente CHAR(36) NOT NULL,
  FOREIGN KEY (ID_Informe) REFERENCES informes_recaudacion(ID_Informe),
  FOREIGN KEY (ID_Componente) REFERENCES componente(ID_Componente)
);

-- Tabla: distribuciones (con UUID)
CREATE TABLE IF NOT EXISTS informe_distribucion (
  ID_Distribucion CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  ID_Maquina CHAR(36) NOT NULL,
  ID_Usuario_Comprobador CHAR(36) NOT NULL,
  ID_Comercio CHAR(36) NOT NULL,
  fecha_alta DATETIME DEFAULT CURRENT_TIMESTAMP,
  fecha_baja DATETIME NULL,
  estado ENUM('Operativa','Retirada','No operativa') DEFAULT 'Operativa',
  FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina),
  FOREIGN KEY (ID_Usuario_Comprobador) REFERENCES usuario(ID_Usuario),
  FOREIGN KEY (ID_Comercio) REFERENCES Comercio(ID_Comercio)
);

-- Tabla: montajes (con UUID)
CREATE TABLE montaje (
    ID_Montaje CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    fecha DATETIME NOT NULL,
    ID_Maquina CHAR(36) NOT NULL, 
    ID_Componente CHAR(36),
    ID_Tecnico CHAR(36),
    detalle TEXT NOT NULL,
    FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina),
    FOREIGN KEY (ID_Componente) REFERENCES componente(ID_Componente),
    FOREIGN KEY (ID_Tecnico) REFERENCES Tecnico(ID_Tecnico)
);
CREATE TABLE historial_maquinas (
    ID_Historial CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    ID_Maquina CHAR(36) NOT NULL,
    ID_Usuario CHAR(36) NOT NULL,

    tipo_usuario ENUM('Tecnico', 'Logistica', 'Administrador') NOT NULL,
    accion VARCHAR(100) NOT NULL,
    descripcion TEXT,

    estado_anterior VARCHAR(50),
    estado_nuevo VARCHAR(50),

    etapa_anterior VARCHAR(50),
    etapa_nueva VARCHAR(50),

    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    detalles_adicionales JSON,

    INDEX idx_historial_maquina (ID_Maquina),
    INDEX idx_historial_usuario (ID_Usuario),
    INDEX idx_historial_fecha (fecha_hora),

    CONSTRAINT fk_historial_maquina 
        FOREIGN KEY (ID_Maquina) 
        REFERENCES MaquinaRecreativa(ID_Maquina) 
        ON DELETE CASCADE,

    CONSTRAINT fk_historial_usuario 
        FOREIGN KEY (ID_Usuario) 
        REFERENCES usuario(ID_Usuario) 
        ON DELETE CASCADE

);
ALTER TABLE usuario MODIFY usuario_asignado VARCHAR(25) NOT NULL UNIQUE DEFAULT 'Aun no tiene';
ALTER TABLE usuario
ADD COLUMN fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE historial_maquinas MODIFY ID_Maquina CHAR(36) NULL;
-- Añadir columnas para edición y soft delete
ALTER TABLE comentario 
ADD COLUMN fecha_edicion DATETIME NULL,
ADD COLUMN eliminado BOOLEAN DEFAULT FALSE;

-- Crear índice para búsquedas
CREATE INDEX idx_comentario_fecha ON comentario(fecha_hora);
CREATE INDEX idx_comentario_eliminado ON comentario(eliminado);
INSERT INTO componente (tipo, nombre, precio) VALUES
('Logistico', 'Carcasa Arcade Clásica Roja', 150.00),
('Logistico', 'Carcasa Arcade Premium Negra', 200.00),
('Logistico', 'Carcasa Arcade Deluxe con Iluminación', 250.00),
('Logistico', 'Carcasa Compacta para Espacios Pequeños', 120.00),
('Logistico', 'Carcasa Resistente para Exteriores', 180.00),
('Logistico', 'Carcasa Arcade Infantil (Colores Varios)', 130.00),
('Logistico', 'Carcasa Profesional para Torneos', 220.00),
('Logistico', 'Carcasa Móvil con Ruedas', 170.00),
('Logistico', 'Carcasa Personalizable (Base Blanca)', 160.00),
('Logistico', 'Carcasa Arcade XL para 2 Jugadores', 300.00);

-- =============================================
-- INSERTAR COMPONENTES ELECTRONICOS
-- =============================================
INSERT INTO componente (tipo, nombre, precio) VALUES
('Electronico', 'Placa Base Arcade Pro V2', 220.00),
('Electronico', 'Tarjeta Gráfica Arcade 4GB', 180.00),
('Electronico', 'Procesador Intel i3', 110.00),
('Electronico', 'Procesador AMD Ryzen 3', 115.00),
('Electronico', 'RAM DDR4 8GB Kit', 45.00),
('Electronico', 'Disco SSD 240GB', 55.00),
('Electronico', 'Fuente de Alimentación 600W Certificada', 95.00),
('Electronico', 'Convertidor de Video HDMI a VGA', 30.00),
('Electronico', 'Tarjeta de sonido 5.1', 40.00),
('Electronico', 'Módulo Wi-Fi integrado', 20.00),
('Electronico', 'Módulo Bluetooth 5.0', 15.00),
('Electronico', 'Sensor de proximidad IR', 12.00),
('Electronico', 'Módulo de cámara VGA', 30.00),
('Electronico', 'Receptor IR para mandos', 10.00),
('Electronico', 'Amplificador de audio 20W', 35.00),
('Electronico', 'Módulo de iluminación RGB', 28.00),
('Electronico', 'Batería de respaldo 5V', 14.00),
('Electronico', 'Sensor térmico NTC', 8.00),
('Electronico', 'Resistencia 10 Ω', 1.50),
('Electronico', 'Condensador 1000 µF', 2.00),
('Electronico', 'Diodo rectificador (5 pz)', 4.00),
('Electronico', 'LED recambio (pack 20)', 6.00),
('Electronico', 'Adaptador DC Jack', 5.50);

-- =============================================
-- INSERTAR COMPONENTES ESTRUCTURALES
-- =============================================
INSERT INTO componente (tipo, nombre, precio) VALUES
('Estructural', 'Carcasa Arcade Premium Negro', 150.00),
('Estructural', 'Carcasa Arcade Premium Blanco', 150.00),
('Estructural', 'Carcasa Arcade Compacta', 120.00),
('Estructural', 'Panel de Control con 8 Botones', 45.00),
('Estructural', 'Joystick Industrial con Botones RGB', 55.00),
('Estructural', 'Soporte metálico para placa', 9.00),
('Estructural', 'Kit tornillería acero M3', 16.00),
('Estructural', 'Taco antivibración de goma', 4.00),
('Estructural', 'Soporte para ventilador', 7.00),
('Estructural', 'Pata ajustable para carcasa', 6.00),
('Estructural', 'Marco de metal para monitor', 25.00),
('Estructural', 'Base de apoyo antideslizante', 12.00),
('Estructural', 'Bisagras para panel de servicio', 8.00),
('Estructural', 'Manija de transporte', 10.00),
('Estructural', 'Rejilla de ventilación', 5.00),
('Estructural', 'Soporte para tarjeta madre', 15.00),
('Estructural', 'Guía para cables', 4.00),
('Estructural', 'Clip organizador de cables', 3.00),
('Estructural', 'Protector de esquinas', 6.00),
('Estructural', 'Pieza de unión estructural', 7.00);

-- =============================================
-- INSERTAR COMPONENTES ACCESORIOS
-- =============================================
INSERT INTO componente (tipo, nombre, precio) VALUES
('Accesorio', 'Monitor LED 32" Pantalla Táctil', 250.00),
('Accesorio', 'Monitor secundario 7"', 60.00),
('Accesorio', 'Kit Cableado Premium con Conectores Dorados', 35.00),
('Accesorio', 'Sistema de Refrigeración Liquida', 120.00),
('Accesorio', 'Kit de Montaje Completo para Arcade', 75.00),
('Accesorio', 'Cable HDMI Premium 2m', 12.00),
('Accesorio', 'Adaptador USB a DB9', 8.50),
('Accesorio', 'Altavoces estéreo USB', 25.00),
('Accesorio', 'Teclado numérico auxiliar', 18.00),
('Accesorio', 'Panel LED de señalización', 22.00),
('Accesorio', 'Kit Reparación Premium 50 Piezas', 50.00),
('Accesorio', 'Lubricante Industrial Especial', 25.00),
('Accesorio', 'Set Limpieza Profesional para Arcade', 30.00),
('Accesorio', 'Pasta Térmica de Alto Rendimiento', 15.00),
('Accesorio', 'Kit de Reparación para Pantallas', 60.00),
('Accesorio', 'Repuestos para Joysticks (Pack 10)', 20.00),
('Accesorio', 'Botones de Reemplazo RGB (Pack 20)', 35.00),
('Accesorio', 'Ventiladores de Refrigeración 120mm', 18.00),
('Accesorio', 'Cintas Aislantes y Termorretráctiles', 12.00),
('Accesorio', 'Kit Emergencia para Fuentes', 40.00),
('Accesorio', 'Fusible rápido 5A', 2.50),
('Accesorio', 'Fusible rápido 3A', 2.00),
('Accesorio', 'Conector de repuesto HDMI', 5.00),
('Accesorio', 'Switch de encendido', 7.00),
('Accesorio', 'Panel de botones recambio', 12.00),
('Accesorio', 'Cable de alimentación IEC', 6.00),
('Accesorio', 'Correa para ventilador', 3.50),
('Accesorio', 'Disipador con ventilador', 14.00),
('Accesorio', 'Conector Molex 4 pines', 3.50),
('Accesorio', 'Cinta térmica Kapton', 7.50),
('Accesorio', 'Estuche porta-fusibles', 8.00);


SELECT tipo, COUNT(*) as cantidad FROM componente GROUP BY tipo;