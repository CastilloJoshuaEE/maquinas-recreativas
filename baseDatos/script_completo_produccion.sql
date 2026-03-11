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
    descripcion TEXT DEFAULT 'Estuvo en su main',
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
    Estado ENUM('Ensamblandose', 'Comprobandose', 'Reensamblandose', 'Distribuyendose', 'Operativa', 'No operativa', 'Retirada') DEFAULT 'Ensamblándose' NOT NULL,
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
    tipo ENUM('Ensamblador', 'Comprobador', 'Mantenimiento', 'Logistico') NOT NULL,
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

INSERT INTO componente (tipo, nombre, precio) VALUES
('Ensamblador', 'Monitor LED 32" Pantalla Táctil', 250.00),
('Ensamblador', 'Placa Base Arcade Pro V2', 220.00),
('Ensamblador', 'Joystick Industrial con Botones RGB', 55.00),
('Ensamblador', 'Fuente de Alimentación 600W Certificada', 95.00),
('Ensamblador', 'Kit Cableado Premium con Conectores Dorados', 35.00),
('Ensamblador', 'Tarjeta Gráfica Arcade 4GB', 180.00),
('Ensamblador', 'Sistema de Refrigeración Liquida', 120.00),
('Ensamblador', 'Panel de Control con 8 Botones', 45.00),
('Ensamblador', 'Convertidor de Video HDMI a VGA', 30.00),
('Ensamblador', 'Kit de Montaje Completo para Arcade', 75.00),
('Ensamblador', 'Cable HDMI Premium 2m', 12.00),
('Ensamblador', 'Adaptador USB a DB9', 8.50),
('Ensamblador', 'Altavoces estéreo USB', 25.00),
('Ensamblador', 'RAM DDR4 8GB Kit', 45.00),
('Ensamblador', 'Disco SSD 240GB', 55.00),
('Ensamblador', 'Procesador Intel i3', 110.00),
('Ensamblador', 'Procesador AMD Ryzen 3', 115.00),
('Ensamblador', 'Módulo Wi-Fi integrado', 20.00),
('Ensamblador', 'Módulo Bluetooth 5.0', 15.00),
('Ensamblador', 'Receptor IR para mandos', 10.00),
('Ensamblador', 'Teclado numérico auxiliar', 18.00),
('Ensamblador', 'Panel LED de señalización', 22.00),
('Ensamblador', 'Monitor secundario 7\"', 60.00),
('Ensamblador', 'Amplificador de audio 20W', 35.00),
('Ensamblador', 'Tarjeta de sonido 5.1', 40.00),
('Ensamblador', 'Módulo de iluminación RGB', 28.00),
('Ensamblador', 'Sensor de proximidad IR', 12.00),
('Ensamblador', 'Módulo de cámara VGA', 30.00),
('Ensamblador', 'Batería de respaldo 5V', 14.00),
('Ensamblador', 'Kit tornillería acero M3', 16.00);

INSERT INTO componente (tipo, nombre, precio) VALUES
('Mantenimiento', 'Kit Reparación Premium 50 Piezas', 50.00),
('Mantenimiento', 'Lubricante Industrial Especial', 25.00),
('Mantenimiento', 'Set Limpieza Profesional para Arcade', 30.00),
('Mantenimiento', 'Pasta Térmica de Alto Rendimiento', 15.00),
('Mantenimiento', 'Kit de Reparación para Pantallas', 60.00),
('Mantenimiento', 'Repuestos para Joysticks (Pack 10)', 20.00),
('Mantenimiento', 'Botones de Reemplazo RGB (Pack 20)', 35.00),
('Mantenimiento', 'Ventiladores de Refrigeración 120mm', 18.00),
('Mantenimiento', 'Cintas Aislantes y Termorretráctiles', 12.00),
('Mantenimiento', 'Kit Emergencia para Fuentes', 40.00),
('Mantenimiento', 'Fusible rápido 5A', 2.50),
('Mantenimiento', 'Fusible rápido 3A', 2.00),
('Mantenimiento', 'Conector de repuesto HDMI', 5.00),
('Mantenimiento', 'Switch de encendido', 7.00),
('Mantenimiento', 'Panel de botones recambio', 12.00),
('Mantenimiento', 'Cable de alimentación IEC', 6.00),
('Mantenimiento', 'Taco antivibración de goma', 4.00),
('Mantenimiento', 'Correa para ventilador', 3.50),
('Mantenimiento', 'Soporte metálico para placa', 9.00),
('Mantenimiento', 'Sensor térmico NTC', 8.00),
('Mantenimiento', 'Disipador con ventilador', 14.00),
('Mantenimiento', 'Resistencia 10 Ω', 1.50),
('Mantenimiento', 'Condensador 1000 µF', 2.00),
('Mantenimiento', 'Diodo rectificador (5 pz)', 4.00),
('Mantenimiento', 'LED recambio (pack 20)', 6.00),
('Mantenimiento', 'Conector Molex 4 pines', 3.50),
('Mantenimiento', 'Adaptador DC Jack', 5.50),
('Mantenimiento', 'Cinta térmica Kapton', 7.50),
('Mantenimiento', 'Pata ajustable para carcasa', 6.00),
('Mantenimiento', 'Estuche porta-fusibles', 8.00);
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


DELIMITER //

-- Procedimiento para registrar usuario (modificado para UUID)
CREATE PROCEDURE sp_registrar_usuario(
    IN p_nombre VARCHAR(50),
    IN p_apellido VARCHAR(50),
    IN p_ci VARCHAR(100),
    IN p_email VARCHAR(100),
    IN p_usuario_asignado VARCHAR(25),
    IN p_contrasena VARCHAR(255),
    IN p_tipo ENUM('Administrador', 'Logistica', 'Tecnico', 'Contabilidad', 'Usuario'),
    IN p_especialidad VARCHAR(20),
    OUT p_id_usuario CHAR(36)
)
BEGIN
    DECLARE v_id CHAR(36);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
        SET p_id_usuario = NULL;
    END;
    
    START TRANSACTION;
    
    -- Verificar si el email ya existe
    IF EXISTS (SELECT 1 FROM usuario WHERE email = p_email) THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'El correo electrónico ya está registrado';
    END IF;
    
    -- Verificar si la cédula ya existe
    IF EXISTS (SELECT 1 FROM usuario WHERE ci = p_ci) THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'La cédula ya está registrada';
    END IF;
    
    -- Insertar usuario principal
    INSERT INTO usuario (
        nombre, apellido, ci, email, usuario_asignado, contrasena, tipo, estado
    ) VALUES (
        p_nombre, p_apellido, p_ci, p_email, p_usuario_asignado, p_contrasena, p_tipo, 'Pendiente de asignacion'
    );
    
    -- Obtener el UUID generado
    SELECT ID_Usuario INTO v_id FROM usuario WHERE email = p_email LIMIT 1;
    SET p_id_usuario = v_id;
    
    -- Insertar en tabla específica según tipo
    IF p_tipo = 'Tecnico' AND p_especialidad IS NOT NULL THEN
        INSERT INTO Tecnico (ID_Tecnico, Especialidad) 
        VALUES (v_id, p_especialidad);
    ELSEIF p_tipo = 'Logistica' THEN
        INSERT INTO Logistica (ID_Logistica) 
        VALUES (v_id);
    END IF;
    
    COMMIT;
END //
CREATE PROCEDURE sp_buscar_usuario_por_email(
    IN p_email VARCHAR(100)
)
BEGIN
    SELECT * 
    FROM usuario
    WHERE email = p_email;
END //
-- Procedimiento para login de usuario (modificado para UUID)
CREATE PROCEDURE sp_login(IN p_usuario_asignado VARCHAR(25))
BEGIN
    SELECT u.*, t.Especialidad 
    FROM usuario u 
    LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
    WHERE u.usuario_asignado = p_usuario_asignado;
END //

-- Procedimiento para registrar inicio de sesión (modificado para UUID)
CREATE PROCEDURE sp_registrar_inicio_sesion(
    IN p_id_usuario CHAR(36),
    IN p_usuario_asignado VARCHAR(100),
    IN p_contrasena VARCHAR(255)
)
BEGIN
    INSERT INTO inicio_sesion 
    (ID_Usuario, usuario_asignado, contrasena) 
    VALUES (p_id_usuario, p_usuario_asignado, p_contrasena);
END //

-- Procedimiento para registrar logout (modificado para UUID)
CREATE PROCEDURE sp_registrar_logout(IN p_id_usuario CHAR(36))
BEGIN
    UPDATE inicio_sesion 
    SET fecha_ultima_sesion = NOW() 
    WHERE ID_Usuario = p_id_usuario 
    ORDER BY fecha_inicio DESC 
    LIMIT 1;
END //

-- Procedimiento para obtener estado de usuario (modificado para UUID)
CREATE PROCEDURE sp_obtener_estado_usuario(
    IN p_id_usuario CHAR(36),
    OUT p_estado VARCHAR(30)
)
BEGIN
    SELECT estado INTO p_estado 
    FROM usuario 
    WHERE ID_Usuario = p_id_usuario;
END //

-- Procedimiento para incrementar actividades de técnico (modificado para UUID)
CREATE PROCEDURE sp_incrementar_actividades_tecnico(IN p_id_tecnico CHAR(36))
BEGIN
    UPDATE Tecnico 
    SET Cantidad_Actividades = Cantidad_Actividades + 1 
    WHERE ID_Tecnico = p_id_tecnico;
END //

-- Procedimiento para obtener usuario por ID (modificado para UUID)
CREATE PROCEDURE sp_obtener_usuario_por_id(IN p_id CHAR(36))
BEGIN
    SELECT u.*, t.Especialidad 
    FROM usuario u 
    LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
    WHERE u.ID_Usuario = p_id;
END //

-- Procedimiento para actualizar perfil de usuario (modificado para UUID)
CREATE PROCEDURE sp_actualizar_perfil(
    IN p_id CHAR(36),
    IN p_nombre VARCHAR(50),
    IN p_apellido VARCHAR(50),
    IN p_email VARCHAR(100),
    IN p_ci VARCHAR(100),
    IN p_tipo VARCHAR(20),
    IN p_estado VARCHAR(30),
    IN p_especialidad VARCHAR(20),
    OUT p_resultado BOOLEAN
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_resultado = FALSE;
    END;
    
    START TRANSACTION;
    
    -- Actualizar usuario
    UPDATE usuario SET 
        nombre = p_nombre, 
        apellido = p_apellido, 
        email = p_email, 
        ci = p_ci,
        tipo = p_tipo,
        estado = p_estado
    WHERE ID_Usuario = p_id;
    
    -- Si es técnico, manejar especialidad
    IF p_tipo = 'Tecnico' THEN
        -- Eliminar primero si existe para evitar duplicados
        DELETE FROM Tecnico WHERE ID_Tecnico = p_id;
        
        -- Insertar solo si hay especialidad
        IF p_especialidad IS NOT NULL THEN
            INSERT INTO Tecnico (ID_Tecnico, Especialidad) 
            VALUES (p_id, p_especialidad);
        END IF;
    ELSE
        -- Si cambia de técnico a otro tipo, eliminar de la tabla Tecnico
        DELETE FROM Tecnico WHERE ID_Tecnico = p_id;
    END IF;
    
    COMMIT;
    SET p_resultado = TRUE;
END //

-- Procedimiento para actualizar usuario asignado (modificado para UUID)
CREATE PROCEDURE sp_actualizar_usuario_asignado(
    IN p_id CHAR(36),
    IN p_usuario_asignado VARCHAR(25),
    OUT p_resultado BOOLEAN
)
BEGIN
    DECLARE v_email VARCHAR(100);
    
    -- Verificar si se proporcionó ID o email
    IF p_id IS NOT NULL THEN
        UPDATE usuario 
        SET usuario_asignado = p_usuario_asignado 
        WHERE ID_Usuario = p_id;
        
        SET p_resultado = (ROW_COUNT() > 0);
    ELSE
        SET p_resultado = FALSE;
    END IF;
END //

-- Procedimiento para recuperar contraseña (modificado para UUID)
CREATE PROCEDURE sp_recuperar_contrasena(
    IN p_email VARCHAR(100),
    IN p_nueva_contrasena VARCHAR(255),
    OUT p_resultado BOOLEAN
)
BEGIN
    UPDATE usuario 
    SET contrasena = p_nueva_contrasena 
    WHERE email = p_email;
    
    SET p_resultado = (ROW_COUNT() > 0);
END //

-- Procedimiento para obtener técnicos por especialidad (modificado para UUID)
CREATE PROCEDURE sp_obtener_tecnicos_por_especialidad(
    IN p_especialidad VARCHAR(20),
    IN p_solo_activos BOOLEAN
)
BEGIN
    SELECT u.ID_Usuario, u.nombre, u.apellido, t.Cantidad_Actividades, u.estado 
    FROM usuario u 
    JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
    WHERE t.Especialidad = p_especialidad
    AND (p_solo_activos = FALSE OR u.estado = 'Activo')
    ORDER BY t.Cantidad_Actividades ASC;
END //

-- Procedimiento para obtener usuarios por tipo (modificado para UUID)
CREATE PROCEDURE sp_obtener_usuarios_por_tipo(
    IN p_tipo VARCHAR(20),
    IN p_excluir_id CHAR(36)
)
BEGIN
    IF p_excluir_id IS NULL THEN
        SELECT ID_Usuario, nombre, apellido, tipo, email 
        FROM usuario 
        WHERE tipo = p_tipo;
    ELSE
        SELECT ID_Usuario, nombre, apellido, tipo, email 
        FROM usuario 
        WHERE tipo = p_tipo AND ID_Usuario != p_excluir_id;
    END IF;
END //

-- Procedimiento para registrar actividad (modificado para UUID)
CREATE PROCEDURE sp_registrar_actividad(
    IN p_id_usuario CHAR(36),
    IN p_descripcion TEXT,
    OUT p_resultado BOOLEAN
)
BEGIN
    INSERT INTO historial_actividades (ID_Usuario, descripcion) 
    VALUES (p_id_usuario, p_descripcion);
    
    SET p_resultado = (ROW_COUNT() > 0);
END //

-- Procedimiento para obtener historial de actividades (modificado para UUID)
CREATE PROCEDURE sp_obtener_historial_actividades(IN p_usuario_id CHAR(36))
BEGIN
    SELECT ID_Historial_Actividades, descripcion, fecha_registro
    FROM historial_actividades
    WHERE ID_Usuario = p_usuario_id
    ORDER BY fecha_registro DESC;
END //

DELIMITER ;

DELIMITER //

-- Obtener todos los usuarios
CREATE PROCEDURE sp_obtener_todos_usuarios()
BEGIN
    SELECT u.*, t.Especialidad 
    FROM usuario u 
    LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
    ORDER BY u.tipo, u.nombre;
END //

-- Actualizar usuario
CREATE PROCEDURE sp_actualizar_usuario(
    IN p_id CHAR(36),
    IN p_nombre VARCHAR(50),
    IN p_apellido VARCHAR(50),
    IN p_email VARCHAR(100),
    IN p_tipo ENUM('Administrador', 'Logistica', 'Tecnico', 'Contabilidad'),
    IN p_estado ENUM('Pendiente de asignacion', 'Activo', 'Inhabilitado'),
    IN p_usuario_asignado VARCHAR(25),
    IN p_especialidad VARCHAR(50)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    UPDATE usuario SET 
        nombre = p_nombre,
        apellido = p_apellido,
        email = p_email,
        tipo = p_tipo,
        estado = p_estado,
        usuario_asignado = p_usuario_asignado
    WHERE ID_Usuario = p_id;
    
    IF p_tipo = 'Tecnico' AND p_especialidad IS NOT NULL THEN
        INSERT INTO Tecnico (ID_Tecnico, Especialidad) 
        VALUES (p_id, p_especialidad)
        ON DUPLICATE KEY UPDATE Especialidad = p_especialidad;
    END IF;
    
    COMMIT;
END //

-- Eliminar usuario
CREATE PROCEDURE sp_eliminar_usuario(IN p_id CHAR(36))
BEGIN
    DECLARE v_has_dependencies INT;
    DECLARE v_tipo VARCHAR(20);
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Verificar dependencias
    SELECT COUNT(*) INTO v_has_dependencies FROM MaquinaRecreativa 
    WHERE ID_Tecnico_Ensamblador = p_id OR ID_Tecnico_Comprobador = p_id OR ID_Tecnico_Mantenimiento = p_id;
    
    IF v_has_dependencies > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se puede eliminar el usuario porque tiene máquinas asignadas';
    END IF;
    
    -- Eliminar registros relacionados
    DELETE FROM historial_actividades WHERE ID_Usuario = p_id;
    DELETE FROM NotificacionMaquinaRecreativa WHERE ID_Remitente = p_id OR ID_Destinatario = p_id;
    DELETE FROM inicio_sesion WHERE ID_Usuario = p_id;
    
    -- Obtener tipo de usuario
    SELECT tipo INTO v_tipo FROM usuario WHERE ID_Usuario = p_id;
    
    -- Eliminar de tablas específicas
    IF v_tipo = 'Tecnico' THEN
        DELETE FROM Tecnico WHERE ID_Tecnico = p_id;
    ELSEIF v_tipo = 'Logistica' THEN
        DELETE FROM Logistica WHERE ID_Logistica = p_id;
    END IF;
    
    -- Finalmente eliminar usuario
    DELETE FROM usuario WHERE ID_Usuario = p_id;
    
    COMMIT;
END //

-- Registrar usuario admin
CREATE PROCEDURE sp_registrar_usuario_admin(
    IN p_nombre VARCHAR(50),
    IN p_apellido VARCHAR(50),
    IN p_ci VARCHAR(100),
    IN p_email VARCHAR(100),
    IN p_usuario_asignado VARCHAR(25),
    IN p_tipo ENUM('Administrador', 'Logistica', 'Tecnico', 'Contabilidad'),
    IN p_estado ENUM('Pendiente de asignacion', 'Activo', 'Inhabilitado'),
    IN p_contrasena VARCHAR(255),
    IN p_especialidad VARCHAR(50),
    OUT p_id_usuario CHAR(36)
)
BEGIN
    DECLARE new_uuid CHAR(36);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    SET new_uuid = UUID();
    
    INSERT INTO usuario (ID_Usuario, nombre, apellido, ci, email, usuario_asignado, contrasena, tipo, estado) 
    VALUES (new_uuid, p_nombre, p_apellido, p_ci, p_email, p_usuario_asignado, p_contrasena, p_tipo, p_estado);
    
    SET p_id_usuario = new_uuid;
    
    IF p_tipo = 'Tecnico' AND p_especialidad IS NOT NULL THEN
        INSERT INTO Tecnico (ID_Tecnico, Especialidad) VALUES (new_uuid, p_especialidad);
    ELSEIF p_tipo = 'Logistica' THEN
        INSERT INTO Logistica (ID_Logistica) VALUES (new_uuid);
    END IF;
    
    COMMIT;
END //

CREATE PROCEDURE sp_cambiar_estado_usuario(
    IN p_id_usuario CHAR(36),
    IN p_estado VARCHAR(30)
)
BEGIN
    UPDATE usuario 
    SET estado = p_estado 
    WHERE ID_Usuario = p_id_usuario;
END //

-- Obtener usuarios con filtros
CREATE PROCEDURE sp_obtener_usuarios_filtrados(
    IN p_ci VARCHAR(100),
    IN p_estado VARCHAR(30),
    IN p_tipo VARCHAR(20),
    IN p_rango VARCHAR(10)
)
BEGIN
    SET @sql = "SELECT 
                  u.*,
                  (SELECT fecha_ultima_sesion 
                   FROM inicio_sesion 
                   WHERE ID_Usuario = u.ID_Usuario 
                   ORDER BY fecha_ultima_sesion DESC 
                   LIMIT 1) AS fecha_ultima_sesion
                FROM usuario u
                WHERE 1=1";
    
    IF p_ci IS NOT NULL THEN
        SET @sql = CONCAT(@sql, " AND u.ci = '", p_ci, "'");
    END IF;
    
    IF p_estado IS NOT NULL THEN
        SET @sql = CONCAT(@sql, " AND u.estado = '", p_estado, "'");
    END IF;
    
    IF p_tipo IS NOT NULL THEN
        SET @sql = CONCAT(@sql, " AND u.tipo = '", p_tipo, "'");
    END IF;
    
    IF p_rango IS NOT NULL THEN
        CASE p_rango
            WHEN 'hoy' THEN
                SET @sql = CONCAT(@sql, " AND DATE((SELECT fecha_ultima_sesion FROM inicio_sesion WHERE ID_Usuario = u.ID_Usuario ORDER BY fecha_ultima_sesion DESC LIMIT 1)) = CURDATE()");
            WHEN 'ayer' THEN
                SET @sql = CONCAT(@sql, " AND DATE((SELECT fecha_ultima_sesion FROM inicio_sesion WHERE ID_Usuario = u.ID_Usuario ORDER BY fecha_ultima_sesion DESC LIMIT 1)) = CURDATE() - INTERVAL 1 DAY");
            WHEN '15dias' THEN
                SET @sql = CONCAT(@sql, " AND DATE((SELECT fecha_ultima_sesion FROM inicio_sesion WHERE ID_Usuario = u.ID_Usuario ORDER BY fecha_ultima_sesion DESC LIMIT 1)) >= CURDATE() - INTERVAL 15 DAY");
            WHEN '30dias' THEN
                SET @sql = CONCAT(@sql, " AND DATE((SELECT fecha_ultima_sesion FROM inicio_sesion WHERE ID_Usuario = u.ID_Usuario ORDER BY fecha_ultima_sesion DESC LIMIT 1)) >= CURDATE() - INTERVAL 30 DAY");
        END CASE;
    END IF;
    
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END //

DELIMITER //

-- Registrar comercio
CREATE PROCEDURE sp_registrar_comercio(
    IN p_nombre VARCHAR(100),
    IN p_tipo ENUM('Minorista', 'Mayorista'),
    IN p_direccion TEXT,
    IN p_telefono VARCHAR(15),
    OUT p_id_comercio CHAR(36)
)
BEGIN
    DECLARE new_uuid CHAR(36);
    SET new_uuid = UUID();
    
    INSERT INTO Comercio (ID_Comercio, Nombre, Tipo, Direccion, Telefono, Fecha_Registro) 
    VALUES (new_uuid, p_nombre, p_tipo, p_direccion, p_telefono, CURDATE());
    
    SET p_id_comercio = new_uuid;
END //

-- Obtener todos los comercios
CREATE PROCEDURE sp_obtener_comercios()
BEGIN
    SELECT * FROM Comercio ORDER BY Nombre;
END //

-- Obtener comercio por ID
CREATE PROCEDURE sp_obtener_comercio_por_id(IN p_id CHAR(36))
BEGIN
    SELECT * FROM Comercio WHERE ID_Comercio = p_id;
END //

-- Incrementar máquinas en comercio
CREATE PROCEDURE sp_incrementar_maquinas_comercio(IN p_id_comercio CHAR(36))
BEGIN
    UPDATE Comercio 
    SET Cantidad_Maquinas = Cantidad_Maquinas + 1 
    WHERE ID_Comercio = p_id_comercio;
END //

DELIMITER //

CREATE TRIGGER after_montaje_insert
AFTER INSERT ON montaje
FOR EACH ROW
BEGIN
    DECLARE v_placa_asignada BOOLEAN DEFAULT FALSE;
    DECLARE v_id_placa CHAR(36);

    -- Verificar si ya existe una placa asignada a esta máquina
    SELECT EXISTS(
        SELECT 1 FROM montaje m
        JOIN componente c ON m.ID_Componente = c.ID_Componente
        WHERE m.ID_Maquina = NEW.ID_Maquina
        AND c.nombre LIKE 'PL%'
    ) INTO v_placa_asignada;

    -- Obtener ID de la placa si existe
    IF v_placa_asignada THEN
        SELECT m.ID_Componente INTO v_id_placa
        FROM montaje m
        JOIN componente c ON m.ID_Componente = c.ID_Componente
        WHERE m.ID_Maquina = NEW.ID_Maquina
        AND c.nombre LIKE 'PL%'
        LIMIT 1;

        -- Actualizar registro de la placa si no está aún asociada a una máquina
        UPDATE componente_usuario
        SET ID_Maquina = NEW.ID_Maquina
        WHERE ID_Componente = v_id_placa
        AND ID_Maquina IS NULL
        AND fecha_liberacion IS NULL;

        -- Registrar en historial
        INSERT INTO historial_actividades (ID_Usuario, descripcion)
        VALUES (
            NEW.ID_Tecnico,
            CONCAT('Placa ID ', v_id_placa, ' asignada a máquina ID ', NEW.ID_Maquina)
        );
    END IF;
END //

CREATE PROCEDURE sp_Generar_Placa_Maquina_Recreativa(
    IN p_id_tecnico CHAR(36),
    OUT p_numero_placa VARCHAR(20),
    OUT p_id_componente CHAR(36)
)
BEGIN
    DECLARE v_prefijo VARCHAR(3);
    DECLARE v_secuencia INT;
    DECLARE new_uuid CHAR(36);
    
    -- Obtener prefijo basado en el año actual
    SET v_prefijo = CONCAT('PL', YEAR(CURDATE()) % 100);
    
    -- Obtener siguiente número de secuencia
    SELECT IFNULL(MAX(CAST(SUBSTRING(nombre, 5) AS UNSIGNED)), 0) + 1 INTO v_secuencia
    FROM componente
    WHERE nombre LIKE CONCAT(v_prefijo, '%') AND tipo = 'Logistico';
    
    -- Formar número de placa (ej: PL23001)
    SET p_numero_placa = CONCAT(v_prefijo, LPAD(v_secuencia, 3, '0'));
    
    -- Generar UUID para el nuevo componente
    SET new_uuid = UUID();
    
    -- Insertar el componente placa
    INSERT INTO componente (ID_Componente, tipo, nombre, precio)
    VALUES (new_uuid, 'Logistico', p_numero_placa, 120.00);
    
    SET p_id_componente = new_uuid;
    
    -- Registrar uso del componente por el técnico
    INSERT INTO componente_usuario (ID_Componente, ID_Usuario, fecha_asignacion)
    VALUES (p_id_componente, p_id_tecnico, NOW());
END //

CREATE PROCEDURE sp_obtener_componentes_disponibles(IN p_tipo VARCHAR(20))
BEGIN
    SELECT 
        c.ID_Componente,
        c.tipo,
        c.nombre,
        c.precio,
        (SELECT COUNT(*) 
         FROM componente_usuario cu 
         WHERE cu.ID_Componente = c.ID_Componente 
         AND cu.fecha_liberacion IS NULL) AS en_uso
    FROM componente c
    WHERE p_tipo IS NULL OR c.tipo = p_tipo;
END //

CREATE PROCEDURE sp_Asignar_Carcasa_Maquina(
    IN p_id_tecnico CHAR(36),
    IN p_id_carcasa CHAR(36),
    OUT p_resultado VARCHAR(100),
    OUT p_exito BOOLEAN
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_exito = FALSE;
        SET p_resultado = 'Error en la transacción';
        ROLLBACK;
    END;
    
    START TRANSACTION;
    
    -- Verificar que la carcasa existe y es de tipo Logístico
    IF NOT EXISTS (
        SELECT 1 FROM componente 
        WHERE ID_Componente = p_id_carcasa 
        AND tipo = 'Logistico' 
        AND nombre LIKE 'Carcasa%'
    ) THEN
        SET p_exito = FALSE;
        SET p_resultado = 'La carcasa seleccionada no existe o no es válida';
        ROLLBACK;
    ELSE
        -- Registrar en componente_usuario para seguimiento (sin ID_Maquina aún)
        INSERT INTO componente_usuario (ID_Componente, ID_Usuario, fecha_asignacion)
        VALUES (p_id_carcasa, p_id_tecnico, NOW());
        
        SET p_exito = TRUE;
        SET p_resultado = 'Carcasa asignada correctamente';
        COMMIT;
    END IF;
END //

CREATE PROCEDURE sp_obtener_componentes(
    IN p_tipo VARCHAR(20),
    IN p_limit INT,
    IN p_offset INT
)
BEGIN
    IF p_tipo IS NULL THEN
        SELECT * FROM componente 
        LIMIT p_limit OFFSET p_offset;
    ELSE
        SELECT * FROM componente 
        WHERE tipo = p_tipo
        LIMIT p_limit OFFSET p_offset;
    END IF;
    
    -- También devolvemos el conteo total para la paginación
    IF p_tipo IS NULL THEN
        SELECT COUNT(*) AS total FROM componente;
    ELSE
        SELECT COUNT(*) AS total FROM componente WHERE tipo = p_tipo;
    END IF;
END //

CREATE PROCEDURE sp_usar_componente(
    IN p_id_componente CHAR(36),
    IN p_id_usuario CHAR(36),
    IN p_id_maquina CHAR(36),
    OUT p_resultado VARCHAR(100),
    OUT p_exito BOOLEAN
)
BEGIN
    DECLARE v_es_tecnico BOOLEAN DEFAULT FALSE;
    DECLARE v_tipo_tecnico VARCHAR(20);
    DECLARE v_tipo_componente VARCHAR(20);
    DECLARE v_componente_nombre VARCHAR(50);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_exito = FALSE;
        SET p_resultado = 'Error en la transacción';
    END;

    -- Verificar que el usuario es un técnico
    SELECT COUNT(*) > 0 INTO v_es_tecnico
    FROM Tecnico 
    WHERE ID_Tecnico = p_id_usuario;

    IF NOT v_es_tecnico THEN
        SET p_exito = FALSE;
        SET p_resultado = 'El usuario no es un técnico válido';
    ELSE
        -- Obtener tipo de técnico y tipo/nombre del componente
        SELECT Especialidad INTO v_tipo_tecnico
        FROM Tecnico 
        WHERE ID_Tecnico = p_id_usuario;

        SELECT tipo, nombre INTO v_tipo_componente, v_componente_nombre
        FROM componente
        WHERE ID_Componente = p_id_componente;

        -- Verificar que el componente existe
        IF v_tipo_componente IS NULL THEN
            SET p_exito = FALSE;
            SET p_resultado = 'El componente no existe';
        ELSE
            -- Verificar compatibilidad de tipos
            IF v_tipo_componente != 'Logistico' AND v_tipo_componente != v_tipo_tecnico THEN
                SET p_exito = FALSE;
                SET p_resultado = CONCAT('Componente de tipo ', v_tipo_componente, 
                                       ' no compatible con técnico ', v_tipo_tecnico);
            ELSE
                START TRANSACTION;

                -- Registrar uso del componente
                INSERT INTO componente_usuario (ID_Componente, ID_Usuario, ID_Maquina, fecha_asignacion)
                VALUES (p_id_componente, p_id_usuario, p_id_maquina, NOW());

                -- Registrar en montaje si hay máquina
                IF p_id_maquina IS NOT NULL THEN
                    INSERT INTO montaje (fecha, ID_Maquina, ID_Componente, ID_Tecnico, detalle)
                    VALUES (NOW(), p_id_maquina, p_id_componente, p_id_usuario, 
                           CONCAT('Componente ', v_componente_nombre, ' asignado'));
                END IF;

                SET p_exito = TRUE;
                SET p_resultado = 'Componente asignado correctamente';
                COMMIT;
            END IF;
        END IF;
    END IF;
END //

CREATE PROCEDURE sp_liberar_componente(
    IN p_id_componente CHAR(36),
    IN p_id_usuario CHAR(36),
    OUT p_resultado VARCHAR(100),
    OUT p_exito BOOLEAN
)
BEGIN
    DECLARE v_afectadas INT;
    DECLARE v_id_maquina CHAR(36);
    DECLARE v_id_registro CHAR(36);
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE;
        SET p_exito = FALSE;
        SET p_resultado = CONCAT('Error en la transacción: ', @sqlstate);
    END;
    
    START TRANSACTION;
    
    -- 1. Obtener el ID del registro y la máquina asociada
    SELECT ID_Registro, ID_Maquina INTO v_id_registro, v_id_maquina
    FROM componente_usuario
    WHERE ID_Componente = p_id_componente 
      AND ID_Usuario = p_id_usuario 
      AND fecha_liberacion IS NULL
    ORDER BY fecha_asignacion DESC 
    LIMIT 1;
    
    -- 2. Marcar como liberado usando ID_Registro para evitar ambigüedades
    IF v_id_registro IS NOT NULL THEN
        UPDATE componente_usuario 
        SET 
            fecha_liberacion = NOW(),
            ID_Maquina = NULL
        WHERE ID_Registro = v_id_registro;
        
        SET v_afectadas = ROW_COUNT();
        
        -- 3. Si tenía máquina asociada, eliminar de montaje
        IF v_id_maquina IS NOT NULL THEN
            DELETE FROM montaje 
            WHERE ID_Componente = p_id_componente
              AND ID_Maquina = v_id_maquina
              AND ID_Tecnico = p_id_usuario;
        END IF;
        
        SET p_resultado = CONCAT('Componente liberado correctamente', 
                                IF(v_id_maquina IS NULL, '', ' de la máquina'));
        SET p_exito = TRUE;
        COMMIT;
    ELSE
        SET p_exito = FALSE;
        SET p_resultado = 'No se encontró el componente en uso';
        ROLLBACK;
    END IF;
END //

CREATE PROCEDURE sp_obtener_componentes_en_uso(
    IN p_id_usuario CHAR(36),
    IN p_id_maquina CHAR(36)
)
BEGIN
    -- Primero verificar si el usuario existe
    IF NOT EXISTS (SELECT 1 FROM usuario WHERE ID_Usuario = p_id_usuario) THEN
        SELECT 
            NULL AS ID_Componente,
            NULL AS tipo,
            'Usuario no existe' AS nombre,
            NULL AS precio,
            NULL AS ID_Maquina,
            NULL AS Nombre_Maquina,
            NULL AS fecha_asignacion,
            'Error' AS estado_uso
        WHERE FALSE; -- Para que no devuelva filas
    ELSE
        -- Si el usuario existe, devolver componentes
        SELECT 
            c.ID_Componente,
            c.tipo,
            c.nombre,
            c.precio,
            cu.ID_Maquina,
            COALESCE(m.Nombre_Maquina, 'N/A') AS Nombre_Maquina,
            cu.fecha_asignacion,
            CASE 
                WHEN cu.ID_Maquina IS NOT NULL THEN 'Asignado permanentemente'
                WHEN cu.fecha_liberacion IS NULL THEN 'En uso temporal'
                ELSE 'Liberado'
            END AS estado_uso
        FROM componente c
        JOIN componente_usuario cu ON c.ID_Componente = cu.ID_Componente
        LEFT JOIN MaquinaRecreativa m ON cu.ID_Maquina = m.ID_Maquina
        WHERE cu.ID_Usuario = p_id_usuario 
        AND cu.fecha_liberacion IS NULL
        AND (p_id_maquina IS NULL OR cu.ID_Maquina = p_id_maquina);
    END IF;
END //

CREATE PROCEDURE crear_componente_maquina(
  IN p_id_componente CHAR(36),
  IN p_id_usuario CHAR(36),
  IN p_id_maquina CHAR(36),
  OUT p_resultado VARCHAR(100)
)
BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    SET p_resultado = 'Error en la transacción';
  END;
  
  START TRANSACTION;
  
  -- Registrar uso del componente
  INSERT INTO componente_usuario (ID_Componente, ID_Usuario, fecha_asignacion)
  VALUES (p_id_componente, p_id_usuario, NOW());
  
  -- Registrar en tabla montaje si hay máquina
  IF p_id_maquina IS NOT NULL THEN
    INSERT INTO montaje (fecha, ID_Maquina, ID_Componente, ID_Tecnico, detalle)
    VALUES (NOW(), p_id_maquina, p_id_componente, p_id_usuario, 'Componente asignado');
  END IF;
  
  COMMIT;
  SET p_resultado = 'Operación exitosa';
END //

CREATE PROCEDURE sp_liberar_componentes_cancelacion(
    IN p_id_placa CHAR(36),
    IN p_id_carcasa CHAR(36),
    IN p_id_usuario CHAR(36),
    OUT p_resultado VARCHAR(100),
    OUT p_exito BOOLEAN
)
BEGIN
    DECLARE v_afectadas_placa INT;
    DECLARE v_afectadas_carcasa INT;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_exito = FALSE;
        SET p_resultado = 'Error en la transacción';
    END;
    
    START TRANSACTION;
    
    -- Liberar placa
    UPDATE componente_usuario 
    SET fecha_liberacion = NOW() 
    WHERE ID_Componente = p_id_placa 
    AND ID_Usuario = p_id_usuario 
    AND fecha_liberacion IS NULL;
    
    SET v_afectadas_placa = ROW_COUNT();
    
    -- Liberar carcasa
    UPDATE componente_usuario 
    SET fecha_liberacion = NOW() 
    WHERE ID_Componente = p_id_carcasa 
    AND ID_Usuario = p_id_usuario 
    AND fecha_liberacion IS NULL;
    
    SET v_afectadas_carcasa = ROW_COUNT();
    
    IF v_afectadas_placa = 0 OR v_afectadas_carcasa = 0 THEN
        SET p_exito = FALSE;
        SET p_resultado = 'No se encontraron todos los componentes en uso';
        ROLLBACK;
    ELSE
        SET p_exito = TRUE;
        SET p_resultado = 'Componentes liberados correctamente';
        COMMIT;
    END IF;
END //
CREATE PROCEDURE sp_obtener_componentes_por_maquina(IN p_id_maquina CHAR(36))
BEGIN
    SELECT 
        c.ID_Componente, 
        c.nombre, 
        c.tipo, 
        c.precio
    FROM montaje m
    JOIN componente c ON m.ID_Componente = c.ID_Componente
    WHERE m.ID_Maquina = p_id_maquina;
END //

DELIMITER ;

DELIMITER //

-- Procedimiento para crear reporte
CREATE PROCEDURE sp_crear_reporte(
    IN p_emisor_id CHAR(36),
    IN p_destinatario_id CHAR(36),
    IN p_descripcion TEXT,
    OUT p_id_reporte CHAR(36)
)
BEGIN
    DECLARE new_uuid CHAR(36);
    SET new_uuid = UUID();
    
    INSERT INTO reporte (
        ID_Reporte, ID_Usuario_Emisor, ID_Usuario_Destinatario, descripcion, estado, fecha_hora
    ) VALUES (
        new_uuid, p_emisor_id, p_destinatario_id, p_descripcion, 'Pendiente', NOW()
    );
    
    SET p_id_reporte = new_uuid;
END //

-- Procedimiento para obtener reportes por usuario
CREATE PROCEDURE sp_obtener_reportes_por_usuario(IN p_user_id CHAR(36))
BEGIN
    SELECT r.*, 
           ue.nombre AS emisor_nombre, ue.apellido AS emisor_apellido, ue.tipo AS emisor_tipo,
           ud.nombre AS destinatario_nombre, ud.apellido AS destinatario_apellido, ud.tipo AS destinatario_tipo
    FROM reporte r
    JOIN usuario ue ON r.ID_Usuario_Emisor = ue.ID_Usuario
    LEFT JOIN usuario ud ON r.ID_Usuario_Destinatario = ud.ID_Usuario
    WHERE r.ID_Usuario_Emisor = p_user_id OR r.ID_Usuario_Destinatario = p_user_id
    ORDER BY r.fecha_hora DESC;
END //

-- Procedimiento para obtener reporte por ID
CREATE PROCEDURE sp_obtener_reporte_por_id(IN p_reporte_id CHAR(36))
BEGIN
    SELECT r.*, 
           ue.nombre AS emisor_nombre, ue.apellido AS emisor_apellido, ue.tipo AS emisor_tipo,
           ud.nombre AS destinatario_nombre, ud.apellido AS destinatario_apellido, ud.tipo AS destinatario_tipo
    FROM reporte r
    JOIN usuario ue ON r.ID_Usuario_Emisor = ue.ID_Usuario
    LEFT JOIN usuario ud ON r.ID_Usuario_Destinatario = ud.ID_Usuario
    WHERE r.ID_Reporte = p_reporte_id;
END //

-- Procedimiento para actualizar estado de reporte
CREATE PROCEDURE sp_actualizar_estado_reporte(
    IN p_reporte_id CHAR(36),
    IN p_estado VARCHAR(15)
)
BEGIN
    UPDATE reporte 
    SET estado = p_estado 
    WHERE ID_Reporte = p_reporte_id;
END //

-- Procedimiento para obtener chat entre dos usuarios
CREATE PROCEDURE sp_obtener_chat(
    IN p_emisor_id CHAR(36),
    IN p_destinatario_id CHAR(36)
)
BEGIN
    SELECT r.ID_Reporte, r.descripcion, r.estado, r.fecha_hora,
           ue.nombre AS emisor_nombre, ue.apellido AS emisor_apellido, ue.tipo AS emisor_tipo,
           ud.nombre AS destinatario_nombre, ud.apellido AS destinatario_apellido, ud.tipo AS destinatario_tipo
    FROM reporte r
    JOIN usuario ue ON r.ID_Usuario_Emisor = ue.ID_Usuario
    JOIN usuario ud ON r.ID_Usuario_Destinatario = ud.ID_Usuario
    WHERE (r.ID_Usuario_Emisor = p_emisor_id AND r.ID_Usuario_Destinatario = p_destinatario_id)
    OR (r.ID_Usuario_Emisor = p_destinatario_id AND r.ID_Usuario_Destinatario = p_emisor_id)
    ORDER BY r.fecha_hora DESC;
END //

-- Procedimiento para obtener usuarios con los que se ha chateado
CREATE PROCEDURE sp_obtener_usuarios_chat(IN p_user_id CHAR(36))
BEGIN
    SELECT DISTINCT 
        CASE 
            WHEN r.ID_Usuario_Emisor = p_user_id THEN ud.ID_Usuario
            ELSE ue.ID_Usuario
        END AS ID_Usuario,
        CASE 
            WHEN r.ID_Usuario_Emisor = p_user_id THEN ud.nombre
            ELSE ue.nombre
        END AS nombre,
        CASE 
            WHEN r.ID_Usuario_Emisor = p_user_id THEN ud.apellido
            ELSE ue.apellido
        END AS apellido,
        CASE 
            WHEN r.ID_Usuario_Emisor = p_user_id THEN ud.email
            ELSE ue.email
        END AS email,
        CASE 
            WHEN r.ID_Usuario_Emisor = p_user_id THEN ud.tipo
            ELSE ue.tipo
        END AS tipo
    FROM reporte r
    JOIN usuario ue ON r.ID_Usuario_Emisor = ue.ID_Usuario
    JOIN usuario ud ON r.ID_Usuario_Destinatario = ud.ID_Usuario
    WHERE r.ID_Usuario_Emisor = p_user_id OR r.ID_Usuario_Destinatario = p_user_id
    ORDER BY nombre, apellido;
END //

DELIMITER //

-- Procedimiento para crear notificación de máquina con UUID
CREATE PROCEDURE sp_crear_notificacion_maquina(
    IN p_id_remitente CHAR(36),
    IN p_id_destinatario CHAR(36),
    IN p_id_maquina CHAR(36),
    IN p_tipo VARCHAR(50),
    IN p_mensaje TEXT
)
BEGIN
    INSERT INTO NotificacionMaquinaRecreativa (
        ID_Notificacion, ID_Remitente, ID_Destinatario, ID_Maquina, Tipo, Mensaje
    ) VALUES (
        UUID(), p_id_remitente, p_id_destinatario, p_id_maquina, p_tipo, p_mensaje
    );
END //

-- Procedimiento para obtener notificaciones por destinatario con UUID
CREATE PROCEDURE sp_obtener_notificaciones_por_destinatario(IN p_id_destinatario CHAR(36))
BEGIN
    SELECT n.*, u.nombre AS NombreRemitente, m.Nombre_Maquina, 
           c.Nombre AS NombreComercio, c.Direccion AS DireccionComercio
    FROM NotificacionMaquinaRecreativa n 
    JOIN usuario u ON n.ID_Remitente = u.ID_Usuario 
    JOIN MaquinaRecreativa m ON n.ID_Maquina = m.ID_Maquina
    JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE n.ID_Destinatario = p_id_destinatario 
    ORDER BY n.Fecha DESC;
END //

-- Procedimiento para marcar notificación como leída con UUID
CREATE PROCEDURE sp_marcar_como_leida(IN p_id_notificacion CHAR(36))
BEGIN
    UPDATE NotificacionMaquinaRecreativa 
    SET Estado = 'Leido' 
    WHERE ID_Notificacion = p_id_notificacion;
END //

-- Procedimiento para obtener cantidad de notificaciones no leídas con UUID
CREATE PROCEDURE sp_obtener_no_leidas(IN p_id_usuario CHAR(36), OUT p_total INT)
BEGIN
    SELECT COUNT(*) INTO p_total 
    FROM NotificacionMaquinaRecreativa 
    WHERE ID_Destinatario = p_id_usuario AND Estado = 'No leido';
END //

-- Procedimiento para crear notificación de reporte con UUID
CREATE PROCEDURE sp_crear_notificacion_reporte(
    IN p_id_reporte CHAR(36),
    IN p_id_usuario CHAR(36),
    IN p_mensaje TEXT
)
BEGIN
    INSERT INTO notificaciones (
        ID_Notificaciones, ID_Reporte, ID_Usuario, mensaje, fecha_hora, leida
    ) VALUES (
        UUID(), p_id_reporte, p_id_usuario, p_mensaje, NOW(), 0
    );
END //

-- Procedimiento para obtener notificaciones por usuario con UUID
CREATE PROCEDURE sp_obtener_notificaciones_por_usuario(IN p_usuario_id CHAR(36))
BEGIN
    SELECT n.*, r.descripcion AS reporte_descripcion,
           ue.nombre AS emisor_nombre, ue.apellido AS emisor_apellido
    FROM notificaciones n
    LEFT JOIN reporte r ON n.ID_Reporte = r.ID_Reporte
    LEFT JOIN usuario ue ON r.ID_Usuario_Emisor = ue.ID_Usuario
    WHERE n.ID_Usuario = p_usuario_id
    ORDER BY n.fecha_hora DESC;
END //

-- Procedimiento para marcar notificación como leída con UUID
CREATE PROCEDURE sp_marcar_como_leida_notificacion(
    IN p_notificacion_id CHAR(36),
    IN p_usuario_id CHAR(36)
)
BEGIN
    UPDATE notificaciones 
    SET leida = 1 
    WHERE ID_Notificaciones = p_notificacion_id AND ID_Usuario = p_usuario_id;
END //

-- Procedimiento para marcar todas las notificaciones como leídas con UUID
CREATE PROCEDURE sp_marcar_todas_como_leidas(IN p_usuario_id CHAR(36))
BEGIN
    UPDATE notificaciones 
    SET leida = 1 
    WHERE ID_Usuario = p_usuario_id;
END //

-- Procedimiento para obtener cantidad de notificaciones no leídas con UUID
CREATE PROCEDURE sp_obtener_cantidad_no_leidas(IN p_usuario_id CHAR(36), OUT p_cantidad INT)
BEGIN
    SELECT COUNT(*) INTO p_cantidad
    FROM notificaciones
    WHERE ID_Usuario = p_usuario_id AND leida = 0;
END //

DELIMITER ;

DELIMITER //

CREATE TRIGGER after_maquina_distribucion
AFTER UPDATE ON MaquinaRecreativa
FOR EACH ROW
BEGIN
    IF NEW.Etapa = 'Distribucion' AND NEW.Estado = 'Distribuyendose' AND 
       (OLD.Etapa != 'Distribucion' OR OLD.Estado != 'Distribuyendose') THEN
        
        -- Verificar si ya existe un informe para esta máquina
        SET @existe_informe = (SELECT COUNT(*) FROM informe_distribucion 
                              WHERE ID_Maquina = NEW.ID_Maquina);
        
        IF @existe_informe = 0 THEN
            -- Solo crear nuevo informe si no existe uno previo
            INSERT INTO informe_distribucion (
                ID_Distribucion,
                ID_Maquina, 
                ID_Usuario_Comprobador, 
                ID_Comercio,
                estado,
                fecha_alta
            ) VALUES (
                UUID(),
                NEW.ID_Maquina,
                NEW.ID_Tecnico_Comprobador, 
                NEW.ID_Comercio,
                'Distribuyendose',
                CURRENT_TIMESTAMP
            );
        ELSE
            -- Actualizar informe existente
            UPDATE informe_distribucion 
            SET estado = 'Distribuyendose',
                fecha_alta = CURRENT_TIMESTAMP,
                fecha_baja = NULL
            WHERE ID_Maquina = NEW.ID_Maquina;
        END IF;
    END IF;
END //

CREATE TRIGGER after_maquina_estado_change
AFTER UPDATE ON MaquinaRecreativa
FOR EACH ROW
BEGIN
    IF NEW.Estado != OLD.Estado THEN
        -- Actualizar el informe de distribución correspondiente
        IF NEW.Estado IN ('Operativa', 'No operativa', 'Retirada') THEN
            UPDATE informe_distribucion 
            SET estado = NEW.Estado,
                fecha_baja = CASE WHEN NEW.Estado = 'Retirada' THEN NOW() ELSE NULL END
            WHERE ID_Maquina = NEW.ID_Maquina;
        END IF;
    END IF;
END //

-- Procedimiento para registrar una máquina
CREATE PROCEDURE sp_registrar_maquina(
    IN p_nombre VARCHAR(100),
    IN p_tipo VARCHAR(50),
    IN p_id_ensamblador CHAR(36),
    IN p_id_comprobador CHAR(36),
    IN p_id_comercio CHAR(36),
    OUT p_id_maquina CHAR(36)
)
BEGIN
    DECLARE new_uuid CHAR(36);
    SET new_uuid = UUID();
    SET @v_fecha = CURDATE();
    
    -- Registrar nueva máquina con valores iniciales por defecto
    INSERT INTO MaquinaRecreativa (
        ID_Maquina,
        Nombre_Maquina, 
        Tipo, 
        Fecha_Registro, 
        ID_Tecnico_Ensamblador, 
        ID_Tecnico_Comprobador, 
        ID_Comercio,
        Etapa, 
        Estado
    ) VALUES (
        new_uuid,
        p_nombre, 
        p_tipo, 
        @v_fecha,
        p_id_ensamblador, 
        p_id_comprobador, 
        p_id_comercio,
        'Montaje', 
        'Ensamblandose'
    );
    
    SET p_id_maquina = new_uuid;
    
    -- Incrementar máquinas en comercio destino
    UPDATE Comercio 
    SET Cantidad_Maquinas = Cantidad_Maquinas + 1 
    WHERE ID_Comercio = p_id_comercio;
END //

-- Procedimiento para actualizar estado de máquina
CREATE PROCEDURE sp_actualizar_estado_maquina(
    IN p_id_maquina CHAR(36),
    IN p_estado VARCHAR(20),
    IN p_etapa VARCHAR(20)
)
BEGIN
    IF p_etapa IS NULL THEN
        UPDATE MaquinaRecreativa 
        SET Estado = p_estado
        WHERE ID_Maquina = p_id_maquina;
    ELSE
        UPDATE MaquinaRecreativa 
        SET Estado = p_estado, 
            Etapa = p_etapa
        WHERE ID_Maquina = p_id_maquina;
    END IF;
END //

-- Procedimiento para asignar técnico de mantenimiento
CREATE PROCEDURE sp_asignar_tecnico_mantenimiento(
    IN p_id_maquina CHAR(36),
    IN p_id_tecnico CHAR(36)
)
BEGIN
    UPDATE MaquinaRecreativa 
    SET ID_Tecnico_Mantenimiento = p_id_tecnico 
    WHERE ID_Maquina = p_id_maquina;
    
    -- Incrementar actividades del técnico
    UPDATE Tecnico 
    SET Cantidad_Actividades = Cantidad_Actividades + 1 
    WHERE ID_Tecnico = p_id_tecnico;
END //

-- Procedimiento para obtener máquinas por técnico ensamblador
CREATE PROCEDURE sp_obtener_maquinas_por_tecnico_ensamblador(IN p_id_tecnico CHAR(36))
BEGIN
    SELECT m.*, c.Nombre as NombreComercio, c.Direccion as DireccionComercio
    FROM MaquinaRecreativa m 
    JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio 
    WHERE (m.Estado = 'Ensamblandose' OR m.Estado = 'Reensamblandose')
    AND m.ID_Tecnico_Ensamblador = p_id_tecnico;
END //

-- Procedimiento para obtener máquinas por técnico comprobador
CREATE PROCEDURE sp_obtener_maquinas_por_tecnico_comprobador(IN p_id_tecnico CHAR(36))
BEGIN
    SELECT m.*, c.Nombre as NombreComercio, c.Direccion as DireccionComercio
    FROM MaquinaRecreativa m 
    JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio 
    WHERE m.Estado = 'Comprobandose'
    AND m.ID_Tecnico_Comprobador = p_id_tecnico;
END //

-- Procedimiento para obtener máquinas por técnico de mantenimiento
CREATE PROCEDURE sp_obtener_maquinas_por_tecnico_mantenimiento(IN p_id_tecnico CHAR(36))
BEGIN
    SELECT m.*, c.Nombre as NombreComercio, c.Direccion as DireccionComercio
    FROM MaquinaRecreativa m 
    JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio 
    WHERE m.Estado = 'No operativa' 
    AND m.ID_Tecnico_Mantenimiento = p_id_tecnico;
END //

-- Procedimiento para obtener máquinas por estado
CREATE PROCEDURE sp_obtener_maquinas_por_estado(IN p_estado VARCHAR(20))
BEGIN
    SELECT m.*, c.Nombre as NombreComercio, c.Direccion as DireccionComercio
    FROM MaquinaRecreativa m 
    JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio 
    WHERE m.Estado = p_estado;
END //

-- Procedimiento para obtener máquinas por etapa
CREATE PROCEDURE sp_obtener_maquinas_por_etapa(IN p_etapa VARCHAR(20))
BEGIN
    SELECT m.*, c.Nombre as NombreComercio, c.Direccion as DireccionComercio
    FROM MaquinaRecreativa m 
    JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio 
    WHERE m.Etapa = p_etapa;
END //

-- Procedimiento para obtener máquina por ID
CREATE PROCEDURE sp_obtener_maquina_por_id(IN p_id CHAR(36))
BEGIN
    SELECT m.*, c.Nombre as NombreComercio, c.Direccion as DireccionComercio
    FROM MaquinaRecreativa m 
    JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio 
    WHERE m.ID_Maquina = p_id;
END //

-- Procedimiento para obtener máquinas operativas por comercio
CREATE PROCEDURE sp_obtener_maquinas_operativas_por_comercio(IN p_id_comercio CHAR(36))
BEGIN
    SELECT m.*, c.Nombre as NombreComercio, c.Tipo as TipoComercio
    FROM MaquinaRecreativa m
    JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.ID_Comercio = p_id_comercio 
    AND m.Estado = 'Operativa'
    AND m.Etapa = 'Recaudacion';
END //

-- Procedimiento para obtener máquinas por etapa y estado
CREATE PROCEDURE sp_obtener_maquinas_por_etapa_y_estado(
    IN p_etapa VARCHAR(20),
    IN p_estado VARCHAR(20)
)
BEGIN
    SELECT m.*, c.Nombre as NombreComercio, c.Direccion as DireccionComercio
    FROM MaquinaRecreativa m
    JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.Etapa = p_etapa AND m.Estado = p_estado;
END //

-- Procedimiento para insertar montaje
CREATE PROCEDURE sp_insertar_montaje(
    IN p_id_maquina CHAR(36),
    IN p_id_componente CHAR(36),
    IN p_id_tecnico CHAR(36),
    IN p_detalle TEXT,
    OUT p_id_montaje CHAR(36)
)
BEGIN
    DECLARE new_uuid CHAR(36);
    SET new_uuid = UUID();
    
    INSERT INTO montaje (
        ID_Montaje,
        fecha, 
        ID_Maquina, 
        ID_Componente, 
        ID_Tecnico, 
        detalle
    ) VALUES (
        new_uuid,
        NOW(), 
        p_id_maquina, 
        p_id_componente, 
        p_id_tecnico, 
        p_detalle
    );
    
    SET p_id_montaje = new_uuid;
END //

DELIMITER ;

DELIMITER //

-- Procedimiento para registrar recaudación
CREATE PROCEDURE sp_registrar_recaudacion(
    IN p_tipo_comercio ENUM('Minorista', 'Mayorista'),
    IN p_id_maquina CHAR(36),
    IN p_id_usuario CHAR(36),
    IN p_monto_total DECIMAL(10,2),
    IN p_monto_empresa DECIMAL(10,2),
    IN p_monto_comercio DECIMAL(10,2),
    IN p_fecha DATETIME,
    IN p_detalle TEXT,
    IN p_porcentaje_comercio DECIMAL(5,2),
    OUT p_id_recaudacion CHAR(36)
)
BEGIN
    DECLARE new_uuid CHAR(36);
    SET new_uuid = UUID();
    
    INSERT INTO recaudaciones (
        ID_Recaudacion,
        Tipo_Comercio, 
        ID_Maquina, 
        ID_Usuario, 
        Monto_Total, 
        Monto_Empresa, 
        Monto_Comercio,
        fecha, 
        detalle, 
        Porcentaje_Comercio
    ) VALUES (
        new_uuid,
        p_tipo_comercio,
        p_id_maquina,
        p_id_usuario,
        p_monto_total,
        p_monto_empresa,
        p_monto_comercio,
        p_fecha,
        IFNULL(p_detalle, ''),
        p_porcentaje_comercio
    );
    
    SET p_id_recaudacion = new_uuid;
END //

-- Procedimiento para obtener recaudaciones con filtros
CREATE PROCEDURE sp_obtener_recaudaciones(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_id_maquina CHAR(36),
    IN p_tipo_comercio VARCHAR(20)
)
BEGIN
    SELECT r.*, m.Nombre_Maquina, c.Nombre as Nombre_Comercio, c.Tipo as Tipo_Comercio,
           u.nombre as UsuarioNombre, u.apellido as UsuarioApellido
    FROM recaudaciones r
    JOIN MaquinaRecreativa m ON r.ID_Maquina = m.ID_Maquina
    JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    JOIN usuario u ON r.ID_Usuario = u.ID_Usuario
    WHERE (p_fecha_inicio IS NULL OR r.fecha >= p_fecha_inicio)
    AND (p_fecha_fin IS NULL OR r.fecha <= p_fecha_fin)
    AND (p_id_maquina IS NULL OR r.ID_Maquina = p_id_maquina)
    AND (p_tipo_comercio IS NULL OR c.Tipo = p_tipo_comercio)
    ORDER BY r.fecha DESC;
END //

-- Procedimiento para obtener resumen de recaudaciones limitado
CREATE PROCEDURE sp_obtener_resumen_recaudaciones_limitado(IN p_limit INT)
BEGIN
    SELECT 
        c.Tipo as Tipo_Comercio,
        COUNT(r.ID_Recaudacion) as TotalRecaudaciones,
        SUM(r.Monto_Total) as TotalRecaudado,
        SUM(r.Monto_Empresa) as TotalEmpresa,
        SUM(r.Monto_Comercio) as TotalComercio
    FROM recaudaciones r
    JOIN MaquinaRecreativa m ON r.ID_Maquina = m.ID_Maquina
    JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    GROUP BY c.Tipo
    ORDER BY TotalRecaudado DESC
    LIMIT p_limit;
END //

-- Procedimiento para actualizar recaudación
CREATE PROCEDURE sp_actualizar_recaudacion(
    IN p_ID_Recaudacion CHAR(36),
    IN p_ID_Maquina CHAR(36),
    IN p_Tipo_Comercio VARCHAR(20),
    IN p_Monto_Total DECIMAL(10,2),
    IN p_Monto_Empresa DECIMAL(10,2),
    IN p_Monto_Comercio DECIMAL(10,2),
    IN p_fecha DATETIME,
    IN p_detalle TEXT,
    IN p_Porcentaje_Comercio DECIMAL(5,2)
)
BEGIN
    DECLARE recaudacion_existe INT;
    DECLARE maquina_existe INT;
    DECLARE tipo_comercio_valido ENUM('Minorista', 'Mayorista');
    DECLARE monto_empresa_calc DECIMAL(10,2);
    DECLARE monto_comercio_calc DECIMAL(10,2);

    -- Verificar que la recaudación existe
    SELECT COUNT(*) INTO recaudacion_existe 
    FROM recaudaciones 
    WHERE ID_Recaudacion = p_ID_Recaudacion;

    IF recaudacion_existe = 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'La recaudación no existe';
    END IF;

    -- Verificar que la máquina existe
    SELECT COUNT(*) INTO maquina_existe 
    FROM MaquinaRecreativa 
    WHERE ID_Maquina = p_ID_Maquina;

    IF maquina_existe = 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'La máquina asociada no existe';
    END IF;

    -- Validar que el tipo de comercio sea válido
    IF p_Tipo_Comercio NOT IN ('Minorista', 'Mayorista') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Tipo de comercio no válido';
    END IF;

    -- Validar montos
    IF p_Monto_Total <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El monto total debe ser positivo';
    END IF;

    -- Calcular montos según tipo de comercio
    IF p_Tipo_Comercio = 'Mayorista' THEN
        -- Para mayoristas, validar porcentaje
        IF p_Porcentaje_Comercio <= 0 OR p_Porcentaje_Comercio > 100 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Porcentaje para comercio debe estar entre 0 y 100';
        END IF;
        
        SET monto_comercio_calc = p_Monto_Total * (p_Porcentaje_Comercio / 100);
        SET monto_empresa_calc = p_Monto_Total - monto_comercio_calc;
    ELSE
        -- Para minoristas, monto comercio es 0
        SET monto_comercio_calc = 0;
        SET monto_empresa_calc = p_Monto_Total;
        SET p_Porcentaje_Comercio = 0;
    END IF;

    -- Validar suma de montos
    IF ABS((monto_empresa_calc + monto_comercio_calc) - p_Monto_Total) > 0.01 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La suma de montos no coincide con el total';
    END IF;

    -- Actualizar los datos
    UPDATE recaudaciones SET 
        ID_Maquina = p_ID_Maquina,
        Tipo_Comercio = p_Tipo_Comercio,
        Monto_Total = p_Monto_Total,
        Monto_Empresa = monto_empresa_calc,
        Monto_Comercio = monto_comercio_calc,
        fecha = p_fecha,
        detalle = IFNULL(p_detalle, ''),
        Porcentaje_Comercio = p_Porcentaje_Comercio
    WHERE ID_Recaudacion = p_ID_Recaudacion;

    -- Devolver el número de filas afectadas
    SELECT ROW_COUNT() AS filas_afectadas;
END //

-- Procedimiento para eliminar recaudación
CREATE PROCEDURE sp_eliminar_recaudacion(IN p_id_recaudacion CHAR(36))
BEGIN
    DELETE FROM recaudaciones WHERE ID_Recaudacion = p_id_recaudacion;
END //

-- Procedimiento para guardar informe principal
CREATE PROCEDURE sp_guardar_informe_principal(
    IN p_id_recaudacion CHAR(36),
    IN p_ci_usuario VARCHAR(100),
    IN p_nombre_maquina VARCHAR(100),
    IN p_id_comercio CHAR(36),
    IN p_nombre_comercio VARCHAR(100),
    IN p_direccion_comercio TEXT,
    IN p_telefono_comercio VARCHAR(15),
    IN p_pago_ensamblador DECIMAL(10,2),
    IN p_pago_comprobador DECIMAL(10,2),
    IN p_pago_mantenimiento DECIMAL(10,2),
    IN p_empresa_nombre VARCHAR(100),
    IN p_empresa_descripcion VARCHAR(255),
    OUT p_id_informe CHAR(36)
)
BEGIN
    DECLARE new_uuid CHAR(36);
    SET new_uuid = UUID();
    
    INSERT INTO informes_recaudacion (
        ID_Informe,
        ID_Recaudacion, 
        CI_Usuario, 
        Nombre_Maquina, 
        ID_Comercio,
        Nombre_Comercio, 
        Direccion_Comercio, 
        Telefono_Comercio,
        Pago_Ensamblador, 
        Pago_Comprobador, 
        Pago_Mantenimiento,
        empresa_nombre, 
        empresa_descripcion
    ) VALUES (
        new_uuid,
        p_id_recaudacion,
        p_ci_usuario,
        p_nombre_maquina,
        p_id_comercio,
        p_nombre_comercio,
        p_direccion_comercio,
        p_telefono_comercio,
        p_pago_ensamblador,
        p_pago_comprobador,
        p_pago_mantenimiento,
        p_empresa_nombre,
        p_empresa_descripcion
    );
    
    SET p_id_informe = new_uuid;
END //

-- Procedimiento para guardar detalle de componente
CREATE PROCEDURE sp_guardar_detalle_componente(
    IN p_id_informe CHAR(36),
    IN p_id_componente CHAR(36)
)
BEGIN
    DECLARE new_uuid CHAR(36);
    SET new_uuid = UUID();
    
    INSERT INTO informe_detalle (
        ID_Informe_Detalle,
        ID_Informe, 
        ID_Componente
    ) VALUES (
        new_uuid,
        p_id_informe, 
        p_id_componente
    );
END //

-- Procedimiento para obtener informe principal
CREATE PROCEDURE sp_obtener_informe_principal(IN p_id_recaudacion CHAR(36))
BEGIN
    SELECT * FROM informes_recaudacion 
    WHERE ID_Recaudacion = p_id_recaudacion 
    LIMIT 1;
END //

-- Procedimiento para obtener componentes de informe
CREATE PROCEDURE sp_obtener_componentes_informe(IN p_id_informe CHAR(36))
BEGIN
    SELECT c.ID_Componente, c.nombre, c.tipo, c.precio
    FROM informe_detalle id
    JOIN componente c ON id.ID_Componente = c.ID_Componente
    WHERE id.ID_Informe = p_id_informe;
END //
CREATE PROCEDURE sp_obtener_recaudacion(IN p_id_recaudacion CHAR(36))
BEGIN
    SELECT 
        r.*, 
        m.Nombre_Maquina, 
        c.Nombre as Nombre_Comercio, 
        c.Tipo as Tipo_Comercio,
        u.nombre as UsuarioNombre, 
        u.apellido as UsuarioApellido
    FROM recaudaciones r
    JOIN MaquinaRecreativa m ON r.ID_Maquina = m.ID_Maquina
    JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    JOIN usuario u ON r.ID_Usuario = u.ID_Usuario
    WHERE r.ID_Recaudacion = p_id_recaudacion;
END //
DELIMITER ;

DELIMITER //

-- Procedimiento para actualizar informe de distribución (UUID)
CREATE PROCEDURE sp_actualizar_informe_distribucion(
    IN p_id_maquina CHAR(36),
    IN p_estado VARCHAR(20)
)
BEGIN
    IF EXISTS (
        SELECT ID_Distribucion FROM informe_distribucion 
        WHERE ID_Maquina = p_id_maquina
    ) THEN
        UPDATE informe_distribucion 
        SET estado = p_estado,
            fecha_baja = CASE 
                WHEN p_estado = 'Operativa' THEN NULL 
                ELSE COALESCE(fecha_baja, CURRENT_TIMESTAMP) 
            END
        WHERE ID_Maquina = p_id_maquina;
    ELSE
        INSERT INTO informe_distribucion (
            ID_Maquina, estado, fecha_alta, fecha_baja
        ) VALUES (
            p_id_maquina, 
            p_estado, 
            CURRENT_TIMESTAMP, 
            CASE WHEN p_estado = 'Operativa' THEN NULL ELSE NULL END
        );
    END IF;
END //

-- Procedimiento para crear informe de distribución (UUID)
CREATE PROCEDURE sp_crear_informe_distribucion(
    IN p_id_maquina CHAR(36),
    IN p_id_usuario CHAR(36),
    IN p_id_comercio CHAR(36)
)
BEGIN
    IF EXISTS (
        SELECT ID_Distribucion FROM informe_distribucion 
        WHERE ID_Maquina = p_id_maquina
    ) THEN
        UPDATE informe_distribucion 
        SET ID_Usuario_Comprobador = p_id_usuario, 
            ID_Comercio = p_id_comercio,
            estado = 'Distribuyendose',
            fecha_alta = CURRENT_TIMESTAMP,
            fecha_baja = NULL
        WHERE ID_Maquina = p_id_maquina;
    ELSE
        INSERT INTO informe_distribucion (
            ID_Maquina, ID_Usuario_Comprobador, ID_Comercio, 
            estado, fecha_alta
        ) VALUES (p_id_maquina, p_id_usuario, p_id_comercio, 'Distribuyendose', CURRENT_TIMESTAMP);
    END IF;
END //

-- Procedimiento para obtener informes de distribución con filtros (UUID)
CREATE PROCEDURE sp_obtener_informes_distribucion(
    IN p_estado VARCHAR(20),
    IN p_id_comercio CHAR(36),
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_id_maquina CHAR(36)
)
BEGIN
    SELECT 
        d.ID_Distribucion,
        d.ID_Maquina,
        d.ID_Usuario_Comprobador,
        d.ID_Comercio,
        d.fecha_alta,
        d.fecha_baja,
        d.estado,
        m.Nombre_Maquina,
        CONCAT(u.nombre, ' ', u.apellido) AS Nombre_Tecnico,
        c.Nombre AS Nombre_Comercio,
        c.Direccion AS Direccion_Comercio,
        c.Telefono AS Telefono_Comercio,
        c.Tipo AS Tipo_Comercio
    FROM informe_distribucion d
    JOIN MaquinaRecreativa m ON d.ID_Maquina = m.ID_Maquina
    JOIN usuario u ON d.ID_Usuario_Comprobador = u.ID_Usuario
    JOIN Comercio c ON d.ID_Comercio = c.ID_Comercio
    WHERE 1=1
    AND (p_estado IS NULL OR d.estado = p_estado)
    AND (p_id_comercio IS NULL OR d.ID_Comercio = p_id_comercio)
    AND (p_fecha_inicio IS NULL OR d.fecha_alta >= p_fecha_inicio)
    AND (p_fecha_fin IS NULL OR d.fecha_alta <= p_fecha_fin)
    AND (p_id_maquina IS NULL OR d.ID_Maquina = p_id_maquina)
    ORDER BY d.fecha_alta DESC;
END //

DELIMITER ;

DELIMITER //

-- Procedimiento para crear un comentario
CREATE PROCEDURE sp_crear_comentario(
    IN p_id_reporte CHAR(36),
    IN p_id_usuario_emisor CHAR(36),
    IN p_comentario TEXT,
    OUT p_id_comentario CHAR(36)
)
BEGIN
    DECLARE new_uuid CHAR(36);
    SET new_uuid = UUID();

    INSERT INTO comentario (id_comentario, id_reporte, id_usuario_emisor, comentario, fecha_hora) 
    VALUES (new_uuid, p_id_reporte, p_id_usuario_emisor, p_comentario, NOW());

    SET p_id_comentario = new_uuid;
END //
CREATE PROCEDURE sp_obtener_comentarios_por_reporte(
    IN p_id_reporte CHAR(36),
    IN p_id_usuario CHAR(36)
)
BEGIN
    -- Verificar acceso al reporte
    IF EXISTS (
        SELECT 1 FROM reporte 
        WHERE id_reporte = p_id_reporte 
        AND (id_usuario_emisor = p_id_usuario OR id_usuario_destinatario = p_id_usuario)
    ) THEN
        SELECT c.*, u.nombre, u.apellido, u.tipo
        FROM comentario c
        JOIN usuario u ON c.id_usuario_emisor = u.id_usuario
        WHERE c.id_reporte = p_id_reporte
        ORDER BY c.fecha_hora ASC;
    END IF;
END //
CREATE PROCEDURE sp_obtener_comentarios_por_chat(
    IN p_id_emisor CHAR(36),
    IN p_id_destinatario CHAR(36)
)
BEGIN
    SELECT c.*, u.nombre, u.apellido, u.tipo
    FROM comentario c
    JOIN usuario u ON c.id_usuario_emisor = u.id_usuario
    JOIN reporte r ON c.id_reporte = r.id_reporte
    WHERE (r.id_usuario_emisor = p_id_emisor AND r.id_usuario_destinatario = p_id_destinatario)
       OR (r.id_usuario_emisor = p_id_destinatario AND r.id_usuario_destinatario = p_id_emisor)
    ORDER BY c.fecha_hora ASC;
END //

DELIMITER ;