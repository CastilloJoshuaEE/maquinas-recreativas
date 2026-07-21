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
-- ============================================================
-- STORED FUNCTIONS Y TRIGGERS - PostgreSQL/Supabase
-- Compatibles con PostgreSQL 14+
-- ============================================================

-- ==============================================================
-- 1. MODULO USUARIO
-- ==============================================================

-- 1.1 Buscar usuario por usuario_asignado
CREATE OR REPLACE FUNCTION sp_buscar_usuario_por_username(p_username VARCHAR(25))
RETURNS TABLE(
    ID_Usuario UUID,
    ci VARCHAR(100),
    nombre VARCHAR(50),
    apellido VARCHAR(50),
    email VARCHAR(100),
    tipo VARCHAR(20),
    usuario_asignado VARCHAR(25),
    contrasena VARCHAR(255),
    estado VARCHAR(25),
    fecha_registro TIMESTAMP,
    Especialidad VARCHAR(20),
    Cantidad_Actividades INT
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        u.ID_Usuario,
        u.ci,
        u.nombre,
        u.apellido,
        u.email,
        u.tipo,
        u.usuario_asignado,
        u.contrasena,
        u.estado,
        u.fecha_registro,
        t.Especialidad,
        t.Cantidad_Actividades
    FROM usuario u
    LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
    WHERE u.usuario_asignado = p_username
    LIMIT 1;
END;
$$ LANGUAGE plpgsql;

-- 1.2 Buscar usuario por email
CREATE OR REPLACE FUNCTION sp_buscar_usuario_por_email(p_email VARCHAR(255))
RETURNS TABLE(
    ID_Usuario UUID,
    ci VARCHAR(100),
    nombre VARCHAR(50),
    apellido VARCHAR(50),
    email VARCHAR(100),
    tipo VARCHAR(20),
    usuario_asignado VARCHAR(25),
    contrasena VARCHAR(255),
    estado VARCHAR(25),
    fecha_registro TIMESTAMP,
    Especialidad VARCHAR(20),
    Cantidad_Actividades INT
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        u.ID_Usuario,
        u.ci,
        u.nombre,
        u.apellido,
        u.email,
        u.tipo,
        u.usuario_asignado,
        u.contrasena,
        u.estado,
        u.fecha_registro,
        t.Especialidad,
        t.Cantidad_Actividades
    FROM usuario u
    LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
    WHERE u.email = p_email
    LIMIT 1;
END;
$$ LANGUAGE plpgsql;

-- 1.3 Buscar usuario por ID
CREATE OR REPLACE FUNCTION sp_buscar_usuario_por_id(p_id UUID)
RETURNS TABLE(
    ID_Usuario UUID,
    ci VARCHAR(100),
    nombre VARCHAR(50),
    apellido VARCHAR(50),
    email VARCHAR(100),
    tipo VARCHAR(20),
    usuario_asignado VARCHAR(25),
    contrasena VARCHAR(255),
    estado VARCHAR(25),
    fecha_registro TIMESTAMP,
    Especialidad VARCHAR(20),
    Cantidad_Actividades INT
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        u.ID_Usuario,
        u.ci,
        u.nombre,
        u.apellido,
        u.email,
        u.tipo,
        u.usuario_asignado,
        u.contrasena,
        u.estado,
        u.fecha_registro,
        t.Especialidad,
        t.Cantidad_Actividades
    FROM usuario u
    LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
    WHERE u.ID_Usuario = p_id
    LIMIT 1;
END;
$$ LANGUAGE plpgsql;

-- 1.4 Insertar usuario
CREATE OR REPLACE FUNCTION sp_insertar_usuario(
    p_id UUID,
    p_nombre VARCHAR(50),
    p_apellido VARCHAR(50),
    p_ci VARCHAR(100),
    p_email VARCHAR(100),
    p_username VARCHAR(25),
    p_contrasena VARCHAR(255),
    p_tipo VARCHAR(20),
    p_estado VARCHAR(25)
)
RETURNS VOID AS $$
BEGIN
    INSERT INTO usuario (
        ID_Usuario, nombre, apellido, ci, email, 
        usuario_asignado, contrasena, tipo, estado
    ) VALUES (
        p_id, p_nombre, p_apellido, p_ci, p_email, 
        p_username, p_contrasena, p_tipo, p_estado
    );
END;
$$ LANGUAGE plpgsql;

-- 1.5 Actualizar usuario
CREATE OR REPLACE FUNCTION sp_actualizar_usuario(
    p_id UUID,
    p_nombre VARCHAR(50),
    p_apellido VARCHAR(50),
    p_ci VARCHAR(100),
    p_email VARCHAR(100),
    p_username VARCHAR(25),
    p_contrasena VARCHAR(255),
    p_tipo VARCHAR(20),
    p_estado VARCHAR(25)
)
RETURNS VOID AS $$
BEGIN
    UPDATE usuario
    SET nombre = p_nombre,
        apellido = p_apellido,
        ci = p_ci,
        email = p_email,
        usuario_asignado = p_username,
        contrasena = p_contrasena,
        tipo = p_tipo,
        estado = p_estado
    WHERE ID_Usuario = p_id;
END;
$$ LANGUAGE plpgsql;

-- 1.6 Cambiar estado de usuario
CREATE OR REPLACE FUNCTION sp_cambiar_estado_usuario(
    p_id UUID,
    p_estado VARCHAR(25)
)
RETURNS VOID AS $$
BEGIN
    UPDATE usuario SET estado = p_estado WHERE ID_Usuario = p_id;
END;
$$ LANGUAGE plpgsql;

-- 1.7 Cambiar contrasena
CREATE OR REPLACE FUNCTION sp_cambiar_contrasena_usuario(
    p_id UUID,
    p_contrasena VARCHAR(255)
)
RETURNS VOID AS $$
BEGIN
    UPDATE usuario SET contrasena = p_contrasena WHERE ID_Usuario = p_id;
END;
$$ LANGUAGE plpgsql;

-- 1.8 Actualizar username
CREATE OR REPLACE FUNCTION sp_actualizar_username(
    p_id UUID,
    p_username VARCHAR(25)
)
RETURNS VOID AS $$
BEGIN
    UPDATE usuario SET usuario_asignado = p_username WHERE ID_Usuario = p_id;
END;
$$ LANGUAGE plpgsql;

-- 1.9 Eliminar usuario (cascada manual)
CREATE OR REPLACE FUNCTION sp_eliminar_usuario(p_id UUID)
RETURNS VOID AS $$
BEGIN
    DELETE FROM comentario WHERE ID_Usuario_Emisor = p_id;
    DELETE FROM notificaciones WHERE ID_Usuario = p_id;
    DELETE FROM NotificacionMaquinaRecreativa WHERE ID_Destinatario = p_id;
    DELETE FROM componente_usuario WHERE ID_Usuario = p_id;
    DELETE FROM inicio_sesion WHERE ID_Usuario = p_id;
    DELETE FROM historial_actividades WHERE ID_Usuario = p_id;
    DELETE FROM Tecnico WHERE ID_Tecnico = p_id;
    DELETE FROM Logistica WHERE ID_Logistica = p_id;
    DELETE FROM reporte WHERE ID_Usuario_Emisor = p_id OR ID_Usuario_Destinatario = p_id;
    DELETE FROM usuario WHERE ID_Usuario = p_id;
END;
$$ LANGUAGE plpgsql;

-- 1.10 Listar usuarios con filtros
CREATE OR REPLACE FUNCTION sp_listar_usuarios(
    p_tipo VARCHAR(20),
    p_estado VARCHAR(30),
    p_ci VARCHAR(100),
    p_limit INT,
    p_offset INT
)
RETURNS TABLE(
    ID_Usuario UUID,
    ci VARCHAR(100),
    nombre VARCHAR(50),
    apellido VARCHAR(50),
    email VARCHAR(100),
    tipo VARCHAR(20),
    usuario_asignado VARCHAR(25),
    contrasena VARCHAR(255),
    estado VARCHAR(25),
    fecha_registro TIMESTAMP,
    Especialidad VARCHAR(20),
    Cantidad_Actividades INT
) AS $$
DECLARE
    sql_query TEXT;
BEGIN
    sql_query := 'SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                  FROM usuario u 
                  LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                  WHERE 1=1';
    
    IF p_tipo IS NOT NULL AND p_tipo != '' THEN
        sql_query := sql_query || ' AND u.tipo = ''' || p_tipo || '''';
    END IF;
    
    IF p_estado IS NOT NULL AND p_estado != '' THEN
        sql_query := sql_query || ' AND u.estado = ''' || p_estado || '''';
    END IF;
    
    IF p_ci IS NOT NULL AND p_ci != '' THEN
        sql_query := sql_query || ' AND u.ci = ''' || p_ci || '''';
    END IF;
    
    sql_query := sql_query || ' ORDER BY u.nombre ASC';
    sql_query := sql_query || ' LIMIT ' || p_limit || ' OFFSET ' || p_offset;
    
    RETURN QUERY EXECUTE sql_query;
END;
$$ LANGUAGE plpgsql;

-- 1.11 Listar usuarios por tipo
CREATE OR REPLACE FUNCTION sp_listar_usuarios_por_tipo(
    p_tipo VARCHAR(20),
    p_excluir UUID
)
RETURNS TABLE(
    ID_Usuario UUID,
    ci VARCHAR(100),
    nombre VARCHAR(50),
    apellido VARCHAR(50),
    email VARCHAR(100),
    tipo VARCHAR(20),
    usuario_asignado VARCHAR(25),
    contrasena VARCHAR(255),
    estado VARCHAR(25),
    fecha_registro TIMESTAMP,
    Especialidad VARCHAR(20),
    Cantidad_Actividades INT
) AS $$
BEGIN
    IF p_excluir IS NULL THEN
        RETURN QUERY
        SELECT u.*, t.Especialidad, t.Cantidad_Actividades
        FROM usuario u
        LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
        WHERE u.tipo = p_tipo
        ORDER BY u.nombre ASC;
    ELSE
        RETURN QUERY
        SELECT u.*, t.Especialidad, t.Cantidad_Actividades
        FROM usuario u
        LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
        WHERE u.tipo = p_tipo AND u.ID_Usuario != p_excluir
        ORDER BY u.nombre ASC;
    END IF;
END;
$$ LANGUAGE plpgsql;

-- 1.12 Verificar existencia por email
CREATE OR REPLACE FUNCTION sp_existe_email(p_email VARCHAR(255))
RETURNS INTEGER AS $$
DECLARE
    v_existe INTEGER;
BEGIN
    SELECT COUNT(*) INTO v_existe FROM usuario WHERE email = p_email;
    RETURN v_existe;
END;
$$ LANGUAGE plpgsql;

-- 1.13 Verificar existencia por CI
CREATE OR REPLACE FUNCTION sp_existe_ci(p_ci VARCHAR(100))
RETURNS INTEGER AS $$
DECLARE
    v_existe INTEGER;
BEGIN
    SELECT COUNT(*) INTO v_existe FROM usuario WHERE ci = p_ci;
    RETURN v_existe;
END;
$$ LANGUAGE plpgsql;

-- 1.14 Verificar existencia por username
CREATE OR REPLACE FUNCTION sp_existe_username(p_username VARCHAR(25))
RETURNS INTEGER AS $$
DECLARE
    v_existe INTEGER;
BEGIN
    SELECT COUNT(*) INTO v_existe FROM usuario WHERE usuario_asignado = p_username;
    RETURN v_existe;
END;
$$ LANGUAGE plpgsql;

-- 1.15 Verificar existencia por username excluyendo ID
CREATE OR REPLACE FUNCTION sp_existe_username_excluyendo_id(
    p_username VARCHAR(25),
    p_id UUID
)
RETURNS INTEGER AS $$
DECLARE
    v_existe INTEGER;
BEGIN
    SELECT COUNT(*) INTO v_existe
    FROM usuario
    WHERE usuario_asignado = p_username AND ID_Usuario != p_id;
    RETURN v_existe;
END;
$$ LANGUAGE plpgsql;

-- 1.16 Estadisticas de usuarios
CREATE OR REPLACE FUNCTION sp_estadisticas_usuarios()
RETURNS TABLE(
    total BIGINT,
    administradores BIGINT,
    tecnicos BIGINT,
    logistica BIGINT,
    contabilidad BIGINT,
    usuarios_normales BIGINT,
    activos BIGINT,
    inhabilitados BIGINT,
    pendientes BIGINT
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        (SELECT COUNT(*) FROM usuario) AS total,
        (SELECT COUNT(*) FROM usuario WHERE tipo = 'Administrador') AS administradores,
        (SELECT COUNT(*) FROM usuario WHERE tipo = 'Tecnico') AS tecnicos,
        (SELECT COUNT(*) FROM usuario WHERE tipo = 'Logistica') AS logistica,
        (SELECT COUNT(*) FROM usuario WHERE tipo = 'Contabilidad') AS contabilidad,
        (SELECT COUNT(*) FROM usuario WHERE tipo = 'Usuario') AS usuarios_normales,
        (SELECT COUNT(*) FROM usuario WHERE estado = 'Activo') AS activos,
        (SELECT COUNT(*) FROM usuario WHERE estado = 'Inhabilitado') AS inhabilitados,
        (SELECT COUNT(*) FROM usuario WHERE estado = 'Pendiente de asignacion') AS pendientes;
END;
$$ LANGUAGE plpgsql;

-- 1.17 Registrar actividad de usuario
CREATE OR REPLACE FUNCTION sp_registrar_actividad(
    p_id_usuario UUID,
    p_descripcion TEXT
)
RETURNS VOID AS $$
BEGIN
    INSERT INTO historial_actividades (ID_Usuario, descripcion)
    VALUES (p_id_usuario, p_descripcion);
END;
$$ LANGUAGE plpgsql;

-- 1.18 Obtener historial de actividades
CREATE OR REPLACE FUNCTION sp_obtener_historial_actividades(
    p_id_usuario UUID,
    p_limite INT
)
RETURNS TABLE(
    ID_Historial_Actividades UUID,
    ID_Usuario UUID,
    descripcion TEXT,
    fecha_registro TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    SELECT * FROM historial_actividades
    WHERE ID_Usuario = p_id_usuario
    ORDER BY fecha_registro DESC
    LIMIT p_limite;
END;
$$ LANGUAGE plpgsql;

-- 1.19 Registrar logout
CREATE OR REPLACE FUNCTION sp_registrar_logout(p_id_usuario UUID)
RETURNS VOID AS $$
BEGIN
    UPDATE inicio_sesion
    SET fecha_ultima_sesion = CURRENT_TIMESTAMP
    WHERE ID_Usuario = p_id_usuario
      AND fecha_ultima_sesion IS NULL
      AND fecha_inicio = (
          SELECT MAX(fecha_inicio)
          FROM inicio_sesion
          WHERE ID_Usuario = p_id_usuario
            AND fecha_ultima_sesion IS NULL
      );
END;
$$ LANGUAGE plpgsql;

-- ==============================================================
-- 2. MODULO TECNICO
-- ==============================================================

-- 2.1 Insertar tecnico
CREATE OR REPLACE FUNCTION sp_insertar_tecnico(
    p_id UUID,
    p_especialidad VARCHAR(20),
    p_actividades INT
)
RETURNS VOID AS $$
BEGIN
    INSERT INTO Tecnico (ID_Tecnico, Especialidad, Cantidad_Actividades)
    VALUES (p_id, p_especialidad, p_actividades)
    ON CONFLICT (ID_Tecnico) DO UPDATE
    SET Especialidad = EXCLUDED.Especialidad,
        Cantidad_Actividades = EXCLUDED.Cantidad_Actividades;
END;
$$ LANGUAGE plpgsql;

-- 2.2 Incrementar actividades de tecnico
CREATE OR REPLACE FUNCTION sp_incrementar_actividades_tecnico(p_id UUID)
RETURNS VOID AS $$
BEGIN
    UPDATE Tecnico
    SET Cantidad_Actividades = Cantidad_Actividades + 1
    WHERE ID_Tecnico = p_id;
END;
$$ LANGUAGE plpgsql;

-- 2.3 Tecnicos por especialidad
CREATE OR REPLACE FUNCTION sp_tecnicos_por_especialidad(p_especialidad VARCHAR(20))
RETURNS TABLE(
    ID_Usuario UUID,
    nombre VARCHAR(50),
    apellido VARCHAR(50),
    usuario_asignado VARCHAR(25),
    tipo VARCHAR(20),
    estado VARCHAR(25),
    especialidad VARCHAR(20),
    cantidad_actividades INT
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        u.ID_Usuario,
        u.nombre,
        u.apellido,
        u.usuario_asignado,
        u.tipo,
        u.estado,
        t.Especialidad AS especialidad,
        t.Cantidad_Actividades AS cantidad_actividades
    FROM usuario u
    INNER JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
    WHERE t.Especialidad = p_especialidad
      AND u.estado = 'Activo'
    ORDER BY t.Cantidad_Actividades ASC, u.nombre ASC;
END;
$$ LANGUAGE plpgsql;

-- 2.4 Tecnicos disponibles por especialidad
CREATE OR REPLACE FUNCTION sp_tecnicos_disponibles_por_especialidad(p_especialidad VARCHAR(20))
RETURNS TABLE(
    ID_Usuario UUID,
    ci VARCHAR(100),
    nombre VARCHAR(50),
    apellido VARCHAR(50),
    email VARCHAR(100),
    tipo VARCHAR(20),
    usuario_asignado VARCHAR(25),
    contrasena VARCHAR(255),
    estado VARCHAR(25),
    fecha_registro TIMESTAMP,
    Especialidad VARCHAR(20),
    Cantidad_Actividades INT
) AS $$
BEGIN
    RETURN QUERY
    SELECT u.*, t.Especialidad, t.Cantidad_Actividades
    FROM usuario u
    INNER JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
    WHERE t.Especialidad = p_especialidad
      AND u.estado = 'Activo'
    ORDER BY t.Cantidad_Actividades ASC;
END;
$$ LANGUAGE plpgsql;

-- ==============================================================
-- 3. MODULO COMERCIO
-- ==============================================================

-- 3.1 Insertar comercio
CREATE OR REPLACE FUNCTION sp_insertar_comercio(
    p_id UUID,
    p_nombre VARCHAR(100),
    p_tipo VARCHAR(20),
    p_direccion TEXT,
    p_telefono VARCHAR(15),
    p_fecha_registro DATE
)
RETURNS VOID AS $$
BEGIN
    INSERT INTO Comercio (ID_Comercio, Nombre, Tipo, Direccion, Telefono, Fecha_Registro)
    VALUES (p_id, p_nombre, p_tipo, p_direccion, p_telefono, p_fecha_registro);
END;
$$ LANGUAGE plpgsql;

-- 3.2 Actualizar comercio
CREATE OR REPLACE FUNCTION sp_actualizar_comercio(
    p_id UUID,
    p_nombre VARCHAR(100),
    p_tipo VARCHAR(20),
    p_direccion TEXT,
    p_telefono VARCHAR(15)
)
RETURNS VOID AS $$
BEGIN
    UPDATE Comercio
    SET Nombre = p_nombre,
        Tipo = p_tipo,
        Direccion = p_direccion,
        Telefono = p_telefono
    WHERE ID_Comercio = p_id;
END;
$$ LANGUAGE plpgsql;

-- 3.3 Eliminar comercio
CREATE OR REPLACE FUNCTION sp_eliminar_comercio(p_id UUID)
RETURNS VOID AS $$
DECLARE
    v_maquinas INT;
BEGIN
    SELECT COUNT(*) INTO v_maquinas
    FROM MaquinaRecreativa WHERE ID_Comercio = p_id;

    IF v_maquinas > 0 THEN
        RAISE EXCEPTION 'No se puede eliminar: el comercio tiene maquinas asociadas';
    END IF;

    DELETE FROM Comercio WHERE ID_Comercio = p_id;
END;
$$ LANGUAGE plpgsql;

-- 3.4 Buscar comercio por ID
CREATE OR REPLACE FUNCTION sp_buscar_comercio_por_id(p_id UUID)
RETURNS TABLE(
    ID_Comercio UUID,
    Nombre VARCHAR(100),
    Tipo VARCHAR(20),
    Direccion TEXT,
    Telefono VARCHAR(15),
    Cantidad_Maquinas INT,
    Fecha_Registro DATE
) AS $$
BEGIN
    RETURN QUERY
    SELECT * FROM Comercio WHERE ID_Comercio = p_id LIMIT 1;
END;
$$ LANGUAGE plpgsql;

-- 3.5 Buscar comercio por nombre
CREATE OR REPLACE FUNCTION sp_buscar_comercio_por_nombre(p_nombre VARCHAR(100))
RETURNS TABLE(
    ID_Comercio UUID,
    Nombre VARCHAR(100),
    Tipo VARCHAR(20),
    Direccion TEXT,
    Telefono VARCHAR(15),
    Cantidad_Maquinas INT,
    Fecha_Registro DATE
) AS $$
BEGIN
    RETURN QUERY
    SELECT * FROM Comercio WHERE Nombre = p_nombre LIMIT 1;
END;
$$ LANGUAGE plpgsql;

-- 3.6 Listar comercios
CREATE OR REPLACE FUNCTION sp_listar_comercios(
    p_tipo VARCHAR(20),
    p_nombre VARCHAR(100),
    p_limit INT,
    p_offset INT
)
RETURNS TABLE(
    ID_Comercio UUID,
    Nombre VARCHAR(100),
    Tipo VARCHAR(20),
    Direccion TEXT,
    Telefono VARCHAR(15),
    Cantidad_Maquinas INT,
    Fecha_Registro DATE
) AS $$
DECLARE
    sql_query TEXT;
BEGIN
    sql_query := 'SELECT * FROM Comercio WHERE 1=1';
    
    IF p_tipo IS NOT NULL AND p_tipo != '' THEN
        sql_query := sql_query || ' AND Tipo = ''' || p_tipo || '''';
    END IF;
    
    IF p_nombre IS NOT NULL AND p_nombre != '' THEN
        sql_query := sql_query || ' AND Nombre LIKE ''%' || p_nombre || '%''';
    END IF;
    
    sql_query := sql_query || ' ORDER BY Nombre ASC';
    sql_query := sql_query || ' LIMIT ' || p_limit || ' OFFSET ' || p_offset;
    
    RETURN QUERY EXECUTE sql_query;
END;
$$ LANGUAGE plpgsql;

-- 3.7 Contar comercios
CREATE OR REPLACE FUNCTION sp_contar_comercios(
    p_tipo VARCHAR(20),
    p_nombre VARCHAR(100)
)
RETURNS INTEGER AS $$
DECLARE
    v_total INTEGER;
    sql_query TEXT;
BEGIN
    sql_query := 'SELECT COUNT(*) FROM Comercio WHERE 1=1';
    
    IF p_tipo IS NOT NULL AND p_tipo != '' THEN
        sql_query := sql_query || ' AND Tipo = ''' || p_tipo || '''';
    END IF;
    
    IF p_nombre IS NOT NULL AND p_nombre != '' THEN
        sql_query := sql_query || ' AND Nombre LIKE ''%' || p_nombre || '%''';
    END IF;
    
    EXECUTE sql_query INTO v_total;
    RETURN v_total;
END;
$$ LANGUAGE plpgsql;

-- 3.8 Verificar si comercio tiene maquinas
CREATE OR REPLACE FUNCTION sp_comercio_tiene_maquinas(p_id UUID)
RETURNS INTEGER AS $$
DECLARE
    v_tiene INTEGER;
BEGIN
    SELECT COUNT(*) INTO v_tiene
    FROM MaquinaRecreativa WHERE ID_Comercio = p_id;
    RETURN v_tiene;
END;
$$ LANGUAGE plpgsql;

-- ==============================================================
-- 4. MODULO MAQUINA RECREATIVA
-- ==============================================================

-- 4.1 Insertar maquina
CREATE OR REPLACE FUNCTION sp_insertar_maquina(
    p_id UUID,
    p_nombre VARCHAR(100),
    p_tipo VARCHAR(50),
    p_fecha_registro DATE,
    p_estado VARCHAR(50),
    p_etapa VARCHAR(50),
    p_id_comercio UUID,
    p_id_tecnico_ensamblador UUID,
    p_id_tecnico_comprobador UUID,
    p_id_tecnico_mant UUID
)
RETURNS VOID AS $$
BEGIN
    INSERT INTO MaquinaRecreativa (
        ID_Maquina, Nombre_Maquina, Tipo, Fecha_Registro,
        Estado, Etapa, ID_Comercio,
        ID_Tecnico_Ensamblador, ID_Tecnico_Comprobador, ID_Tecnico_Mantenimiento
    ) VALUES (
        p_id, p_nombre, p_tipo, p_fecha_registro,
        p_estado, p_etapa, p_id_comercio,
        p_id_tecnico_ensamblador, p_id_tecnico_comprobador, p_id_tecnico_mant
    );
END;
$$ LANGUAGE plpgsql;

-- 4.2 Actualizar estado y etapa de maquina
CREATE OR REPLACE FUNCTION sp_actualizar_estado_maquina(
    p_id UUID,
    p_estado VARCHAR(50),
    p_etapa VARCHAR(50)
)
RETURNS VOID AS $$
BEGIN
    UPDATE MaquinaRecreativa
    SET Estado = p_estado,
        Etapa = p_etapa
    WHERE ID_Maquina = p_id;
END;
$$ LANGUAGE plpgsql;

-- 4.3 Actualizar datos de maquina
CREATE OR REPLACE FUNCTION sp_actualizar_maquina(
    p_id UUID,
    p_nombre VARCHAR(100),
    p_tipo VARCHAR(50),
    p_id_comercio UUID,
    p_estado VARCHAR(50),
    p_etapa VARCHAR(50)
)
RETURNS VOID AS $$
BEGIN
    UPDATE MaquinaRecreativa
    SET Nombre_Maquina = p_nombre,
        Tipo = p_tipo,
        ID_Comercio = p_id_comercio,
        Estado = COALESCE(p_estado, Estado),
        Etapa = COALESCE(p_etapa, Etapa)
    WHERE ID_Maquina = p_id;
END;
$$ LANGUAGE plpgsql;

-- 4.4 Asignar tecnico de mantenimiento
CREATE OR REPLACE FUNCTION sp_asignar_tecnico_mantenimiento(
    p_id_maquina UUID,
    p_id_tecnico UUID
)
RETURNS VOID AS $$
BEGIN
    UPDATE MaquinaRecreativa
    SET ID_Tecnico_Mantenimiento = p_id_tecnico,
        Estado = 'No operativa'
    WHERE ID_Maquina = p_id_maquina;
END;
$$ LANGUAGE plpgsql;

-- 4.5 Buscar maquina por ID
CREATE OR REPLACE FUNCTION sp_buscar_maquina_por_id(p_id UUID)
RETURNS TABLE(
    ID_Maquina UUID,
    Nombre_Maquina VARCHAR(100),
    Tipo VARCHAR(50),
    Fecha_Registro DATE,
    Estado VARCHAR(50),
    Etapa VARCHAR(50),
    ID_Comercio UUID,
    ID_Tecnico_Ensamblador UUID,
    ID_Tecnico_Comprobador UUID,
    ID_Tecnico_Mantenimiento UUID,
    NombreComercio VARCHAR(100),
    DireccionComercio TEXT
) AS $$
BEGIN
    RETURN QUERY
    SELECT m.ID_Maquina,
           m.Nombre_Maquina,
           m.Tipo,
           m.Fecha_Registro,
           m.Estado,
           m.Etapa,
           m.ID_Comercio,
           m.ID_Tecnico_Ensamblador,
           m.ID_Tecnico_Comprobador,
           m.ID_Tecnico_Mantenimiento,
           c.Nombre AS NombreComercio,
           c.Direccion AS DireccionComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.ID_Maquina = p_id;
END;
$$ LANGUAGE plpgsql;

-- 4.6 Maquinas por tecnico ensamblador
CREATE OR REPLACE FUNCTION sp_maquinas_por_tecnico_ensamblador(p_id_tecnico UUID)
RETURNS TABLE(
    ID_Maquina UUID,
    Nombre_Maquina VARCHAR(100),
    Tipo VARCHAR(50),
    Fecha_Registro DATE,
    Estado VARCHAR(50),
    Etapa VARCHAR(50),
    ID_Comercio UUID,
    ID_Tecnico_Ensamblador UUID,
    ID_Tecnico_Comprobador UUID,
    ID_Tecnico_Mantenimiento UUID,
    NombreComercio VARCHAR(100),
    DireccionComercio TEXT
) AS $$
BEGIN
    RETURN QUERY
    SELECT m.*, c.Nombre AS NombreComercio, c.Direccion AS DireccionComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.ID_Tecnico_Ensamblador = p_id_tecnico
    ORDER BY m.Fecha_Registro DESC;
END;
$$ LANGUAGE plpgsql;

-- 4.7 Maquinas por tecnico comprobador
CREATE OR REPLACE FUNCTION sp_maquinas_por_tecnico_comprobador(p_id_tecnico UUID)
RETURNS TABLE(
    ID_Maquina UUID,
    Nombre_Maquina VARCHAR(100),
    Tipo VARCHAR(50),
    Fecha_Registro DATE,
    Estado VARCHAR(50),
    Etapa VARCHAR(50),
    ID_Comercio UUID,
    ID_Tecnico_Ensamblador UUID,
    ID_Tecnico_Comprobador UUID,
    ID_Tecnico_Mantenimiento UUID,
    NombreComercio VARCHAR(100),
    DireccionComercio TEXT
) AS $$
BEGIN
    RETURN QUERY
    SELECT m.*, c.Nombre AS NombreComercio, c.Direccion AS DireccionComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.ID_Tecnico_Comprobador = p_id_tecnico
      AND m.Estado = 'Comprobandose'
    ORDER BY m.Fecha_Registro DESC;
END;
$$ LANGUAGE plpgsql;

-- 4.8 Maquinas por tecnico mantenimiento
CREATE OR REPLACE FUNCTION sp_maquinas_por_tecnico_mantenimiento(p_id_tecnico UUID)
RETURNS TABLE(
    ID_Maquina UUID,
    Nombre_Maquina VARCHAR(100),
    Tipo VARCHAR(50),
    Fecha_Registro DATE,
    Estado VARCHAR(50),
    Etapa VARCHAR(50),
    ID_Comercio UUID,
    ID_Tecnico_Ensamblador UUID,
    ID_Tecnico_Comprobador UUID,
    ID_Tecnico_Mantenimiento UUID,
    NombreComercio VARCHAR(100),
    DireccionComercio TEXT
) AS $$
BEGIN
    RETURN QUERY
    SELECT m.*, c.Nombre AS NombreComercio, c.Direccion AS DireccionComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.ID_Tecnico_Mantenimiento = p_id_tecnico
      AND m.Estado = 'No operativa'
    ORDER BY m.Fecha_Registro DESC;
END;
$$ LANGUAGE plpgsql;

-- 4.9 Maquinas por estado
CREATE OR REPLACE FUNCTION sp_maquinas_por_estado(p_estado VARCHAR)
RETURNS TABLE(
    ID_Maquina UUID,
    Nombre_Maquina VARCHAR,
    Tipo VARCHAR,
    Estado VARCHAR,
    Etapa VARCHAR,
    ID_Comercio UUID,
    ID_Tecnico_Ensamblador UUID,
    ID_Tecnico_Comprobador UUID,
    ID_Tecnico_Mantenimiento UUID,
    Fecha_Registro DATE,
    NombreComercio VARCHAR,
    DireccionComercio TEXT
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        m.ID_Maquina,
        m.Nombre_Maquina,
        m.Tipo,
        m.Estado,
        m.Etapa,
        m.ID_Comercio,
        m.ID_Tecnico_Ensamblador,
        m.ID_Tecnico_Comprobador,
        m.ID_Tecnico_Mantenimiento,
        m.Fecha_Registro,
        c.Nombre AS NombreComercio,
        c.Direccion AS DireccionComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.Estado = p_estado
    ORDER BY m.Fecha_Registro DESC;
END;
$$ LANGUAGE plpgsql;

-- 4.10 Maquinas por etapa
CREATE OR REPLACE FUNCTION sp_maquinas_por_etapa(p_etapa VARCHAR)
RETURNS TABLE(
    ID_Maquina UUID,
    Nombre_Maquina VARCHAR,
    Tipo VARCHAR,
    Estado VARCHAR,
    Etapa VARCHAR,
    ID_Comercio UUID,
    ID_Tecnico_Ensamblador UUID,
    ID_Tecnico_Comprobador UUID,
    ID_Tecnico_Mantenimiento UUID,
    Fecha_Registro DATE,
    NombreComercio VARCHAR,
    DireccionComercio TEXT
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        m.ID_Maquina,
        m.Nombre_Maquina,
        m.Tipo,
        m.Estado,
        m.Etapa,
        m.ID_Comercio,
        m.ID_Tecnico_Ensamblador,
        m.ID_Tecnico_Comprobador,
        m.ID_Tecnico_Mantenimiento,
        m.Fecha_Registro,
        c.Nombre AS NombreComercio,
        c.Direccion AS DireccionComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.Etapa = p_etapa
    ORDER BY m.Fecha_Registro DESC;
END;
$$ LANGUAGE plpgsql;

-- 4.11 Todas las maquinas
CREATE OR REPLACE FUNCTION sp_todas_las_maquinas()
RETURNS TABLE(
    ID_Maquina UUID,
    Nombre_Maquina VARCHAR,
    Tipo VARCHAR,
    Estado VARCHAR,
    Etapa VARCHAR,
    ID_Comercio UUID,
    ID_Tecnico_Ensamblador UUID,
    ID_Tecnico_Comprobador UUID,
    ID_Tecnico_Mantenimiento UUID,
    Fecha_Registro DATE,
    NombreComercio VARCHAR,
    DireccionComercio TEXT
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        m.ID_Maquina,
        m.Nombre_Maquina,
        m.Tipo,
        m.Estado,
        m.Etapa,
        m.ID_Comercio,
        m.ID_Tecnico_Ensamblador,
        m.ID_Tecnico_Comprobador,
        m.ID_Tecnico_Mantenimiento,
        m.Fecha_Registro,
        c.Nombre AS NombreComercio,
        c.Direccion AS DireccionComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    ORDER BY m.Fecha_Registro DESC;
END;
$$ LANGUAGE plpgsql;

-- 4.12 Maquinas operativas por comercio
CREATE OR REPLACE FUNCTION sp_maquinas_operativas_por_comercio(p_id_comercio UUID)
RETURNS TABLE(
    ID_Maquina UUID,
    Nombre_Maquina VARCHAR,
    Tipo VARCHAR,
    Estado VARCHAR,
    Etapa VARCHAR,
    ID_Comercio UUID,
    ID_Tecnico_Ensamblador UUID,
    ID_Tecnico_Comprobador UUID,
    ID_Tecnico_Mantenimiento UUID,
    Fecha_Registro DATE
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        m.ID_Maquina,
        m.Nombre_Maquina,
        m.Tipo,
        m.Estado,
        m.Etapa,
        m.ID_Comercio,
        m.ID_Tecnico_Ensamblador,
        m.ID_Tecnico_Comprobador,
        m.ID_Tecnico_Mantenimiento,
        m.Fecha_Registro
    FROM MaquinaRecreativa m
    WHERE m.ID_Comercio = p_id_comercio
      AND m.Estado = 'Operativa'
      AND m.Etapa = 'Recaudacion'
    ORDER BY m.Nombre_Maquina ASC;
END;
$$ LANGUAGE plpgsql;

-- 4.13 Maquinas en distribucion
CREATE OR REPLACE FUNCTION sp_maquinas_para_distribucion()
RETURNS TABLE(
    ID_Maquina UUID,
    Nombre_Maquina VARCHAR(100),
    Tipo VARCHAR(50),
    Fecha_Registro DATE,
    Estado VARCHAR(50),
    Etapa VARCHAR(50),
    ID_Comercio UUID,
    ID_Tecnico_Ensamblador UUID,
    ID_Tecnico_Comprobador UUID,
    ID_Tecnico_Mantenimiento UUID,
    NombreComercio VARCHAR(100),
    DireccionComercio TEXT
) AS $$
BEGIN
    RETURN QUERY
    SELECT m.*, c.Nombre AS NombreComercio, c.Direccion AS DireccionComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.Etapa = 'Distribucion'
      AND m.Estado = 'Distribuyendose'
    ORDER BY m.Fecha_Registro DESC;
END;
$$ LANGUAGE plpgsql;

-- 4.14 Componentes por maquina (via montaje)
CREATE OR REPLACE FUNCTION sp_componentes_por_maquina(p_id_maquina UUID)
RETURNS TABLE(
    ID_Componente UUID,
    tipo VARCHAR(20),
    nombre VARCHAR(50),
    precio DECIMAL(10,2),
    fecha_montaje TIMESTAMP,
    detalle TEXT
) AS $$
BEGIN
    RETURN QUERY
    SELECT c.*, mo.fecha AS fecha_montaje, mo.detalle
    FROM montaje mo
    JOIN componente c ON mo.ID_Componente = c.ID_Componente
    WHERE mo.ID_Maquina = p_id_maquina
    ORDER BY mo.fecha DESC;
END;
$$ LANGUAGE plpgsql;

-- 4.15 Componentes en uso por maquina
CREATE OR REPLACE FUNCTION sp_componentes_en_uso_por_maquina(p_id_maquina UUID)
RETURNS TABLE(
    ID_Componente UUID,
    tipo VARCHAR(20),
    nombre VARCHAR(50),
    precio DECIMAL(10,2),
    fecha_asignacion TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    SELECT c.ID_Componente, c.tipo, c.nombre, c.precio, cu.fecha_asignacion
    FROM componente_usuario cu
    INNER JOIN componente c ON cu.ID_Componente = c.ID_Componente
    WHERE cu.ID_Maquina = p_id_maquina
      AND cu.fecha_liberacion IS NULL
    ORDER BY cu.fecha_asignacion DESC;
END;
$$ LANGUAGE plpgsql;

-- 4.16 Verificar si usuario tiene maquinas asignadas
CREATE OR REPLACE FUNCTION sp_usuario_tiene_maquinas(p_id_usuario UUID)
RETURNS INTEGER AS $$
DECLARE
    v_tiene INTEGER;
BEGIN
    SELECT COUNT(*) INTO v_tiene
    FROM MaquinaRecreativa
    WHERE ID_Tecnico_Ensamblador = p_id_usuario
       OR ID_Tecnico_Comprobador = p_id_usuario
       OR ID_Tecnico_Mantenimiento = p_id_usuario;
    RETURN v_tiene;
END;
$$ LANGUAGE plpgsql;

-- ==============================================================
-- 5. MODULO COMPONENTE
-- ==============================================================

-- 5.1 Listar componentes
CREATE OR REPLACE FUNCTION sp_listar_componentes(
    p_tipo VARCHAR(20),
    p_limit INT,
    p_offset INT
)
RETURNS TABLE(
    ID_Componente UUID,
    tipo VARCHAR(20),
    nombre VARCHAR(50),
    precio DECIMAL(10,2),
    usuario_uso UUID,
    maquina_uso UUID,
    fecha_asignacion TIMESTAMP,
    fecha_liberacion TIMESTAMP
) AS $$
DECLARE
    sql_query TEXT;
BEGIN
    sql_query := 'SELECT c.*, cu.ID_Usuario AS usuario_uso, cu.ID_Maquina AS maquina_uso,
                  cu.fecha_asignacion, cu.fecha_liberacion
                  FROM componente c
                  LEFT JOIN componente_usuario cu ON c.ID_Componente = cu.ID_Componente
                      AND cu.fecha_liberacion IS NULL
                  WHERE 1=1';
    
    IF p_tipo IS NOT NULL AND p_tipo != '' THEN
        sql_query := sql_query || ' AND c.tipo = ''' || p_tipo || '''';
    END IF;
    
    sql_query := sql_query || ' GROUP BY c.ID_Componente, cu.ID_Usuario, cu.ID_Maquina, cu.fecha_asignacion, cu.fecha_liberacion';
    sql_query := sql_query || ' LIMIT ' || p_limit || ' OFFSET ' || p_offset;
    
    RETURN QUERY EXECUTE sql_query;
END;
$$ LANGUAGE plpgsql;

-- 5.2 Componentes disponibles
CREATE OR REPLACE FUNCTION sp_componentes_disponibles(p_tipo VARCHAR(20))
RETURNS TABLE(
    ID_Componente UUID,
    tipo VARCHAR(20),
    nombre VARCHAR(50),
    precio DECIMAL(10,2)
) AS $$
BEGIN
    IF p_tipo IS NULL OR p_tipo = '' THEN
        RETURN QUERY
        SELECT c.*
        FROM componente c
        LEFT JOIN componente_usuario cu ON c.ID_Componente = cu.ID_Componente
            AND cu.fecha_liberacion IS NULL
        WHERE cu.ID_Componente IS NULL
        ORDER BY c.nombre ASC;
    ELSE
        RETURN QUERY
        SELECT c.*
        FROM componente c
        LEFT JOIN componente_usuario cu ON c.ID_Componente = cu.ID_Componente
            AND cu.fecha_liberacion IS NULL
        WHERE cu.ID_Componente IS NULL
          AND c.tipo = p_tipo
        ORDER BY c.nombre ASC;
    END IF;
END;
$$ LANGUAGE plpgsql;

-- 5.3 Componentes en uso por usuario
CREATE OR REPLACE FUNCTION sp_componentes_en_uso_por_usuario(
    p_id_usuario UUID,
    p_id_maquina UUID
)
RETURNS TABLE(
    ID_Componente UUID,
    tipo VARCHAR(20),
    nombre VARCHAR(50),
    precio DECIMAL(10,2),
    fecha_asignacion TIMESTAMP,
    maquina_uso UUID
) AS $$
BEGIN
    IF p_id_maquina IS NULL THEN
        RETURN QUERY
        SELECT c.*, cu.fecha_asignacion, cu.ID_Maquina AS maquina_uso
        FROM componente_usuario cu
        INNER JOIN componente c ON cu.ID_Componente = c.ID_Componente
        WHERE cu.ID_Usuario = p_id_usuario
          AND cu.fecha_liberacion IS NULL;
    ELSE
        RETURN QUERY
        SELECT c.*, cu.fecha_asignacion, cu.ID_Maquina AS maquina_uso
        FROM componente_usuario cu
        INNER JOIN componente c ON cu.ID_Componente = c.ID_Componente
        WHERE cu.ID_Usuario = p_id_usuario
          AND cu.ID_Maquina = p_id_maquina
          AND cu.fecha_liberacion IS NULL;
    END IF;
END;
$$ LANGUAGE plpgsql;

-- 5.4 Asignar componente
CREATE OR REPLACE FUNCTION sp_asignar_componente(
    p_id_componente UUID,
    p_id_usuario UUID,
    p_id_maquina UUID,
    p_fecha TIMESTAMP
)
RETURNS VOID AS $$
DECLARE
    v_activo INTEGER;
BEGIN
    SELECT COUNT(*) INTO v_activo
    FROM componente_usuario
    WHERE ID_Componente = p_id_componente AND fecha_liberacion IS NULL;

    IF v_activo = 0 THEN
        INSERT INTO componente_usuario (ID_Registro, ID_Componente, ID_Usuario, ID_Maquina, fecha_asignacion)
        VALUES (uuid_generate_v4(), p_id_componente, p_id_usuario, p_id_maquina, p_fecha);
    END IF;
END;
$$ LANGUAGE plpgsql;

-- 5.5 Liberar componente
CREATE OR REPLACE FUNCTION sp_liberar_componente(
    p_id_componente UUID,
    p_fecha TIMESTAMP
)
RETURNS VOID AS $$
BEGIN
    UPDATE componente_usuario
    SET fecha_liberacion = p_fecha
    WHERE ID_Componente = p_id_componente
      AND fecha_liberacion IS NULL;
END;
$$ LANGUAGE plpgsql;

-- 5.6 Liberar todos los componentes de un usuario
CREATE OR REPLACE FUNCTION sp_liberar_componentes_usuario(
    p_id_usuario UUID,
    p_fecha TIMESTAMP
)
RETURNS VOID AS $$
BEGIN
    UPDATE componente_usuario
    SET fecha_liberacion = p_fecha
    WHERE ID_Usuario = p_id_usuario
      AND fecha_liberacion IS NULL;
END;
$$ LANGUAGE plpgsql;

-- 5.7 Generar numero de placa
CREATE OR REPLACE FUNCTION sp_generar_numero_placa()
RETURNS VARCHAR(20) AS $$
DECLARE
    v_anio VARCHAR(2);
    v_prefijo VARCHAR(6);
    v_max INT;
    p_placa VARCHAR(20);
BEGIN
    v_anio := TO_CHAR(CURRENT_TIMESTAMP, 'YY');
    v_prefijo := 'PL' || v_anio;

    SELECT COALESCE(MAX(CAST(SUBSTRING(nombre, 5) AS INTEGER)), 0)
    INTO v_max
    FROM componente
    WHERE nombre LIKE v_prefijo || '%' AND tipo = 'Logistico';

    p_placa := v_prefijo || LPAD((v_max + 1)::TEXT, 3, '0');
    RETURN p_placa;
END;
$$ LANGUAGE plpgsql;

-- 5.8 Contar componentes por tipo
CREATE OR REPLACE FUNCTION sp_contar_componentes_por_tipo(p_tipo VARCHAR(20))
RETURNS INTEGER AS $$
DECLARE
    v_total INTEGER;
BEGIN
    IF p_tipo IS NULL OR p_tipo = '' THEN
        SELECT COUNT(*) INTO v_total FROM componente;
    ELSE
        SELECT COUNT(*) INTO v_total FROM componente WHERE tipo = p_tipo;
    END IF;
    RETURN v_total;
END;
$$ LANGUAGE plpgsql;

-- ==============================================================
-- 6. MODULO MONTAJE
-- ==============================================================

-- 6.1 Insertar montaje
CREATE OR REPLACE FUNCTION sp_insertar_montaje(
    p_id_montaje UUID,
    p_id_maquina UUID,
    p_id_componente UUID,
    p_id_tecnico UUID,
    p_detalle TEXT,
    p_fecha TIMESTAMP
)
RETURNS VOID AS $$
BEGIN
    INSERT INTO montaje (ID_Montaje, ID_Maquina, ID_Componente, ID_Tecnico, detalle, fecha)
    VALUES (p_id_montaje, p_id_maquina, p_id_componente, p_id_tecnico, p_detalle, p_fecha);
END;
$$ LANGUAGE plpgsql;

-- 6.2 Montajes por maquina
CREATE OR REPLACE FUNCTION sp_montajes_por_maquina(p_id_maquina UUID)
RETURNS TABLE(
    ID_Montaje UUID,
    ID_Maquina UUID,
    ID_Componente UUID,
    ID_Tecnico UUID,
    detalle TEXT,
    fecha TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    SELECT * FROM montaje WHERE ID_Maquina = p_id_maquina ORDER BY fecha DESC;
END;
$$ LANGUAGE plpgsql;

-- 6.3 Montajes por componente
CREATE OR REPLACE FUNCTION sp_montajes_por_componente(p_id_componente UUID)
RETURNS TABLE(
    ID_Montaje UUID,
    ID_Maquina UUID,
    ID_Componente UUID,
    ID_Tecnico UUID,
    detalle TEXT,
    fecha TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    SELECT * FROM montaje WHERE ID_Componente = p_id_componente ORDER BY fecha DESC;
END;
$$ LANGUAGE plpgsql;

-- 6.4 Montajes por tecnico
CREATE OR REPLACE FUNCTION sp_montajes_por_tecnico(p_id_tecnico UUID)
RETURNS TABLE(
    ID_Montaje UUID,
    ID_Maquina UUID,
    ID_Componente UUID,
    ID_Tecnico UUID,
    detalle TEXT,
    fecha TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    SELECT * FROM montaje WHERE ID_Tecnico = p_id_tecnico ORDER BY fecha DESC;
END;
$$ LANGUAGE plpgsql;

-- ==============================================================
-- 7. MODULO NOTIFICACIONES
-- ==============================================================

-- 7.1 Crear notificacion de maquina
CREATE OR REPLACE FUNCTION sp_crear_notificacion_maquina(
    p_id UUID,
    p_remitente UUID,
    p_destinatario UUID,
    p_id_maquina UUID,
    p_tipo VARCHAR(100),
    p_mensaje TEXT,
    p_fecha TIMESTAMP
)
RETURNS VOID AS $$
BEGIN
    INSERT INTO NotificacionMaquinaRecreativa (
        ID_Notificacion, ID_Remitente, ID_Destinatario, ID_Maquina,
        Tipo, Mensaje, Fecha, Estado
    ) VALUES (
        p_id, p_remitente, p_destinatario, p_id_maquina,
        p_tipo, p_mensaje, p_fecha, 'No leido'
    );
END;
$$ LANGUAGE plpgsql;

-- 7.2 Notificaciones de maquina por destinatario
CREATE OR REPLACE FUNCTION sp_notificaciones_maquina_por_destinatario(p_id_destinatario UUID)
RETURNS TABLE(
    ID_Notificacion UUID,
    ID_Remitente UUID,
    ID_Destinatario UUID,
    ID_Maquina UUID,
    Tipo VARCHAR(100),
    Mensaje TEXT,
    Fecha TIMESTAMP,
    Estado VARCHAR(10),
    nombre_remitente VARCHAR(50),
    apellido_remitente VARCHAR(50),
    Nombre_Maquina VARCHAR(100),
    NombreComercio VARCHAR(100),
    DireccionComercio TEXT
) AS $$
BEGIN
    RETURN QUERY
    SELECT n.*,
           u.nombre AS nombre_remitente,
           u.apellido AS apellido_remitente,
           m.Nombre_Maquina,
           c.Nombre AS NombreComercio,
           c.Direccion AS DireccionComercio
    FROM NotificacionMaquinaRecreativa n
    LEFT JOIN usuario u ON n.ID_Remitente = u.ID_Usuario
    LEFT JOIN MaquinaRecreativa m ON n.ID_Maquina = m.ID_Maquina
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE n.ID_Destinatario = p_id_destinatario
    ORDER BY n.Fecha DESC;
END;
$$ LANGUAGE plpgsql;

-- 7.3 Marcar notificacion de maquina como leida
CREATE OR REPLACE FUNCTION sp_marcar_leida_maquina(p_id UUID)
RETURNS VOID AS $$
BEGIN
    UPDATE NotificacionMaquinaRecreativa
    SET Estado = 'Leido' WHERE ID_Notificacion = p_id;
END;
$$ LANGUAGE plpgsql;

-- 7.4 Contar no leidas de maquina
CREATE OR REPLACE FUNCTION sp_contar_no_leidas_maquina(p_id_destinatario UUID)
RETURNS INTEGER AS $$
DECLARE
    v_total INTEGER;
BEGIN
    SELECT COUNT(*) INTO v_total
    FROM NotificacionMaquinaRecreativa
    WHERE ID_Destinatario = p_id_destinatario AND Estado = 'No leido';
    RETURN v_total;
END;
$$ LANGUAGE plpgsql;

-- 7.5 Crear notificacion de reporte
CREATE OR REPLACE FUNCTION sp_crear_notificacion_reporte(
    p_id UUID,
    p_id_reporte UUID,
    p_id_usuario UUID,
    p_mensaje TEXT,
    p_fecha TIMESTAMP
)
RETURNS VOID AS $$
BEGIN
    INSERT INTO notificaciones (ID_Notificaciones, ID_Reporte, ID_Usuario, mensaje, fecha_hora, leida)
    VALUES (p_id, p_id_reporte, p_id_usuario, p_mensaje, p_fecha, FALSE);
END;
$$ LANGUAGE plpgsql;

-- 7.6 Notificaciones de reporte por usuario
CREATE OR REPLACE FUNCTION sp_notificaciones_reporte_por_usuario(p_id_usuario UUID)
RETURNS TABLE(
    ID_Notificaciones UUID,
    ID_Reporte UUID,
    ID_Usuario UUID,
    fecha_hora TIMESTAMP,
    mensaje TEXT,
    leida BOOLEAN,
    reporte_descripcion TEXT
) AS $$
BEGIN
    RETURN QUERY
    SELECT n.*, r.descripcion AS reporte_descripcion
    FROM notificaciones n
    LEFT JOIN reporte r ON n.ID_Reporte = r.ID_Reporte
    WHERE n.ID_Usuario = p_id_usuario
    ORDER BY n.fecha_hora DESC;
END;
$$ LANGUAGE plpgsql;

-- 7.7 Marcar notificacion de reporte como leida
CREATE OR REPLACE FUNCTION sp_marcar_leida_reporte(
    p_id_notificacion UUID,
    p_id_usuario UUID
)
RETURNS VOID AS $$
BEGIN
    UPDATE notificaciones
    SET leida = TRUE
    WHERE ID_Notificaciones = p_id_notificacion AND ID_Usuario = p_id_usuario;
END;
$$ LANGUAGE plpgsql;

-- 7.8 Marcar todas las notificaciones de reporte como leidas
CREATE OR REPLACE FUNCTION sp_marcar_todas_leidas_reporte(p_id_usuario UUID)
RETURNS VOID AS $$
BEGIN
    UPDATE notificaciones SET leida = TRUE WHERE ID_Usuario = p_id_usuario;
END;
$$ LANGUAGE plpgsql;

-- 7.9 Contar no leidas de reporte
CREATE OR REPLACE FUNCTION sp_contar_no_leidas_reporte(p_id_usuario UUID)
RETURNS INTEGER AS $$
DECLARE
    v_total INTEGER;
BEGIN
    SELECT COUNT(*) INTO v_total
    FROM notificaciones
    WHERE ID_Usuario = p_id_usuario AND leida = FALSE;
    RETURN v_total;
END;
$$ LANGUAGE plpgsql;

-- ==============================================================
-- 8. MODULO REPORTE / CHAT
-- ==============================================================

-- 8.1 Insertar reporte
CREATE OR REPLACE FUNCTION sp_insertar_reporte(
    p_id UUID,
    p_id_emisor UUID,
    p_id_destinatario UUID,
    p_descripcion TEXT,
    p_fecha_hora TIMESTAMP,
    p_estado VARCHAR(15)
)
RETURNS VOID AS $$
BEGIN
    INSERT INTO reporte (ID_Reporte, ID_Usuario_Emisor, ID_Usuario_Destinatario, descripcion, fecha_hora, estado)
    VALUES (p_id, p_id_emisor, p_id_destinatario, p_descripcion, p_fecha_hora, p_estado)
    ON CONFLICT (ID_Reporte) DO UPDATE
    SET estado = EXCLUDED.estado,
        descripcion = EXCLUDED.descripcion;
END;
$$ LANGUAGE plpgsql;

-- 8.2 Actualizar estado de reporte
CREATE OR REPLACE FUNCTION sp_actualizar_estado_reporte(
    p_id UUID,
    p_estado VARCHAR(15)
)
RETURNS VOID AS $$
BEGIN
    UPDATE reporte SET estado = p_estado WHERE ID_Reporte = p_id;
END;
$$ LANGUAGE plpgsql;

-- 8.3 Buscar reporte por ID
CREATE OR REPLACE FUNCTION sp_buscar_reporte_por_id(p_id UUID)
RETURNS TABLE(
    ID_Reporte UUID,
    ID_Usuario_Emisor UUID,
    ID_Usuario_Destinatario UUID,
    fecha_hora TIMESTAMP,
    descripcion TEXT,
    estado VARCHAR(15),
    emisor_nombre VARCHAR(50),
    emisor_apellido VARCHAR(50),
    emisor_email VARCHAR(100),
    destinatario_nombre VARCHAR(50),
    destinatario_apellido VARCHAR(50),
    destinatario_email VARCHAR(100)
) AS $$
BEGIN
    RETURN QUERY
    SELECT r.*,
           e.nombre AS emisor_nombre,
           e.apellido AS emisor_apellido,
           e.email AS emisor_email,
           d.nombre AS destinatario_nombre,
           d.apellido AS destinatario_apellido,
           d.email AS destinatario_email
    FROM reporte r
    JOIN usuario e ON r.ID_Usuario_Emisor = e.ID_Usuario
    LEFT JOIN usuario d ON r.ID_Usuario_Destinatario = d.ID_Usuario
    WHERE r.ID_Reporte = p_id
    LIMIT 1;
END;
$$ LANGUAGE plpgsql;

-- 8.4 Reportes por usuario
CREATE OR REPLACE FUNCTION sp_reportes_por_usuario(p_id_usuario UUID)
RETURNS TABLE(
    ID_Reporte UUID,
    ID_Usuario_Emisor UUID,
    ID_Usuario_Destinatario UUID,
    fecha_hora TIMESTAMP,
    descripcion TEXT,
    estado VARCHAR(15),
    emisor_nombre VARCHAR(50),
    emisor_apellido VARCHAR(50),
    emisor_email VARCHAR(100),
    destinatario_nombre VARCHAR(50),
    destinatario_apellido VARCHAR(50),
    destinatario_email VARCHAR(100)
) AS $$
BEGIN
    RETURN QUERY
    SELECT r.*,
           e.nombre AS emisor_nombre,
           e.apellido AS emisor_apellido,
           e.email AS emisor_email,
           d.nombre AS destinatario_nombre,
           d.apellido AS destinatario_apellido,
           d.email AS destinatario_email
    FROM reporte r
    JOIN usuario e ON r.ID_Usuario_Emisor = e.ID_Usuario
    LEFT JOIN usuario d ON r.ID_Usuario_Destinatario = d.ID_Usuario
    WHERE r.ID_Usuario_Emisor = p_id_usuario
       OR r.ID_Usuario_Destinatario = p_id_usuario
    ORDER BY r.fecha_hora DESC;
END;
$$ LANGUAGE plpgsql;

-- 8.5 Chat entre dos usuarios
CREATE OR REPLACE FUNCTION sp_chat_entre_usuarios(
    p_emisor UUID,
    p_destinatario UUID
)
RETURNS TABLE(
    ID_Reporte UUID,
    ID_Usuario_Emisor UUID,
    ID_Usuario_Destinatario UUID,
    fecha_hora TIMESTAMP,
    descripcion TEXT,
    estado VARCHAR(15),
    emisor_nombre VARCHAR(50),
    emisor_apellido VARCHAR(50),
    emisor_email VARCHAR(100),
    destinatario_nombre VARCHAR(50),
    destinatario_apellido VARCHAR(50),
    destinatario_email VARCHAR(100)
) AS $$
BEGIN
    RETURN QUERY
    SELECT r.*,
           e.nombre AS emisor_nombre,
           e.apellido AS emisor_apellido,
           e.email AS emisor_email,
           d.nombre AS destinatario_nombre,
           d.apellido AS destinatario_apellido,
           d.email AS destinatario_email
    FROM reporte r
    JOIN usuario e ON r.ID_Usuario_Emisor = e.ID_Usuario
    LEFT JOIN usuario d ON r.ID_Usuario_Destinatario = d.ID_Usuario
    WHERE (r.ID_Usuario_Emisor = p_emisor AND r.ID_Usuario_Destinatario = p_destinatario)
       OR (r.ID_Usuario_Emisor = p_destinatario AND r.ID_Usuario_Destinatario = p_emisor)
    ORDER BY r.fecha_hora ASC;
END;
$$ LANGUAGE plpgsql;

-- 8.6 Usuarios con quienes ha chateado
CREATE OR REPLACE FUNCTION sp_usuarios_chat(p_id_usuario UUID)
RETURNS TABLE(
    ID_Usuario UUID,
    ci VARCHAR(100),
    nombre VARCHAR(50),
    apellido VARCHAR(50),
    email VARCHAR(100),
    tipo VARCHAR(20),
    usuario_asignado VARCHAR(25),
    contrasena VARCHAR(255),
    estado VARCHAR(25),
    fecha_registro TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    SELECT DISTINCT u.* FROM usuario u
    WHERE u.ID_Usuario IN (
        SELECT DISTINCT ID_Usuario_Emisor FROM reporte WHERE ID_Usuario_Destinatario = p_id_usuario
        UNION
        SELECT DISTINCT ID_Usuario_Destinatario FROM reporte WHERE ID_Usuario_Emisor = p_id_usuario
    )
    AND u.ID_Usuario != p_id_usuario
    ORDER BY u.nombre ASC;
END;
$$ LANGUAGE plpgsql;

-- ==============================================================
-- 9. MODULO COMENTARIO
-- ==============================================================

-- 9.1 Insertar comentario
CREATE OR REPLACE FUNCTION sp_insertar_comentario(
    p_id UUID,
    p_id_reporte UUID,
    p_id_emisor UUID,
    p_comentario TEXT,
    p_fecha_hora TIMESTAMP
)
RETURNS VOID AS $$
BEGIN
    INSERT INTO comentario (ID_Comentario, ID_Reporte, ID_Usuario_Emisor, comentario, fecha_hora, fecha_edicion, eliminado)
    VALUES (p_id, p_id_reporte, p_id_emisor, p_comentario, p_fecha_hora, NULL, FALSE);
END;
$$ LANGUAGE plpgsql;

-- 9.2 Buscar comentario por ID
CREATE OR REPLACE FUNCTION sp_buscar_comentario_por_id(p_id UUID)
RETURNS TABLE(
    ID_Comentario UUID,
    ID_Reporte UUID,
    ID_Usuario_Emisor UUID,
    fecha_hora TIMESTAMP,
    comentario TEXT,
    fecha_edicion TIMESTAMP,
    eliminado BOOLEAN
) AS $$
BEGIN
    RETURN QUERY
    SELECT * FROM comentario WHERE ID_Comentario = p_id LIMIT 1;
END;
$$ LANGUAGE plpgsql;

-- 9.3 Editar comentario
CREATE OR REPLACE FUNCTION sp_editar_comentario(
    p_id UUID,
    p_comentario TEXT,
    p_fecha TIMESTAMP
)
RETURNS VOID AS $$
BEGIN
    UPDATE comentario
    SET comentario = p_comentario,
        fecha_edicion = p_fecha
    WHERE ID_Comentario = p_id;
END;
$$ LANGUAGE plpgsql;

-- 9.4 Eliminar comentario (soft delete)
CREATE OR REPLACE FUNCTION sp_eliminar_comentario(p_id UUID)
RETURNS VOID AS $$
BEGIN
    UPDATE comentario SET eliminado = TRUE WHERE ID_Comentario = p_id;
END;
$$ LANGUAGE plpgsql;

-- 9.5 Comentarios por reporte
CREATE OR REPLACE FUNCTION sp_comentarios_por_reporte(
    p_id_reporte UUID,
    p_id_usuario UUID
)
RETURNS TABLE(
    ID_Comentario UUID,
    ID_Reporte UUID,
    ID_Usuario_Emisor UUID,
    fecha_hora TIMESTAMP,
    comentario TEXT,
    fecha_edicion TIMESTAMP,
    eliminado BOOLEAN,
    nombre_emisor VARCHAR(50),
    apellido_emisor VARCHAR(50),
    email_emisor VARCHAR(100),
    tipo_emisor VARCHAR(20),
    es_propio INTEGER
) AS $$
BEGIN
    RETURN QUERY
    SELECT c.*,
           u.nombre AS nombre_emisor,
           u.apellido AS apellido_emisor,
           u.email AS email_emisor,
           u.tipo AS tipo_emisor,
           CASE WHEN u.ID_Usuario = p_id_usuario THEN 1 ELSE 0 END AS es_propio
    FROM comentario c
    JOIN usuario u ON c.ID_Usuario_Emisor = u.ID_Usuario
    WHERE c.ID_Reporte = p_id_reporte
      AND c.eliminado = FALSE
    ORDER BY c.fecha_hora ASC;
END;
$$ LANGUAGE plpgsql;

-- 9.6 Eliminar comentarios de un reporte
CREATE OR REPLACE FUNCTION sp_eliminar_comentarios_reporte(p_id_reporte UUID)
RETURNS VOID AS $$
BEGIN
    DELETE FROM comentario WHERE ID_Reporte = p_id_reporte;
END;
$$ LANGUAGE plpgsql;

-- ==============================================================
-- 10. MODULO RECAUDACION
-- ==============================================================

-- 10.1 Insertar recaudacion
CREATE OR REPLACE FUNCTION sp_insertar_recaudacion(
    p_id UUID,
    p_tipo_comercio VARCHAR(20),
    p_id_maquina UUID,
    p_id_usuario UUID,
    p_monto_total DECIMAL(10,2),
    p_monto_empresa DECIMAL(10,2),
    p_monto_comercio DECIMAL(10,2),
    p_porcentaje_comercio DECIMAL(5,2),
    p_fecha TIMESTAMP,
    p_detalle TEXT
)
RETURNS VOID AS $$
BEGIN
    INSERT INTO recaudaciones (
        ID_Recaudacion, Tipo_Comercio, ID_Maquina, ID_Usuario,
        Monto_Total, Monto_Empresa, Monto_Comercio, Porcentaje_Comercio,
        fecha, detalle
    ) VALUES (
        p_id, p_tipo_comercio, p_id_maquina, p_id_usuario,
        p_monto_total, p_monto_empresa, p_monto_comercio, p_porcentaje_comercio,
        p_fecha, p_detalle
    );
END;
$$ LANGUAGE plpgsql;

-- 10.2 Actualizar recaudacion
CREATE OR REPLACE FUNCTION sp_actualizar_recaudacion(
    p_id UUID,
    p_tipo_comercio VARCHAR(20),
    p_id_maquina UUID,
    p_monto_total DECIMAL(10,2),
    p_monto_empresa DECIMAL(10,2),
    p_monto_comercio DECIMAL(10,2),
    p_porcentaje_comercio DECIMAL(5,2),
    p_fecha TIMESTAMP,
    p_detalle TEXT
)
RETURNS VOID AS $$
BEGIN
    UPDATE recaudaciones
    SET Tipo_Comercio = p_tipo_comercio,
        ID_Maquina = p_id_maquina,
        Monto_Total = p_monto_total,
        Monto_Empresa = p_monto_empresa,
        Monto_Comercio = p_monto_comercio,
        Porcentaje_Comercio = p_porcentaje_comercio,
        fecha = p_fecha,
        detalle = p_detalle
    WHERE ID_Recaudacion = p_id;
END;
$$ LANGUAGE plpgsql;

-- 10.3 Eliminar recaudacion
CREATE OR REPLACE FUNCTION sp_eliminar_recaudacion(p_id UUID)
RETURNS VOID AS $$
BEGIN
    DELETE FROM informe_detalle
    USING informes_recaudacion
    WHERE informe_detalle.ID_Informe = informes_recaudacion.ID_Informe
      AND informes_recaudacion.ID_Recaudacion = p_id;

    DELETE FROM informes_recaudacion WHERE ID_Recaudacion = p_id;
    DELETE FROM recaudaciones WHERE ID_Recaudacion = p_id;
END;
$$ LANGUAGE plpgsql;

-- 10.4 Buscar recaudacion por ID
CREATE OR REPLACE FUNCTION sp_buscar_recaudacion_por_id(p_id UUID)
RETURNS TABLE(
    ID_Recaudacion UUID,
    ID_Maquina UUID,
    ID_Usuario UUID,
    Fecha_Recaudacion TIMESTAMP,
    Monto DECIMAL(10,2),
    Descripcion TEXT,
    Nombre_Maquina VARCHAR(100),
    Nombre_Comercio VARCHAR(100),
    Direccion_Comercio TEXT,
    Telefono_Comercio VARCHAR(20),
    nombre_usuario VARCHAR(50),
    apellido_usuario VARCHAR(50),
    nombre_ensamblador VARCHAR(50),
    apellido_ensamblador VARCHAR(50),
    nombre_comprobador VARCHAR(50),
    apellido_comprobador VARCHAR(50),
    nombre_mantenimiento VARCHAR(50),
    apellido_mantenimiento VARCHAR(50),
    ID_Tecnico_Ensamblador UUID,
    ID_Tecnico_Comprobador UUID,
    ID_Tecnico_Mantenimiento UUID
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        r.ID_Recaudacion,
        r.ID_Maquina,
        r.ID_Usuario,
        r.Fecha_Recaudacion,
        r.Monto,
        r.Descripcion,
        m.Nombre_Maquina,
        c.Nombre AS Nombre_Comercio,
        c.Direccion AS Direccion_Comercio,
        c.Telefono AS Telefono_Comercio,
        u.nombre AS nombre_usuario,
        u.apellido AS apellido_usuario,
        te.nombre AS nombre_ensamblador,
        te.apellido AS apellido_ensamblador,
        tc.nombre AS nombre_comprobador,
        tc.apellido AS apellido_comprobador,
        tm.nombre AS nombre_mantenimiento,
        tm.apellido AS apellido_mantenimiento,
        m.ID_Tecnico_Ensamblador,
        m.ID_Tecnico_Comprobador,
        m.ID_Tecnico_Mantenimiento
    FROM recaudaciones r
    INNER JOIN MaquinaRecreativa m ON r.ID_Maquina = m.ID_Maquina
    INNER JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    INNER JOIN usuario u ON r.ID_Usuario = u.ID_Usuario
    LEFT JOIN usuario te ON m.ID_Tecnico_Ensamblador = te.ID_Usuario
    LEFT JOIN usuario tc ON m.ID_Tecnico_Comprobador = tc.ID_Usuario
    LEFT JOIN usuario tm ON m.ID_Tecnico_Mantenimiento = tm.ID_Usuario
    WHERE r.ID_Recaudacion = p_id
    LIMIT 1;
END;
$$ LANGUAGE plpgsql;

-- 10.5 Listar recaudaciones
CREATE OR REPLACE FUNCTION sp_listar_recaudaciones(
    p_fecha_inicio DATE,
    p_fecha_fin DATE,
    p_id_maquina UUID,
    p_tipo_comercio VARCHAR(20),
    p_limit INT,
    p_offset INT
)
RETURNS TABLE(
    ID_Recaudacion UUID,
    Tipo_Comercio VARCHAR(20),
    ID_Maquina UUID,
    ID_Usuario UUID,
    Monto_Total DECIMAL(10,2),
    Monto_Empresa DECIMAL(10,2),
    Monto_Comercio DECIMAL(10,2),
    Porcentaje_Comercio DECIMAL(5,2),
    fecha TIMESTAMP,
    detalle TEXT,
    Nombre_Comercio VARCHAR(100),
    Nombre_Maquina VARCHAR(100),
    nombre_usuario VARCHAR(50),
    apellido_usuario VARCHAR(50)
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        r.ID_Recaudacion, r.Tipo_Comercio, r.ID_Maquina, r.ID_Usuario,
        r.Monto_Total, r.Monto_Empresa, r.Monto_Comercio, r.Porcentaje_Comercio,
        r.fecha, r.detalle,
        c.Nombre AS Nombre_Comercio,
        m.Nombre_Maquina,
        u.nombre AS nombre_usuario,
        u.apellido AS apellido_usuario
    FROM recaudaciones r
    INNER JOIN MaquinaRecreativa m ON r.ID_Maquina = m.ID_Maquina
    INNER JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    INNER JOIN usuario u ON r.ID_Usuario = u.ID_Usuario
    WHERE (p_fecha_inicio IS NULL OR DATE(r.fecha) >= p_fecha_inicio)
      AND (p_fecha_fin IS NULL OR DATE(r.fecha) <= p_fecha_fin)
      AND (p_id_maquina IS NULL OR r.ID_Maquina = p_id_maquina)
      AND (p_tipo_comercio IS NULL OR r.Tipo_Comercio = p_tipo_comercio)
    ORDER BY r.fecha DESC
    LIMIT p_limit OFFSET p_offset;
END;
$$ LANGUAGE plpgsql;

-- 10.6 Resumen de recaudaciones
CREATE OR REPLACE FUNCTION sp_resumen_recaudaciones(p_limit INT)
RETURNS TABLE(
    Tipo_Comercio VARCHAR(20),
    TotalRecaudaciones BIGINT,
    TotalRecaudado NUMERIC,
    TotalEmpresa NUMERIC,
    TotalComercio NUMERIC
) AS $$
BEGIN
    IF p_limit IS NULL THEN
        RETURN QUERY
        SELECT r.Tipo_Comercio,
               COUNT(*) AS TotalRecaudaciones,
               SUM(r.Monto_Total) AS TotalRecaudado,
               SUM(r.Monto_Empresa) AS TotalEmpresa,
               SUM(r.Monto_Comercio) AS TotalComercio
        FROM recaudaciones r
        GROUP BY r.Tipo_Comercio
        ORDER BY TotalRecaudado DESC;
    ELSE
        RETURN QUERY
        SELECT r.Tipo_Comercio,
               COUNT(*) AS TotalRecaudaciones,
               SUM(r.Monto_Total) AS TotalRecaudado,
               SUM(r.Monto_Empresa) AS TotalEmpresa,
               SUM(r.Monto_Comercio) AS TotalComercio
        FROM recaudaciones r
        GROUP BY r.Tipo_Comercio
        ORDER BY TotalRecaudado DESC
        LIMIT p_limit;
    END IF;
END;
$$ LANGUAGE plpgsql;

-- 10.7 Maquinas operativas para recaudacion
CREATE OR REPLACE FUNCTION sp_maquinas_operativas_recaudacion()
RETURNS TABLE(
    ID_Maquina UUID,
    Nombre_Maquina VARCHAR,
    Tipo VARCHAR,
    Estado VARCHAR,
    Etapa VARCHAR,
    ID_Comercio UUID,
    ID_Tecnico_Ensamblador UUID,
    ID_Tecnico_Comprobador UUID,
    ID_Tecnico_Mantenimiento UUID,
    Fecha_Registro DATE,
    NombreComercio VARCHAR,
    DireccionComercio TEXT,
    TelefonoComercio VARCHAR,
    TipoComercio VARCHAR
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        m.ID_Maquina,
        m.Nombre_Maquina,
        m.Tipo,
        m.Estado,
        m.Etapa,
        m.ID_Comercio,
        m.ID_Tecnico_Ensamblador,
        m.ID_Tecnico_Comprobador,
        m.ID_Tecnico_Mantenimiento,
        m.Fecha_Registro,
        c.Nombre AS NombreComercio,
        c.Direccion AS DireccionComercio,
        c.Telefono AS TelefonoComercio,
        c.Tipo AS TipoComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.Etapa = 'Recaudacion' 
      AND m.Estado = 'Operativa'
    ORDER BY m.Fecha_Registro DESC;
END;
$$ LANGUAGE plpgsql;

-- 10.8 Guardar informe de recaudacion
CREATE OR REPLACE FUNCTION sp_guardar_informe_recaudacion(
    p_id UUID,
    p_id_recaudacion UUID,
    p_ci_usuario VARCHAR(100),
    p_nombre_maquina VARCHAR(100),
    p_id_comercio UUID,
    p_nombre_comercio VARCHAR(100),
    p_direccion_comercio TEXT,
    p_telefono_comercio VARCHAR(15),
    p_pago_ensamblador DECIMAL(10,2),
    p_pago_comprobador DECIMAL(10,2),
    p_pago_mantenimiento DECIMAL(10,2),
    p_empresa_nombre VARCHAR(100),
    p_empresa_descripcion VARCHAR(255)
)
RETURNS VOID AS $$
BEGIN
    INSERT INTO informes_recaudacion (
        ID_Informe, ID_Recaudacion, CI_Usuario, Nombre_Maquina,
        ID_Comercio, Nombre_Comercio, Direccion_Comercio, Telefono_Comercio,
        Pago_Ensamblador, Pago_Comprobador, Pago_Mantenimiento,
        empresa_nombre, empresa_descripcion
    ) VALUES (
        p_id, p_id_recaudacion, p_ci_usuario, p_nombre_maquina,
        p_id_comercio, p_nombre_comercio, p_direccion_comercio, p_telefono_comercio,
        p_pago_ensamblador, p_pago_comprobador, p_pago_mantenimiento,
        p_empresa_nombre, p_empresa_descripcion
    )
    ON CONFLICT (ID_Informe) DO UPDATE
    SET CI_Usuario = EXCLUDED.CI_Usuario,
        Nombre_Maquina = EXCLUDED.Nombre_Maquina,
        Nombre_Comercio = EXCLUDED.Nombre_Comercio,
        Pago_Ensamblador = EXCLUDED.Pago_Ensamblador,
        Pago_Comprobador = EXCLUDED.Pago_Comprobador,
        Pago_Mantenimiento = EXCLUDED.Pago_Mantenimiento;
END;
$$ LANGUAGE plpgsql;

-- 10.9 Guardar detalle de informe
CREATE OR REPLACE FUNCTION sp_guardar_detalle_informe(
    p_id_detalle UUID,
    p_id_informe UUID,
    p_id_componente UUID
)
RETURNS VOID AS $$
BEGIN
    INSERT INTO informe_detalle (ID_Informe_Detalle, ID_Informe, ID_Componente)
    VALUES (p_id_detalle, p_id_informe, p_id_componente);
END;
$$ LANGUAGE plpgsql;

-- 10.10 Obtener informe por recaudacion
CREATE OR REPLACE FUNCTION sp_obtener_informe_por_recaudacion(p_id_recaudacion UUID)
RETURNS TABLE(
    ID_Informe UUID,
    ID_Recaudacion UUID,
    CI_Usuario VARCHAR(100),
    Nombre_Maquina VARCHAR(100),
    ID_Comercio UUID,
    Nombre_Comercio VARCHAR(100),
    Direccion_Comercio TEXT,
    Telefono_Comercio VARCHAR(15),
    Pago_Ensamblador DECIMAL(10,2),
    Pago_Comprobador DECIMAL(10,2),
    Pago_Mantenimiento DECIMAL(10,2),
    empresa_nombre VARCHAR(100),
    empresa_descripcion VARCHAR(255)
) AS $$
BEGIN
    RETURN QUERY
    SELECT * FROM informes_recaudacion WHERE ID_Recaudacion = p_id_recaudacion LIMIT 1;
END;
$$ LANGUAGE plpgsql;

-- 10.11 Detalles de informe por ID
CREATE OR REPLACE FUNCTION sp_detalles_informe(p_id_informe UUID)
RETURNS TABLE(
    ID_Componente UUID,
    tipo VARCHAR(20),
    nombre VARCHAR(50),
    precio DECIMAL(10,2)
) AS $$
BEGIN
    RETURN QUERY
    SELECT c.*
    FROM informe_detalle id
    JOIN componente c ON id.ID_Componente = c.ID_Componente
    WHERE id.ID_Informe = p_id_informe;
END;
$$ LANGUAGE plpgsql;

-- ==============================================================
-- 11. MODULO DISTRIBUCION
-- ==============================================================

-- 11.1 Guardar informe de distribucion
CREATE OR REPLACE FUNCTION sp_guardar_informe_distribucion(
    p_id UUID,
    p_id_maquina UUID,
    p_id_comprobador UUID,
    p_id_comercio UUID,
    p_fecha_alta TIMESTAMP,
    p_estado VARCHAR(20)
)
RETURNS VOID AS $$
BEGIN
    INSERT INTO informe_distribucion (
        ID_Distribucion, ID_Maquina, ID_Usuario_Comprobador, ID_Comercio, fecha_alta, estado
    ) VALUES (
        p_id, p_id_maquina, p_id_comprobador, p_id_comercio, p_fecha_alta, p_estado
    )
    ON CONFLICT (ID_Distribucion) DO UPDATE
    SET estado = EXCLUDED.estado,
        fecha_baja = CASE WHEN EXCLUDED.estado = 'Retirada' THEN CURRENT_TIMESTAMP ELSE NULL END;
END;
$$ LANGUAGE plpgsql;

-- 11.2 Actualizar estado de distribucion
CREATE OR REPLACE FUNCTION sp_actualizar_estado_distribucion(
    p_id_maquina UUID,
    p_estado VARCHAR(20)
)
RETURNS VOID AS $$
BEGIN
    UPDATE informe_distribucion
    SET estado = p_estado,
        fecha_baja = CASE WHEN p_estado = 'Retirada' THEN CURRENT_TIMESTAMP ELSE NULL END
    WHERE ID_Maquina = p_id_maquina;
END;
$$ LANGUAGE plpgsql;

-- 11.3 Buscar distribucion por maquina
CREATE OR REPLACE FUNCTION sp_buscar_distribucion_por_maquina(p_id_maquina UUID)
RETURNS TABLE(
    ID_Distribucion UUID,
    ID_Maquina UUID,
    ID_Usuario_Comprobador UUID,
    ID_Comercio UUID,
    fecha_alta TIMESTAMP,
    fecha_baja TIMESTAMP,
    estado VARCHAR(20)
) AS $$
BEGIN
    RETURN QUERY
    SELECT * FROM informe_distribucion WHERE ID_Maquina = p_id_maquina LIMIT 1;
END;
$$ LANGUAGE plpgsql;

-- 11.4 Listar informes de distribucion
CREATE OR REPLACE FUNCTION sp_listar_distribuciones(
    p_estado VARCHAR(20),
    p_id_comercio UUID,
    p_id_maquina UUID,
    p_fecha_inicio DATE,
    p_fecha_fin DATE,
    p_limit INT,
    p_offset INT
)
RETURNS TABLE(
    ID_Distribucion UUID,
    ID_Maquina UUID,
    ID_Usuario_Comprobador UUID,
    ID_Comercio UUID,
    fecha_alta TIMESTAMP,
    fecha_baja TIMESTAMP,
    estado VARCHAR(20),
    Nombre_Maquina VARCHAR(100),
    Nombre_Tecnico TEXT,
    Nombre_Comercio VARCHAR(100),
    Direccion_Comercio TEXT,
    Telefono_Comercio VARCHAR(15),
    Tipo_Comercio VARCHAR(20)
) AS $$
DECLARE
    sql_query TEXT;
BEGIN
    sql_query := 'SELECT id.*, m.Nombre_Maquina,
                  CONCAT(u.nombre, '' '', u.apellido) AS Nombre_Tecnico,
                  c.Nombre AS Nombre_Comercio,
                  c.Direccion AS Direccion_Comercio,
                  c.Telefono AS Telefono_Comercio,
                  c.Tipo AS Tipo_Comercio
                  FROM informe_distribucion id
                  INNER JOIN MaquinaRecreativa m ON id.ID_Maquina = m.ID_Maquina
                  INNER JOIN usuario u ON id.ID_Usuario_Comprobador = u.ID_Usuario
                  INNER JOIN Comercio c ON id.ID_Comercio = c.ID_Comercio
                  WHERE id.fecha_alta = (
                      SELECT MAX(id2.fecha_alta)
                      FROM informe_distribucion id2
                      WHERE id2.ID_Maquina = id.ID_Maquina
                  )';
    
    IF p_estado IS NOT NULL THEN
        sql_query := sql_query || ' AND id.estado = ''' || p_estado || '''';
    END IF;
    
    IF p_id_comercio IS NOT NULL THEN
        sql_query := sql_query || ' AND id.ID_Comercio = ''' || p_id_comercio || '''';
    END IF;
    
    IF p_id_maquina IS NOT NULL THEN
        sql_query := sql_query || ' AND id.ID_Maquina = ''' || p_id_maquina || '''';
    END IF;
    
    IF p_fecha_inicio IS NOT NULL THEN
        sql_query := sql_query || ' AND DATE(id.fecha_alta) >= ''' || p_fecha_inicio || '''';
    END IF;
    
    IF p_fecha_fin IS NOT NULL THEN
        sql_query := sql_query || ' AND DATE(id.fecha_alta) <= ''' || p_fecha_fin || '''';
    END IF;
    
    sql_query := sql_query || ' ORDER BY id.fecha_alta DESC';
    sql_query := sql_query || ' LIMIT ' || p_limit || ' OFFSET ' || p_offset;
    
    RETURN QUERY EXECUTE sql_query;
END;
$$ LANGUAGE plpgsql;

-- ==============================================================
-- 12. MODULO HISTORIAL DE MAQUINAS
-- ==============================================================

-- 12.1 Insertar historial
CREATE OR REPLACE FUNCTION sp_insertar_historial_maquina(
    p_id_maquina UUID,
    p_id_usuario UUID,
    p_tipo_usuario VARCHAR(20),
    p_accion VARCHAR(100),
    p_descripcion TEXT,
    p_estado_anterior VARCHAR(50),
    p_estado_nuevo VARCHAR(50),
    p_etapa_anterior VARCHAR(50),
    p_etapa_nueva VARCHAR(50),
    p_ip_address VARCHAR(45),
    p_detalles_json JSONB
)
RETURNS VOID AS $$
BEGIN
    INSERT INTO historial_maquinas (
        ID_Maquina, ID_Usuario, tipo_usuario, accion, descripcion,
        estado_anterior, estado_nuevo, etapa_anterior, etapa_nueva,
        ip_address, detalles_adicionales
    ) VALUES (
        p_id_maquina, p_id_usuario, p_tipo_usuario, p_accion, p_descripcion,
        p_estado_anterior, p_estado_nuevo, p_etapa_anterior, p_etapa_nueva,
        p_ip_address, p_detalles_json
    );
END;
$$ LANGUAGE plpgsql;

-- 12.2 Historial por maquina
CREATE OR REPLACE FUNCTION sp_historial_por_maquina(
    p_id_maquina UUID,
    p_limit INT,
    p_offset INT
)
RETURNS TABLE(
    ID_Historial UUID,
    ID_Maquina UUID,
    ID_Usuario UUID,
    tipo_usuario VARCHAR(20),
    accion VARCHAR(100),
    descripcion TEXT,
    estado_anterior VARCHAR(50),
    estado_nuevo VARCHAR(50),
    etapa_anterior VARCHAR(50),
    etapa_nueva VARCHAR(50),
    fecha_hora TIMESTAMP,
    ip_address VARCHAR(45),
    detalles_adicionales JSONB,
    usuario_nombre VARCHAR(50),
    usuario_apellido VARCHAR(50),
    usuario_tipo VARCHAR(20),
    Nombre_Maquina VARCHAR(100)
) AS $$
BEGIN
    RETURN QUERY
    SELECT h.*,
           u.nombre AS usuario_nombre,
           u.apellido AS usuario_apellido,
           u.tipo AS usuario_tipo,
           m.Nombre_Maquina
    FROM historial_maquinas h
    INNER JOIN usuario u ON h.ID_Usuario = u.ID_Usuario
    INNER JOIN MaquinaRecreativa m ON h.ID_Maquina = m.ID_Maquina
    WHERE h.ID_Maquina = p_id_maquina
    ORDER BY h.fecha_hora DESC
    LIMIT p_limit OFFSET p_offset;
END;
$$ LANGUAGE plpgsql;

-- 12.3 Historial por usuario
CREATE OR REPLACE FUNCTION sp_historial_por_usuario(
    p_id_usuario UUID,
    p_limit INT,
    p_offset INT
)
RETURNS TABLE(
    ID_Historial UUID,
    ID_Maquina UUID,
    ID_Usuario UUID,
    tipo_usuario VARCHAR(20),
    accion VARCHAR(100),
    descripcion TEXT,
    estado_anterior VARCHAR(50),
    estado_nuevo VARCHAR(50),
    etapa_anterior VARCHAR(50),
    etapa_nueva VARCHAR(50),
    fecha_hora TIMESTAMP,
    ip_address VARCHAR(45),
    detalles_adicionales JSONB,
    usuario_nombre VARCHAR(50),
    usuario_apellido VARCHAR(50),
    Nombre_Maquina VARCHAR(100)
) AS $$
BEGIN
    RETURN QUERY
    SELECT h.*,
           u.nombre AS usuario_nombre,
           u.apellido AS usuario_apellido,
           m.Nombre_Maquina
    FROM historial_maquinas h
    INNER JOIN usuario u ON h.ID_Usuario = u.ID_Usuario
    INNER JOIN MaquinaRecreativa m ON h.ID_Maquina = m.ID_Maquina
    WHERE h.ID_Usuario = p_id_usuario
    ORDER BY h.fecha_hora DESC
    LIMIT p_limit OFFSET p_offset;
END;
$$ LANGUAGE plpgsql;

-- 12.4 Historial general
CREATE OR REPLACE FUNCTION sp_historial_general(
    p_id_maquina UUID,
    p_id_usuario UUID,
    p_tipo_usuario VARCHAR(20),
    p_accion VARCHAR(100),
    p_fecha_inicio DATE,
    p_fecha_fin DATE,
    p_limit INT,
    p_offset INT
)
RETURNS TABLE(
    ID_Historial UUID,
    ID_Maquina UUID,
    ID_Usuario UUID,
    tipo_usuario VARCHAR(20),
    accion VARCHAR(100),
    descripcion TEXT,
    estado_anterior VARCHAR(50),
    estado_nuevo VARCHAR(50),
    etapa_anterior VARCHAR(50),
    etapa_nueva VARCHAR(50),
    fecha_hora TIMESTAMP,
    ip_address VARCHAR(45),
    detalles_adicionales JSONB,
    usuario_nombre VARCHAR(50),
    usuario_apellido VARCHAR(50),
    usuario_tipo VARCHAR(20),
    Nombre_Maquina VARCHAR(100),
    NombreComercio VARCHAR(100)
) AS $$
DECLARE
    sql_query TEXT;
BEGIN
    sql_query := 'SELECT h.*,
                  u.nombre AS usuario_nombre,
                  u.apellido AS usuario_apellido,
                  u.tipo AS usuario_tipo,
                  m.Nombre_Maquina,
                  c.Nombre AS NombreComercio
                  FROM historial_maquinas h
                  INNER JOIN usuario u ON h.ID_Usuario = u.ID_Usuario
                  INNER JOIN MaquinaRecreativa m ON h.ID_Maquina = m.ID_Maquina
                  LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
                  WHERE 1=1';
    
    IF p_id_maquina IS NOT NULL THEN
        sql_query := sql_query || ' AND h.ID_Maquina = ''' || p_id_maquina || '''';
    END IF;
    
    IF p_id_usuario IS NOT NULL THEN
        sql_query := sql_query || ' AND h.ID_Usuario = ''' || p_id_usuario || '''';
    END IF;
    
    IF p_tipo_usuario IS NOT NULL THEN
        sql_query := sql_query || ' AND h.tipo_usuario = ''' || p_tipo_usuario || '''';
    END IF;
    
    IF p_accion IS NOT NULL THEN
        sql_query := sql_query || ' AND h.accion LIKE ''%' || p_accion || '%''';
    END IF;
    
    IF p_fecha_inicio IS NOT NULL THEN
        sql_query := sql_query || ' AND DATE(h.fecha_hora) >= ''' || p_fecha_inicio || '''';
    END IF;
    
    IF p_fecha_fin IS NOT NULL THEN
        sql_query := sql_query || ' AND DATE(h.fecha_hora) <= ''' || p_fecha_fin || '''';
    END IF;
    
    sql_query := sql_query || ' ORDER BY h.fecha_hora DESC';
    sql_query := sql_query || ' LIMIT ' || p_limit || ' OFFSET ' || p_offset;
    
    RETURN QUERY EXECUTE sql_query;
END;
$$ LANGUAGE plpgsql;

-- 12.5 Contar historial
CREATE OR REPLACE FUNCTION sp_contar_historial(
    p_id_maquina UUID,
    p_id_usuario UUID,
    p_tipo_usuario VARCHAR(20),
    p_accion VARCHAR(100),
    p_fecha_inicio DATE,
    p_fecha_fin DATE
)
RETURNS INTEGER AS $$
DECLARE
    v_total INTEGER;
    sql_query TEXT;
BEGIN
    sql_query := 'SELECT COUNT(*) FROM historial_maquinas h WHERE 1=1';
    
    IF p_id_maquina IS NOT NULL THEN
        sql_query := sql_query || ' AND h.ID_Maquina = ''' || p_id_maquina || '''';
    END IF;
    
    IF p_id_usuario IS NOT NULL THEN
        sql_query := sql_query || ' AND h.ID_Usuario = ''' || p_id_usuario || '''';
    END IF;
    
    IF p_tipo_usuario IS NOT NULL THEN
        sql_query := sql_query || ' AND h.tipo_usuario = ''' || p_tipo_usuario || '''';
    END IF;
    
    IF p_accion IS NOT NULL THEN
        sql_query := sql_query || ' AND h.accion LIKE ''%' || p_accion || '%''';
    END IF;
    
    IF p_fecha_inicio IS NOT NULL THEN
        sql_query := sql_query || ' AND DATE(h.fecha_hora) >= ''' || p_fecha_inicio || '''';
    END IF;
    
    IF p_fecha_fin IS NOT NULL THEN
        sql_query := sql_query || ' AND DATE(h.fecha_hora) <= ''' || p_fecha_fin || '''';
    END IF;
    
    EXECUTE sql_query INTO v_total;
    RETURN v_total;
END;
$$ LANGUAGE plpgsql;

-- 12.6 Resumen reciente de historial
CREATE OR REPLACE FUNCTION sp_resumen_historial_reciente(p_limite INT)
RETURNS TABLE(
    ID_Historial UUID,
    ID_Maquina UUID,
    ID_Usuario UUID,
    tipo_usuario VARCHAR(20),
    accion VARCHAR(100),
    descripcion TEXT,
    estado_anterior VARCHAR(50),
    estado_nuevo VARCHAR(50),
    etapa_anterior VARCHAR(50),
    etapa_nueva VARCHAR(50),
    fecha_hora TIMESTAMP,
    ip_address VARCHAR(45),
    detalles_adicionales JSONB,
    usuario_nombre VARCHAR(50),
    usuario_apellido VARCHAR(50),
    usuario_tipo VARCHAR(20),
    Nombre_Maquina VARCHAR(100),
    NombreComercio VARCHAR(100)
) AS $$
BEGIN
    RETURN QUERY
    SELECT h.*,
           u.nombre AS usuario_nombre,
           u.apellido AS usuario_apellido,
           u.tipo AS usuario_tipo,
           m.Nombre_Maquina,
           c.Nombre AS NombreComercio
    FROM historial_maquinas h
    INNER JOIN usuario u ON h.ID_Usuario = u.ID_Usuario
    INNER JOIN MaquinaRecreativa m ON h.ID_Maquina = m.ID_Maquina
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    ORDER BY h.fecha_hora DESC
    LIMIT p_limite;
END;
$$ LANGUAGE plpgsql;

-- ==============================================================
-- TRIGGERS
-- ==============================================================

-- Trigger 1: Crear/Actualizar informe de distribucion
CREATE OR REPLACE FUNCTION after_maquina_distribucion_trigger()
RETURNS TRIGGER AS $$
DECLARE
    v_existe_informe INTEGER;
BEGIN
    IF NEW.Etapa = 'Distribucion' AND NEW.Estado = 'Distribuyendose' AND 
       (OLD.Etapa != 'Distribucion' OR OLD.Estado != 'Distribuyendose') THEN
        
        SELECT COUNT(*) INTO v_existe_informe 
        FROM informe_distribucion 
        WHERE ID_Maquina = NEW.ID_Maquina;
        
        IF v_existe_informe = 0 THEN
            INSERT INTO informe_distribucion (
                ID_Distribucion,
                ID_Maquina, 
                ID_Usuario_Comprobador, 
                ID_Comercio,
                estado,
                fecha_alta
            ) VALUES (
                uuid_generate_v4(),
                NEW.ID_Maquina,
                NEW.ID_Tecnico_Comprobador, 
                NEW.ID_Comercio,
                'Distribuyendose',
                CURRENT_TIMESTAMP
            );
        ELSE
            UPDATE informe_distribucion 
            SET estado = 'Distribuyendose',
                fecha_alta = CURRENT_TIMESTAMP,
                fecha_baja = NULL
            WHERE ID_Maquina = NEW.ID_Maquina;
        END IF;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS after_maquina_distribucion ON MaquinaRecreativa;
CREATE TRIGGER after_maquina_distribucion
AFTER UPDATE ON MaquinaRecreativa
FOR EACH ROW
EXECUTE FUNCTION after_maquina_distribucion_trigger();

-- Trigger 2: Actualizar estado del informe de distribucion
CREATE OR REPLACE FUNCTION after_maquina_estado_change_trigger()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.Estado != OLD.Estado THEN
        IF NEW.Estado IN ('Operativa', 'No operativa', 'Retirada') THEN
            UPDATE informe_distribucion 
            SET estado = NEW.Estado,
                fecha_baja = CASE WHEN NEW.Estado = 'Retirada' THEN CURRENT_TIMESTAMP ELSE NULL END
            WHERE ID_Maquina = NEW.ID_Maquina;
        END IF;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS after_maquina_estado_change ON MaquinaRecreativa;
CREATE TRIGGER after_maquina_estado_change
AFTER UPDATE ON MaquinaRecreativa
FOR EACH ROW
EXECUTE FUNCTION after_maquina_estado_change_trigger();

-- ==============================================================
-- VERIFICACION FINAL
-- ==============================================================

-- Listar todas las funciones creadas
SELECT proname, pronargs 
FROM pg_proc 
WHERE proname LIKE 'sp_%' 
ORDER BY proname;
