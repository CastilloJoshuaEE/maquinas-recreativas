-- =============================================
-- CREAR USUARIO EN POSTGRESQL
-- =============================================
-- Primero, conectar con un usuario con privilegios de administrador
-- CREATE USER recrea_user WITH PASSWORD 'recrea_pass123';
-- GRANT ALL PRIVILEGES ON DATABASE bd_recrea_sys TO recrea_user;
-- Nota: En Supabase, los usuarios se manejan de manera diferente

-- =============================================
-- SELECCIONAR BASE DE DATOS (en PostgreSQL no se usa USE)
-- =============================================
-- Simplemente conéctate a la base de datos bd_recrea_sys

-- =============================================
-- CREAR EXTENSIONES NECESARIAS
-- =============================================
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- =============================================
-- TABLA: USUARIO
-- =============================================
CREATE TABLE usuario (
    ID_Usuario UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    ci VARCHAR(100) NOT NULL UNIQUE,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    tipo VARCHAR(20) NOT NULL CHECK (tipo IN ('Administrador', 'Logistica', 'Tecnico', 'Contabilidad', 'Usuario')),
    usuario_asignado VARCHAR(25) NOT NULL DEFAULT 'Aun no tiene' UNIQUE,
    contrasena VARCHAR(255) NOT NULL DEFAULT 'Aun no tiene',
    estado VARCHAR(25) NOT NULL DEFAULT 'Pendiente de asignacion' CHECK (estado IN ('Pendiente de asignacion', 'Activo', 'Inhabilitado')),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- TABLA: INICIO_SESION
-- =============================================
CREATE TABLE inicio_sesion (
    ID_Inicio_Sesion UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    ID_Usuario UUID NOT NULL,
    usuario_asignado VARCHAR(100) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_ultima_sesion TIMESTAMP NULL,
    FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario)
);

-- =============================================
-- TABLA: TECNICO
-- =============================================
CREATE TABLE Tecnico(
    ID_Tecnico UUID PRIMARY KEY,
    Especialidad VARCHAR(20) NOT NULL CHECK (Especialidad IN ('Ensamblador', 'Comprobador', 'Mantenimiento')),
    Cantidad_Actividades INT DEFAULT 0 NOT NULL,
    FOREIGN KEY (ID_Tecnico) REFERENCES usuario(ID_Usuario)
);

-- =============================================
-- TABLA: LOGISTICA
-- =============================================
CREATE TABLE Logistica(
    ID_Logistica UUID PRIMARY KEY,
    FOREIGN KEY (ID_Logistica) REFERENCES usuario(ID_Usuario)
);

-- =============================================
-- HISTORIAL DE ACTIVIDADES
-- =============================================
CREATE TABLE historial_actividades (
    ID_Historial_Actividades UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    ID_Usuario UUID NOT NULL,
    descripcion TEXT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario)
);

-- =============================================
-- TABLA: COMERCIO
-- =============================================
CREATE TABLE Comercio (
    ID_Comercio UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    Nombre VARCHAR(100) NOT NULL UNIQUE,
    Tipo VARCHAR(20) NOT NULL CHECK (Tipo IN ('Minorista', 'Mayorista')),
    Direccion TEXT NOT NULL,
    Telefono VARCHAR(15) NOT NULL UNIQUE,
    Cantidad_Maquinas INT DEFAULT 0 NOT NULL,
    Fecha_Registro DATE NOT NULL
);

-- =============================================
-- TABLA: MAQUINARECREATIVA
-- =============================================
CREATE TABLE MaquinaRecreativa (
    ID_Maquina UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    Nombre_Maquina VARCHAR(100) NOT NULL,
    Tipo VARCHAR(50) NOT NULL,
    Etapa VARCHAR(20) NOT NULL DEFAULT 'Montaje' CHECK (Etapa IN ('Montaje', 'Distribucion', 'Recaudacion')),
    Estado VARCHAR(25) NOT NULL DEFAULT 'Ensamblandose' CHECK (Estado IN ('Ensamblandose', 'Comprobandose', 'Reensamblandose', 'Distribuyendose', 'Operativa', 'No operativa', 'Retirada')),
    Fecha_Registro DATE NOT NULL,
    ID_Tecnico_Ensamblador UUID NOT NULL,
    ID_Tecnico_Comprobador UUID NOT NULL,
    ID_Comercio UUID NOT NULL,
    ID_Tecnico_Mantenimiento UUID,
    FOREIGN KEY (ID_Tecnico_Ensamblador) REFERENCES Tecnico(ID_Tecnico),
    FOREIGN KEY (ID_Tecnico_Comprobador) REFERENCES Tecnico(ID_Tecnico),
    FOREIGN KEY (ID_Comercio) REFERENCES Comercio(ID_Comercio),
    FOREIGN KEY (ID_Tecnico_Mantenimiento) REFERENCES Tecnico(ID_Tecnico)
);

-- =============================================
-- TABLA: NOTIFICACIONMAQUINARECREATIVA
-- =============================================
CREATE TABLE NotificacionMaquinaRecreativa (
    ID_Notificacion UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    ID_Remitente UUID NOT NULL,
    ID_Destinatario UUID NOT NULL,
    ID_Maquina UUID NOT NULL,
    Tipo VARCHAR(40) NOT NULL CHECK (Tipo IN (
        'Nuevo montaje',
        'Comprobar maquina recreativa',
        'Reensamblar maquina recreativa',
        'Distribuir maquina recreativa',
        'Dar mantenimiento a maquina recreativa',
        'Maquina recreativa retirada',
        'Maquina recreativa reparada'
    )),
    Mensaje TEXT,
    Fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Estado VARCHAR(10) NOT NULL DEFAULT 'No leido' CHECK (Estado IN ('Leido', 'No leido')),
    FOREIGN KEY (ID_Remitente) REFERENCES usuario(ID_Usuario),
    FOREIGN KEY (ID_Destinatario) REFERENCES usuario(ID_Usuario),
    FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina)
);

CREATE INDEX idx_notificacion_maquina_estado ON NotificacionMaquinaRecreativa(Estado);

-- =============================================
-- TABLA: COMPONENTE
-- =============================================
CREATE TABLE componente (
    ID_Componente UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    tipo VARCHAR(20) NOT NULL CHECK (tipo IN ('Logistico', 'Electronico', 'Estructural', 'Accesorio')),
    nombre VARCHAR(50) NOT NULL,
    precio DECIMAL(10,2) DEFAULT 10.00
);

-- =============================================
-- TABLA: COMPONENTE_USUARIO
-- =============================================
CREATE TABLE componente_usuario (
    ID_Registro UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    ID_Componente UUID NOT NULL,
    ID_Usuario UUID NOT NULL,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_liberacion TIMESTAMP NULL,
    ID_Maquina UUID NULL,
    FOREIGN KEY (ID_Componente) REFERENCES componente(ID_Componente),
    FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario),
    FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina)
);

-- =============================================
-- TABLA: REPORTE
-- =============================================
CREATE TABLE reporte (
    ID_Reporte UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    ID_Usuario_Emisor UUID NOT NULL,
    ID_Usuario_Destinatario UUID,
    fecha_hora TIMESTAMP NOT NULL,
    descripcion TEXT NOT NULL,
    estado VARCHAR(15) NOT NULL,
    FOREIGN KEY (ID_Usuario_Emisor) REFERENCES usuario(ID_Usuario),
    FOREIGN KEY (ID_Usuario_Destinatario) REFERENCES usuario(ID_Usuario)
);

-- =============================================
-- TABLA: NOTIFICACIONES
-- =============================================
CREATE TABLE notificaciones (
    ID_Notificaciones UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    ID_Reporte UUID NOT NULL,
    ID_Usuario UUID NOT NULL,
    fecha_hora TIMESTAMP NOT NULL,
    mensaje TEXT NOT NULL,
    leida BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (ID_Reporte) REFERENCES reporte(ID_Reporte),
    FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario)
);

-- =============================================
-- TABLA: COMENTARIO
-- =============================================
CREATE TABLE comentario (
    ID_Comentario UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    ID_Reporte UUID NOT NULL,
    ID_Usuario_Emisor UUID NOT NULL, 
    fecha_hora TIMESTAMP NOT NULL,
    comentario TEXT NOT NULL,
    fecha_edicion TIMESTAMP NULL,
    eliminado BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (ID_Reporte) REFERENCES reporte(ID_Reporte),
    FOREIGN KEY (ID_Usuario_Emisor) REFERENCES usuario(ID_Usuario)
);

CREATE INDEX idx_comentario_fecha ON comentario(fecha_hora);
CREATE INDEX idx_comentario_eliminado ON comentario(eliminado);

-- =============================================
-- TABLA: RECAUDACIONES
-- =============================================
CREATE TABLE recaudaciones (
   ID_Recaudacion UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
   Tipo_Comercio VARCHAR(20) NOT NULL CHECK (Tipo_Comercio IN ('Minorista', 'Mayorista')), 
   ID_Maquina UUID NOT NULL,
   ID_Usuario UUID NOT NULL,
   Monto_Total DECIMAL(10,2) NOT NULL,
   Monto_Empresa DECIMAL(10,2),
   Monto_Comercio DECIMAL(10,2),
   Porcentaje_Comercio DECIMAL(5,2) DEFAULT 0,
   fecha TIMESTAMP NOT NULL,
   detalle TEXT NOT NULL,
   FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario),
   FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina)
);

-- =============================================
-- TABLA: INFORMES_RECAUDACION
-- =============================================
CREATE TABLE informes_recaudacion (
  ID_Informe UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  ID_Recaudacion UUID NOT NULL,
  CI_Usuario VARCHAR(100) NOT NULL,
  Nombre_Maquina VARCHAR(100) NOT NULL,
  ID_Comercio UUID NOT NULL,
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

-- =============================================
-- TABLA: INFORME_DETALLE
-- =============================================
CREATE TABLE informe_detalle (
  ID_Informe_Detalle UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  ID_Informe UUID NOT NULL,
  ID_Componente UUID NOT NULL,
  FOREIGN KEY (ID_Informe) REFERENCES informes_recaudacion(ID_Informe),
  FOREIGN KEY (ID_Componente) REFERENCES componente(ID_Componente)
);

-- =============================================
-- TABLA: INFORME_DISTRIBUCION
-- =============================================
CREATE TABLE informe_distribucion (
  ID_Distribucion UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  ID_Maquina UUID NOT NULL,
  ID_Usuario_Comprobador UUID NOT NULL,
  ID_Comercio UUID NOT NULL,
  fecha_alta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  fecha_baja TIMESTAMP NULL,
  estado VARCHAR(15) DEFAULT 'Operativa' CHECK (estado IN ('Operativa','Retirada','No operativa')),
  FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina),
  FOREIGN KEY (ID_Usuario_Comprobador) REFERENCES usuario(ID_Usuario),
  FOREIGN KEY (ID_Comercio) REFERENCES Comercio(ID_Comercio)
);

-- =============================================
-- TABLA: MONTAJE
-- =============================================
CREATE TABLE montaje (
    ID_Montaje UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    fecha TIMESTAMP NOT NULL,
    ID_Maquina UUID NOT NULL, 
    ID_Componente UUID,
    ID_Tecnico UUID,
    detalle TEXT NOT NULL,
    FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina),
    FOREIGN KEY (ID_Componente) REFERENCES componente(ID_Componente),
    FOREIGN KEY (ID_Tecnico) REFERENCES Tecnico(ID_Tecnico)
);

-- =============================================
-- TABLA: HISTORIAL_MAQUINAS
-- =============================================
CREATE TABLE historial_maquinas (
    ID_Historial UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    ID_Maquina UUID,
    ID_Usuario UUID NOT NULL,
    tipo_usuario VARCHAR(20) NOT NULL CHECK (tipo_usuario IN ('Tecnico', 'Logistica', 'Administrador')),
    accion VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado_anterior VARCHAR(50),
    estado_nuevo VARCHAR(50),
    etapa_anterior VARCHAR(50),
    etapa_nueva VARCHAR(50),
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    detalles_adicionales JSONB,
    CONSTRAINT fk_historial_maquina 
        FOREIGN KEY (ID_Maquina) 
        REFERENCES MaquinaRecreativa(ID_Maquina) 
        ON DELETE CASCADE,
    CONSTRAINT fk_historial_usuario 
        FOREIGN KEY (ID_Usuario) 
        REFERENCES usuario(ID_Usuario) 
        ON DELETE CASCADE
);

CREATE INDEX idx_historial_maquina ON historial_maquinas(ID_Maquina);
CREATE INDEX idx_historial_usuario ON historial_maquinas(ID_Usuario);
CREATE INDEX idx_historial_fecha ON historial_maquinas(fecha_hora);

-- =============================================
-- INSERTAR DATOS DE COMPONENTES
-- =============================================
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
('Logistico', 'Carcasa Arcade XL para 2 Jugadores', 300.00),

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
('Electronico', 'Adaptador DC Jack', 5.50),

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
('Estructural', 'Pieza de unión estructural', 7.00),

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

-- =============================================
-- CONSULTAS DE VERIFICACIÓN
-- =============================================
SELECT tipo, COUNT(*) as cantidad FROM componente GROUP BY tipo;