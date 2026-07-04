-- ============================================================
-- STORED PROCEDURES Y TRIGGERS - bd_recrea_sys
-- Compatibles con MySQL 8.0+
-- ============================================================

USE bd_recrea_sys;
DELIMITER $$

-- ==============================================================
-- 1. MÓDULO USUARIO
-- ==============================================================

DROP PROCEDURE IF EXISTS sp_buscar_usuario_por_username $$
CREATE PROCEDURE sp_buscar_usuario_por_username(IN p_username VARCHAR(25))
BEGIN
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
END $$

DROP PROCEDURE IF EXISTS sp_buscar_usuario_por_email $$
CREATE PROCEDURE sp_buscar_usuario_por_email(IN p_email VARCHAR(255))
BEGIN
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
END $$

DROP PROCEDURE IF EXISTS sp_buscar_usuario_por_id $$
CREATE PROCEDURE sp_buscar_usuario_por_id(IN p_id CHAR(36))
BEGIN
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
END $$

DROP PROCEDURE IF EXISTS sp_insertar_usuario $$
CREATE PROCEDURE sp_insertar_usuario(
    IN p_id           CHAR(36),
    IN p_nombre       VARCHAR(50),
    IN p_apellido     VARCHAR(50),
    IN p_ci           VARCHAR(100),
    IN p_email        VARCHAR(100),
    IN p_username     VARCHAR(25),
    IN p_contrasena   VARCHAR(255),
    IN p_tipo         ENUM('Administrador','Logistica','Tecnico','Contabilidad','Usuario'),
    IN p_estado       ENUM('Pendiente de asignacion','Activo','Inhabilitado')
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;
    INSERT INTO usuario
        (ID_Usuario, nombre, apellido, ci, email, usuario_asignado, contrasena, tipo, estado)
    VALUES
        (p_id, p_nombre, p_apellido, p_ci, p_email, p_username, p_contrasena, p_tipo, p_estado);
    COMMIT;
END $$

DROP PROCEDURE IF EXISTS sp_actualizar_usuario $$
CREATE PROCEDURE sp_actualizar_usuario(
    IN p_id         CHAR(36),
    IN p_nombre     VARCHAR(50),
    IN p_apellido   VARCHAR(50),
    IN p_ci         VARCHAR(100),
    IN p_email      VARCHAR(100),
    IN p_username   VARCHAR(25),
    IN p_contrasena VARCHAR(255),
    IN p_tipo       ENUM('Administrador','Logistica','Tecnico','Contabilidad','Usuario'),
    IN p_estado     ENUM('Pendiente de asignacion','Activo','Inhabilitado')
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;
    UPDATE usuario
    SET nombre          = p_nombre,
        apellido        = p_apellido,
        ci              = p_ci,
        email           = p_email,
        usuario_asignado = p_username,
        contrasena      = p_contrasena,
        tipo            = p_tipo,
        estado          = p_estado
    WHERE ID_Usuario = p_id;
    COMMIT;
END $$

DROP PROCEDURE IF EXISTS sp_cambiar_estado_usuario $$
CREATE PROCEDURE sp_cambiar_estado_usuario(
    IN p_id     CHAR(36),
    IN p_estado ENUM('Pendiente de asignacion','Activo','Inhabilitado')
)
BEGIN
    UPDATE usuario SET estado = p_estado WHERE ID_Usuario = p_id;
END $$

DROP PROCEDURE IF EXISTS sp_cambiar_contrasena_usuario $$
CREATE PROCEDURE sp_cambiar_contrasena_usuario(
    IN p_id         CHAR(36),
    IN p_contrasena VARCHAR(255)
)
BEGIN
    UPDATE usuario SET contrasena = p_contrasena WHERE ID_Usuario = p_id;
END $$

DROP PROCEDURE IF EXISTS sp_actualizar_username $$
CREATE PROCEDURE sp_actualizar_username(
    IN p_id       CHAR(36),
    IN p_username VARCHAR(25)
)
BEGIN
    UPDATE usuario SET usuario_asignado = p_username WHERE ID_Usuario = p_id;
END $$

DROP PROCEDURE IF EXISTS sp_eliminar_usuario $$
CREATE PROCEDURE sp_eliminar_usuario(IN p_id CHAR(36))
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;
    DELETE FROM comentario                   WHERE ID_Usuario_Emisor = p_id;
    DELETE FROM notificaciones               WHERE ID_Usuario = p_id;
    DELETE FROM NotificacionMaquinaRecreativa WHERE ID_Destinatario = p_id;
    DELETE FROM componente_usuario           WHERE ID_Usuario = p_id;
    DELETE FROM inicio_sesion                WHERE ID_Usuario = p_id;
    DELETE FROM historial_actividades        WHERE ID_Usuario = p_id;
    DELETE FROM Tecnico                      WHERE ID_Tecnico = p_id;
    DELETE FROM Logistica                    WHERE ID_Logistica = p_id;
    DELETE FROM reporte
        WHERE ID_Usuario_Emisor = p_id OR ID_Usuario_Destinatario = p_id;
    DELETE FROM usuario                      WHERE ID_Usuario = p_id;
    COMMIT;
END $$

DROP PROCEDURE IF EXISTS sp_listar_usuarios $$
CREATE PROCEDURE sp_listar_usuarios(
    IN p_tipo   VARCHAR(20),
    IN p_estado VARCHAR(30),
    IN p_ci     VARCHAR(100),
    IN p_limit  INT,
    IN p_offset INT
)
BEGIN
    SET @sql = CONCAT(
        'SELECT u.*, t.Especialidad, t.Cantidad_Actividades
         FROM usuario u
         LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
         WHERE 1=1'
    );

    IF p_tipo IS NOT NULL AND p_tipo != '' THEN
        SET @sql = CONCAT(@sql, ' AND u.tipo = ''', p_tipo, '''');
    END IF;
    IF p_estado IS NOT NULL AND p_estado != '' THEN
        SET @sql = CONCAT(@sql, ' AND u.estado = ''', p_estado, '''');
    END IF;
    IF p_ci IS NOT NULL AND p_ci != '' THEN
        SET @sql = CONCAT(@sql, ' AND u.ci = ''', p_ci, '''');
    END IF;

    SET @sql = CONCAT(@sql, ' ORDER BY u.nombre ASC');
    SET @sql = CONCAT(@sql, ' LIMIT ', p_limit, ' OFFSET ', p_offset);

    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END $$

DROP PROCEDURE IF EXISTS sp_listar_usuarios_por_tipo $$
CREATE PROCEDURE sp_listar_usuarios_por_tipo(
    IN p_tipo      VARCHAR(20),
    IN p_excluir   CHAR(36)
)
BEGIN
    IF p_excluir IS NULL OR p_excluir = '' THEN
        SELECT u.*, t.Especialidad, t.Cantidad_Actividades
        FROM usuario u
        LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
        WHERE u.tipo = p_tipo
        ORDER BY u.nombre ASC;
    ELSE
        SELECT u.*, t.Especialidad, t.Cantidad_Actividades
        FROM usuario u
        LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
        WHERE u.tipo = p_tipo AND u.ID_Usuario != p_excluir
        ORDER BY u.nombre ASC;
    END IF;
END $$

DROP PROCEDURE IF EXISTS sp_existe_email $$
CREATE PROCEDURE sp_existe_email(IN p_email VARCHAR(255), OUT p_existe TINYINT)
BEGIN
    SELECT COUNT(*) INTO p_existe FROM usuario WHERE email = p_email;
END $$

DROP PROCEDURE IF EXISTS sp_existe_ci $$
CREATE PROCEDURE sp_existe_ci(IN p_ci VARCHAR(100), OUT p_existe TINYINT)
BEGIN
    SELECT COUNT(*) INTO p_existe FROM usuario WHERE ci = p_ci;
END $$

DROP PROCEDURE IF EXISTS sp_existe_username $$
CREATE PROCEDURE sp_existe_username(IN p_username VARCHAR(25), OUT p_existe TINYINT)
BEGIN
    SELECT COUNT(*) INTO p_existe FROM usuario WHERE usuario_asignado = p_username;
END $$

DROP PROCEDURE IF EXISTS sp_existe_username_excluyendo_id $$
CREATE PROCEDURE sp_existe_username_excluyendo_id(
    IN  p_username VARCHAR(25),
    IN  p_id       CHAR(36),
    OUT p_existe   TINYINT
)
BEGIN
    SELECT COUNT(*) INTO p_existe
    FROM usuario
    WHERE usuario_asignado = p_username AND ID_Usuario != p_id;
END $$

DROP PROCEDURE IF EXISTS sp_estadisticas_usuarios $$
CREATE PROCEDURE sp_estadisticas_usuarios()
BEGIN
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
END $$

DROP PROCEDURE IF EXISTS sp_registrar_actividad $$
CREATE PROCEDURE sp_registrar_actividad(
    IN p_id_usuario  CHAR(36),
    IN p_descripcion TEXT
)
BEGIN
    INSERT INTO historial_actividades (ID_Usuario, descripcion, fecha_registro)
    VALUES (p_id_usuario, p_descripcion, NOW());
END $$

DROP PROCEDURE IF EXISTS sp_obtener_historial_actividades $$
CREATE PROCEDURE sp_obtener_historial_actividades(
    IN p_id_usuario CHAR(36),
    IN p_limite     INT
)
BEGIN
    SELECT * FROM historial_actividades
    WHERE ID_Usuario = p_id_usuario
    ORDER BY fecha_registro DESC
    LIMIT p_limite;
END $$

DROP PROCEDURE IF EXISTS sp_registrar_logout $$
CREATE PROCEDURE sp_registrar_logout(IN p_id_usuario CHAR(36))
BEGIN
    UPDATE inicio_sesion
    SET fecha_ultima_sesion = NOW()
    WHERE ID_Usuario = p_id_usuario
      AND fecha_ultima_sesion IS NULL
    ORDER BY fecha_inicio DESC
    LIMIT 1;
END $$

-- ==============================================================
-- 2. MÓDULO TÉCNICO
-- ==============================================================

DROP PROCEDURE IF EXISTS sp_insertar_tecnico $$
CREATE PROCEDURE sp_insertar_tecnico(
    IN p_id          CHAR(36),
    IN p_especialidad ENUM('Ensamblador','Comprobador','Mantenimiento'),
    IN p_actividades INT
)
BEGIN
    INSERT INTO Tecnico (ID_Tecnico, Especialidad, Cantidad_Actividades)
    VALUES (p_id, p_especialidad, p_actividades)
    ON DUPLICATE KEY UPDATE
        Especialidad         = VALUES(Especialidad),
        Cantidad_Actividades = VALUES(Cantidad_Actividades);
END $$

DROP PROCEDURE IF EXISTS sp_incrementar_actividades_tecnico $$
CREATE PROCEDURE sp_incrementar_actividades_tecnico(IN p_id CHAR(36))
BEGIN
    UPDATE Tecnico
    SET Cantidad_Actividades = Cantidad_Actividades + 1
    WHERE ID_Tecnico = p_id;
END $$

DROP PROCEDURE IF EXISTS sp_tecnicos_por_especialidad $$
CREATE PROCEDURE sp_tecnicos_por_especialidad(
    IN p_especialidad ENUM('Ensamblador','Comprobador','Mantenimiento')
)
BEGIN
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
END $$

DROP PROCEDURE IF EXISTS sp_tecnicos_disponibles_por_especialidad $$
CREATE PROCEDURE sp_tecnicos_disponibles_por_especialidad(
    IN p_especialidad VARCHAR(20)
)
BEGIN
    SELECT u.*, t.Especialidad, t.Cantidad_Actividades
    FROM usuario u
    INNER JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
    WHERE t.Especialidad = p_especialidad
      AND u.estado = 'Activo'
    ORDER BY t.Cantidad_Actividades ASC;
END $$

-- ==============================================================
-- 3. MÓDULO COMERCIO
-- ==============================================================

DROP PROCEDURE IF EXISTS sp_insertar_comercio $$
CREATE PROCEDURE sp_insertar_comercio(
    IN p_id             CHAR(36),
    IN p_nombre         VARCHAR(100),
    IN p_tipo           ENUM('Minorista','Mayorista'),
    IN p_direccion      TEXT,
    IN p_telefono       VARCHAR(15),
    IN p_fecha_registro DATE
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;
    INSERT INTO Comercio (ID_Comercio, Nombre, Tipo, Direccion, Telefono, Fecha_Registro)
    VALUES (p_id, p_nombre, p_tipo, p_direccion, p_telefono, p_fecha_registro);
    COMMIT;
END $$

DROP PROCEDURE IF EXISTS sp_actualizar_comercio $$
CREATE PROCEDURE sp_actualizar_comercio(
    IN p_id        CHAR(36),
    IN p_nombre    VARCHAR(100),
    IN p_tipo      ENUM('Minorista','Mayorista'),
    IN p_direccion TEXT,
    IN p_telefono  VARCHAR(15)
)
BEGIN
    UPDATE Comercio
    SET Nombre    = p_nombre,
        Tipo      = p_tipo,
        Direccion = p_direccion,
        Telefono  = p_telefono
    WHERE ID_Comercio = p_id;
END $$

DROP PROCEDURE IF EXISTS sp_eliminar_comercio $$
CREATE PROCEDURE sp_eliminar_comercio(IN p_id CHAR(36))
BEGIN
    DECLARE v_maquinas INT DEFAULT 0;

    SELECT COUNT(*) INTO v_maquinas
    FROM MaquinaRecreativa WHERE ID_Comercio = p_id;

    IF v_maquinas > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No se puede eliminar: el comercio tiene máquinas asociadas';
    END IF;

    DELETE FROM Comercio WHERE ID_Comercio = p_id;
END $$

DROP PROCEDURE IF EXISTS sp_buscar_comercio_por_id $$
CREATE PROCEDURE sp_buscar_comercio_por_id(IN p_id CHAR(36))
BEGIN
    SELECT * FROM Comercio WHERE ID_Comercio = p_id LIMIT 1;
END $$

DROP PROCEDURE IF EXISTS sp_buscar_comercio_por_nombre $$
CREATE PROCEDURE sp_buscar_comercio_por_nombre(IN p_nombre VARCHAR(100))
BEGIN
    SELECT * FROM Comercio WHERE Nombre = p_nombre LIMIT 1;
END $$

DROP PROCEDURE IF EXISTS sp_listar_comercios $$
CREATE PROCEDURE sp_listar_comercios(
    IN p_tipo   VARCHAR(20),
    IN p_nombre VARCHAR(100),
    IN p_limit  INT,
    IN p_offset INT
)
BEGIN
    SET @sql = 'SELECT * FROM Comercio WHERE 1=1';

    IF p_tipo IS NOT NULL AND p_tipo != '' THEN
        SET @sql = CONCAT(@sql, ' AND Tipo = ''', p_tipo, '''');
    END IF;
    IF p_nombre IS NOT NULL AND p_nombre != '' THEN
        SET @sql = CONCAT(@sql, ' AND Nombre LIKE ''%', p_nombre, '%''');
    END IF;

    SET @sql = CONCAT(@sql, ' ORDER BY Nombre ASC');
    SET @sql = CONCAT(@sql, ' LIMIT ', p_limit, ' OFFSET ', p_offset);

    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END $$

DROP PROCEDURE IF EXISTS sp_contar_comercios $$
CREATE PROCEDURE sp_contar_comercios(
    IN  p_tipo   VARCHAR(20),
    IN  p_nombre VARCHAR(100),
    OUT p_total  INT
)
BEGIN
    SELECT COUNT(*) INTO p_total FROM Comercio
    WHERE (p_tipo   IS NULL OR p_tipo   = '' OR Tipo   = p_tipo)
      AND (p_nombre IS NULL OR p_nombre = '' OR Nombre LIKE CONCAT('%', p_nombre, '%'));
END $$

DROP PROCEDURE IF EXISTS sp_comercio_tiene_maquinas $$
CREATE PROCEDURE sp_comercio_tiene_maquinas(
    IN  p_id     CHAR(36),
    OUT p_tiene  TINYINT
)
BEGIN
    SELECT COUNT(*) INTO p_tiene
    FROM MaquinaRecreativa WHERE ID_Comercio = p_id;
END $$

-- ==============================================================
-- 4. MÓDULO MÁQUINA RECREATIVA
-- ==============================================================

DROP PROCEDURE IF EXISTS sp_insertar_maquina $$
CREATE PROCEDURE sp_insertar_maquina(
    IN p_id                     CHAR(36),
    IN p_nombre                 VARCHAR(100),
    IN p_tipo                   VARCHAR(50),
    IN p_fecha_registro         DATE,
    IN p_estado                 VARCHAR(50),
    IN p_etapa                  VARCHAR(50),
    IN p_id_comercio            CHAR(36),
    IN p_id_tecnico_ensamblador CHAR(36),
    IN p_id_tecnico_comprobador CHAR(36),
    IN p_id_tecnico_mant        CHAR(36)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;
    INSERT INTO MaquinaRecreativa (
        ID_Maquina, Nombre_Maquina, Tipo, Fecha_Registro,
        Estado, Etapa, ID_Comercio,
        ID_Tecnico_Ensamblador, ID_Tecnico_Comprobador, ID_Tecnico_Mantenimiento
    ) VALUES (
        p_id, p_nombre, p_tipo, p_fecha_registro,
        p_estado, p_etapa, p_id_comercio,
        p_id_tecnico_ensamblador, p_id_tecnico_comprobador, p_id_tecnico_mant
    );
    COMMIT;
END $$

DROP PROCEDURE IF EXISTS sp_actualizar_estado_maquina $$
CREATE PROCEDURE sp_actualizar_estado_maquina(
    IN p_id     CHAR(36),
    IN p_estado VARCHAR(50),
    IN p_etapa  VARCHAR(50)
)
BEGIN
    UPDATE MaquinaRecreativa
    SET Estado = p_estado,
        Etapa  = p_etapa
    WHERE ID_Maquina = p_id;
END $$

DROP PROCEDURE IF EXISTS sp_actualizar_maquina $$
CREATE PROCEDURE sp_actualizar_maquina(
    IN p_id         CHAR(36),
    IN p_nombre     VARCHAR(100),
    IN p_tipo       VARCHAR(50),
    IN p_id_comercio CHAR(36),
    IN p_estado     VARCHAR(50),
    IN p_etapa      VARCHAR(50)
)
BEGIN
    UPDATE MaquinaRecreativa
    SET Nombre_Maquina = p_nombre,
        Tipo           = p_tipo,
        ID_Comercio    = p_id_comercio,
        Estado         = COALESCE(p_estado, Estado),
        Etapa          = COALESCE(p_etapa, Etapa)
    WHERE ID_Maquina = p_id;
END $$

DROP PROCEDURE IF EXISTS sp_asignar_tecnico_mantenimiento $$
CREATE PROCEDURE sp_asignar_tecnico_mantenimiento(
    IN p_id_maquina CHAR(36),
    IN p_id_tecnico CHAR(36)
)
BEGIN
    UPDATE MaquinaRecreativa
    SET ID_Tecnico_Mantenimiento = p_id_tecnico,
        Estado = 'No operativa'
    WHERE ID_Maquina = p_id_maquina;
END $$

DROP PROCEDURE IF EXISTS sp_buscar_maquina_por_id $$
CREATE PROCEDURE sp_buscar_maquina_por_id(IN p_id CHAR(36))
BEGIN
    SELECT m.*, c.Nombre AS NombreComercio, c.Direccion AS DireccionComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.ID_Maquina = p_id
    LIMIT 1;
END $$

DROP PROCEDURE IF EXISTS sp_maquinas_por_tecnico_ensamblador $$
CREATE PROCEDURE sp_maquinas_por_tecnico_ensamblador(IN p_id_tecnico CHAR(36))
BEGIN
    SELECT m.*, c.Nombre AS NombreComercio, c.Direccion AS DireccionComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.ID_Tecnico_Ensamblador = p_id_tecnico
    ORDER BY m.Fecha_Registro DESC;
END $$

DROP PROCEDURE IF EXISTS sp_maquinas_por_tecnico_comprobador $$
CREATE PROCEDURE sp_maquinas_por_tecnico_comprobador(IN p_id_tecnico CHAR(36))
BEGIN
    SELECT m.*, c.Nombre AS NombreComercio, c.Direccion AS DireccionComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.ID_Tecnico_Comprobador = p_id_tecnico
      AND m.Estado = 'Comprobandose'
    ORDER BY m.Fecha_Registro DESC;
END $$

DROP PROCEDURE IF EXISTS sp_maquinas_por_tecnico_mantenimiento $$
CREATE PROCEDURE sp_maquinas_por_tecnico_mantenimiento(IN p_id_tecnico CHAR(36))
BEGIN
    SELECT m.*, c.Nombre AS NombreComercio, c.Direccion AS DireccionComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.ID_Tecnico_Mantenimiento = p_id_tecnico
      AND m.Estado = 'No operativa'
    ORDER BY m.Fecha_Registro DESC;
END $$

DROP PROCEDURE IF EXISTS sp_maquinas_por_estado $$
CREATE PROCEDURE sp_maquinas_por_estado(IN p_estado VARCHAR(50))
BEGIN
    SELECT m.*, c.Nombre AS NombreComercio, c.Direccion AS DireccionComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.Estado = p_estado
    ORDER BY m.Fecha_Registro DESC;
END $$

DROP PROCEDURE IF EXISTS sp_maquinas_por_etapa $$
CREATE PROCEDURE sp_maquinas_por_etapa(IN p_etapa VARCHAR(50))
BEGIN
    SELECT m.*, c.Nombre AS NombreComercio, c.Direccion AS DireccionComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.Etapa = p_etapa
    ORDER BY m.Fecha_Registro DESC;
END $$

DROP PROCEDURE IF EXISTS sp_todas_las_maquinas $$
CREATE PROCEDURE sp_todas_las_maquinas()
BEGIN
    SELECT
        m.ID_Maquina,
        m.Nombre_Maquina,
        m.Tipo,
        m.Estado,
        m.Etapa,
        m.ID_Comercio,
        m.ID_Tecnico_Ensamblador,
        m.ID_Tecnico_Comprobador,
        m.Fecha_Registro,
        c.Nombre AS NombreComercio,
        c.Direccion AS DireccionComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    ORDER BY m.Fecha_Registro DESC;
END $$

DROP PROCEDURE IF EXISTS sp_maquinas_operativas_por_comercio $$
CREATE PROCEDURE sp_maquinas_operativas_por_comercio(IN p_id_comercio CHAR(36))
BEGIN
    SELECT m.*
    FROM MaquinaRecreativa m
    WHERE m.ID_Comercio = p_id_comercio
      AND m.Estado = 'Operativa'
      AND m.Etapa  = 'Recaudacion'
    ORDER BY m.Nombre_Maquina ASC;
END $$

DROP PROCEDURE IF EXISTS sp_maquinas_para_distribucion $$
CREATE PROCEDURE sp_maquinas_para_distribucion()
BEGIN
    SELECT m.*, c.Nombre AS NombreComercio, c.Direccion AS DireccionComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.Etapa  = 'Distribucion'
      AND m.Estado = 'Distribuyendose'
    ORDER BY m.Fecha_Registro DESC;
END $$

DROP PROCEDURE IF EXISTS sp_componentes_por_maquina $$
CREATE PROCEDURE sp_componentes_por_maquina(IN p_id_maquina CHAR(36))
BEGIN
    SELECT c.*, mo.fecha AS fecha_montaje, mo.detalle
    FROM montaje mo
    JOIN componente c ON mo.ID_Componente = c.ID_Componente
    WHERE mo.ID_Maquina = p_id_maquina
    ORDER BY mo.fecha DESC;
END $$

DROP PROCEDURE IF EXISTS sp_componentes_en_uso_por_maquina $$
CREATE PROCEDURE sp_componentes_en_uso_por_maquina(IN p_id_maquina CHAR(36))
BEGIN
    SELECT c.ID_Componente, c.tipo, c.nombre, c.precio, cu.fecha_asignacion
    FROM componente_usuario cu
    INNER JOIN componente c ON cu.ID_Componente = c.ID_Componente
    WHERE cu.ID_Maquina = p_id_maquina
      AND cu.fecha_liberacion IS NULL
    ORDER BY cu.fecha_asignacion DESC;
END $$

DROP PROCEDURE IF EXISTS sp_usuario_tiene_maquinas $$
CREATE PROCEDURE sp_usuario_tiene_maquinas(
    IN  p_id_usuario CHAR(36),
    OUT p_tiene      TINYINT
)
BEGIN
    SELECT COUNT(*) INTO p_tiene
    FROM MaquinaRecreativa
    WHERE ID_Tecnico_Ensamblador    = p_id_usuario
       OR ID_Tecnico_Comprobador    = p_id_usuario
       OR ID_Tecnico_Mantenimiento  = p_id_usuario;
END $$

-- ==============================================================
-- 5. MÓDULO COMPONENTE
-- ==============================================================

DROP PROCEDURE IF EXISTS sp_listar_componentes $$
CREATE PROCEDURE sp_listar_componentes(
    IN p_tipo   VARCHAR(20),
    IN p_limit  INT,
    IN p_offset INT
)
BEGIN
    IF p_tipo IS NULL OR p_tipo = '' THEN
        SELECT c.*, cu.ID_Usuario AS usuario_uso, cu.ID_Maquina AS maquina_uso,
               cu.fecha_asignacion, cu.fecha_liberacion
        FROM componente c
        LEFT JOIN componente_usuario cu ON c.ID_Componente = cu.ID_Componente
            AND cu.fecha_liberacion IS NULL
        GROUP BY c.ID_Componente
        LIMIT p_limit OFFSET p_offset;
    ELSE
        SELECT c.*, cu.ID_Usuario AS usuario_uso, cu.ID_Maquina AS maquina_uso,
               cu.fecha_asignacion, cu.fecha_liberacion
        FROM componente c
        LEFT JOIN componente_usuario cu ON c.ID_Componente = cu.ID_Componente
            AND cu.fecha_liberacion IS NULL
        WHERE c.tipo = p_tipo
        GROUP BY c.ID_Componente
        LIMIT p_limit OFFSET p_offset;
    END IF;
END $$

DROP PROCEDURE IF EXISTS sp_componentes_disponibles $$
CREATE PROCEDURE sp_componentes_disponibles(IN p_tipo VARCHAR(20))
BEGIN
    IF p_tipo IS NULL OR p_tipo = '' THEN
        SELECT c.*
        FROM componente c
        LEFT JOIN componente_usuario cu ON c.ID_Componente = cu.ID_Componente
            AND cu.fecha_liberacion IS NULL
        WHERE cu.ID_Componente IS NULL
        ORDER BY c.nombre ASC;
    ELSE
        SELECT c.*
        FROM componente c
        LEFT JOIN componente_usuario cu ON c.ID_Componente = cu.ID_Componente
            AND cu.fecha_liberacion IS NULL
        WHERE cu.ID_Componente IS NULL
          AND c.tipo = p_tipo
        ORDER BY c.nombre ASC;
    END IF;
END $$

DROP PROCEDURE IF EXISTS sp_componentes_en_uso_por_usuario $$
CREATE PROCEDURE sp_componentes_en_uso_por_usuario(
    IN p_id_usuario CHAR(36),
    IN p_id_maquina CHAR(36)
)
BEGIN
    IF p_id_maquina IS NULL OR p_id_maquina = '' THEN
        SELECT c.*, cu.fecha_asignacion, cu.ID_Maquina AS maquina_uso
        FROM componente_usuario cu
        INNER JOIN componente c ON cu.ID_Componente = c.ID_Componente
        WHERE cu.ID_Usuario = p_id_usuario
          AND cu.fecha_liberacion IS NULL;
    ELSE
        SELECT c.*, cu.fecha_asignacion, cu.ID_Maquina AS maquina_uso
        FROM componente_usuario cu
        INNER JOIN componente c ON cu.ID_Componente = c.ID_Componente
        WHERE cu.ID_Usuario = p_id_usuario
          AND cu.ID_Maquina = p_id_maquina
          AND cu.fecha_liberacion IS NULL;
    END IF;
END $$

DROP PROCEDURE IF EXISTS sp_asignar_componente $$
CREATE PROCEDURE sp_asignar_componente(
    IN p_id_componente CHAR(36),
    IN p_id_usuario    CHAR(36),
    IN p_id_maquina    CHAR(36),
    IN p_fecha         DATETIME
)
BEGIN
    DECLARE v_activo INT DEFAULT 0;

    SELECT COUNT(*) INTO v_activo
    FROM componente_usuario
    WHERE ID_Componente = p_id_componente AND fecha_liberacion IS NULL;

    IF v_activo = 0 THEN
        INSERT INTO componente_usuario (ID_Registro, ID_Componente, ID_Usuario, ID_Maquina, fecha_asignacion)
        VALUES (UUID(), p_id_componente, p_id_usuario, p_id_maquina, p_fecha);
    END IF;
END $$

DROP PROCEDURE IF EXISTS sp_liberar_componente $$
CREATE PROCEDURE sp_liberar_componente(
    IN p_id_componente CHAR(36),
    IN p_fecha         DATETIME
)
BEGIN
    UPDATE componente_usuario
    SET fecha_liberacion = p_fecha
    WHERE ID_Componente = p_id_componente
      AND fecha_liberacion IS NULL;
END $$

DROP PROCEDURE IF EXISTS sp_liberar_componentes_usuario $$
CREATE PROCEDURE sp_liberar_componentes_usuario(
    IN p_id_usuario CHAR(36),
    IN p_fecha      DATETIME
)
BEGIN
    UPDATE componente_usuario
    SET fecha_liberacion = p_fecha
    WHERE ID_Usuario = p_id_usuario
      AND fecha_liberacion IS NULL;
END $$

DROP PROCEDURE IF EXISTS sp_generar_numero_placa $$
CREATE PROCEDURE sp_generar_numero_placa(OUT p_placa VARCHAR(20))
BEGIN
    DECLARE v_anio   VARCHAR(2);
    DECLARE v_prefijo VARCHAR(6);
    DECLARE v_max     INT DEFAULT 0;

    SET v_anio    = DATE_FORMAT(NOW(), '%y');
    SET v_prefijo = CONCAT('PL', v_anio);

    SELECT COALESCE(MAX(CAST(SUBSTRING(nombre, 5) AS UNSIGNED)), 0)
    INTO v_max
    FROM componente
    WHERE nombre LIKE CONCAT(v_prefijo, '%') AND tipo = 'Logistico';

    SET p_placa = CONCAT(v_prefijo, LPAD(v_max + 1, 3, '0'));
END $$

DROP PROCEDURE IF EXISTS sp_contar_componentes_por_tipo $$
CREATE PROCEDURE sp_contar_componentes_por_tipo(
    IN  p_tipo  VARCHAR(20),
    OUT p_total INT
)
BEGIN
    IF p_tipo IS NULL OR p_tipo = '' THEN
        SELECT COUNT(*) INTO p_total FROM componente;
    ELSE
        SELECT COUNT(*) INTO p_total FROM componente WHERE tipo = p_tipo;
    END IF;
END $$

-- ==============================================================
-- 6. MÓDULO MONTAJE
-- ==============================================================

DROP PROCEDURE IF EXISTS sp_insertar_montaje $$
CREATE PROCEDURE sp_insertar_montaje(
    IN p_id_montaje    CHAR(36),
    IN p_id_maquina    CHAR(36),
    IN p_id_componente CHAR(36),
    IN p_id_tecnico    CHAR(36),
    IN p_detalle       TEXT,
    IN p_fecha         DATETIME
)
BEGIN
    INSERT INTO montaje (ID_Montaje, ID_Maquina, ID_Componente, ID_Tecnico, detalle, fecha)
    VALUES (p_id_montaje, p_id_maquina, p_id_componente, p_id_tecnico, p_detalle, p_fecha);
END $$

DROP PROCEDURE IF EXISTS sp_montajes_por_maquina $$
CREATE PROCEDURE sp_montajes_por_maquina(IN p_id_maquina CHAR(36))
BEGIN
    SELECT * FROM montaje WHERE ID_Maquina = p_id_maquina ORDER BY fecha DESC;
END $$

DROP PROCEDURE IF EXISTS sp_montajes_por_componente $$
CREATE PROCEDURE sp_montajes_por_componente(IN p_id_componente CHAR(36))
BEGIN
    SELECT * FROM montaje WHERE ID_Componente = p_id_componente ORDER BY fecha DESC;
END $$

DROP PROCEDURE IF EXISTS sp_montajes_por_tecnico $$
CREATE PROCEDURE sp_montajes_por_tecnico(IN p_id_tecnico CHAR(36))
BEGIN
    SELECT * FROM montaje WHERE ID_Tecnico = p_id_tecnico ORDER BY fecha DESC;
END $$

-- ==============================================================
-- 7. MÓDULO NOTIFICACIONES
-- ==============================================================

DROP PROCEDURE IF EXISTS sp_crear_notificacion_maquina $$
CREATE PROCEDURE sp_crear_notificacion_maquina(
    IN p_id             CHAR(36),
    IN p_remitente      CHAR(36),
    IN p_destinatario   CHAR(36),
    IN p_id_maquina     CHAR(36),
    IN p_tipo           VARCHAR(100),
    IN p_mensaje        TEXT,
    IN p_fecha          TIMESTAMP
)
BEGIN
    INSERT INTO NotificacionMaquinaRecreativa
        (ID_Notificacion, ID_Remitente, ID_Destinatario, ID_Maquina, Tipo, Mensaje, Fecha, Estado)
    VALUES
        (p_id, p_remitente, p_destinatario, p_id_maquina, p_tipo, p_mensaje, p_fecha, 'No leido');
END $$

DROP PROCEDURE IF EXISTS sp_notificaciones_maquina_por_destinatario $$
CREATE PROCEDURE sp_notificaciones_maquina_por_destinatario(IN p_id_destinatario CHAR(36))
BEGIN
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
END $$

DROP PROCEDURE IF EXISTS sp_marcar_leida_maquina $$
CREATE PROCEDURE sp_marcar_leida_maquina(IN p_id CHAR(36))
BEGIN
    UPDATE NotificacionMaquinaRecreativa
    SET Estado = 'Leido' WHERE ID_Notificacion = p_id;
END $$

DROP PROCEDURE IF EXISTS sp_contar_no_leidas_maquina $$
CREATE PROCEDURE sp_contar_no_leidas_maquina(
    IN  p_id_destinatario CHAR(36),
    OUT p_total INT
)
BEGIN
    SELECT COUNT(*) INTO p_total
    FROM NotificacionMaquinaRecreativa
    WHERE ID_Destinatario = p_id_destinatario AND Estado = 'No leido';
END $$

DROP PROCEDURE IF EXISTS sp_crear_notificacion_reporte $$
CREATE PROCEDURE sp_crear_notificacion_reporte(
    IN p_id         CHAR(36),
    IN p_id_reporte CHAR(36),
    IN p_id_usuario CHAR(36),
    IN p_mensaje    TEXT,
    IN p_fecha      DATETIME
)
BEGIN
    INSERT INTO notificaciones (ID_Notificaciones, ID_Reporte, ID_Usuario, mensaje, fecha_hora, leida)
    VALUES (p_id, p_id_reporte, p_id_usuario, p_mensaje, p_fecha, FALSE);
END $$

DROP PROCEDURE IF EXISTS sp_notificaciones_reporte_por_usuario $$
CREATE PROCEDURE sp_notificaciones_reporte_por_usuario(IN p_id_usuario CHAR(36))
BEGIN
    SELECT n.*, r.descripcion AS reporte_descripcion
    FROM notificaciones n
    LEFT JOIN reporte r ON n.ID_Reporte = r.ID_Reporte
    WHERE n.ID_Usuario = p_id_usuario
    ORDER BY n.fecha_hora DESC;
END $$

DROP PROCEDURE IF EXISTS sp_marcar_leida_reporte $$
CREATE PROCEDURE sp_marcar_leida_reporte(
    IN p_id_notificacion CHAR(36),
    IN p_id_usuario      CHAR(36)
)
BEGIN
    UPDATE notificaciones
    SET leida = 1
    WHERE ID_Notificaciones = p_id_notificacion AND ID_Usuario = p_id_usuario;
END $$

DROP PROCEDURE IF EXISTS sp_marcar_todas_leidas_reporte $$
CREATE PROCEDURE sp_marcar_todas_leidas_reporte(IN p_id_usuario CHAR(36))
BEGIN
    UPDATE notificaciones SET leida = 1 WHERE ID_Usuario = p_id_usuario;
END $$

DROP PROCEDURE IF EXISTS sp_contar_no_leidas_reporte $$
CREATE PROCEDURE sp_contar_no_leidas_reporte(
    IN  p_id_usuario CHAR(36),
    OUT p_total      INT
)
BEGIN
    SELECT COUNT(*) INTO p_total
    FROM notificaciones
    WHERE ID_Usuario = p_id_usuario AND leida = 0;
END $$

-- ==============================================================
-- 8. MÓDULO REPORTE / CHAT
-- ==============================================================

DROP PROCEDURE IF EXISTS sp_insertar_reporte $$
CREATE PROCEDURE sp_insertar_reporte(
    IN p_id             CHAR(36),
    IN p_id_emisor      CHAR(36),
    IN p_id_destinatario CHAR(36),
    IN p_descripcion    TEXT,
    IN p_fecha_hora     DATETIME,
    IN p_estado         VARCHAR(15)
)
BEGIN
    INSERT INTO reporte (ID_Reporte, ID_Usuario_Emisor, ID_Usuario_Destinatario, descripcion, fecha_hora, estado)
    VALUES (p_id, p_id_emisor, p_id_destinatario, p_descripcion, p_fecha_hora, p_estado)
    ON DUPLICATE KEY UPDATE
        estado = VALUES(estado),
        descripcion = VALUES(descripcion);
END $$

DROP PROCEDURE IF EXISTS sp_actualizar_estado_reporte $$
CREATE PROCEDURE sp_actualizar_estado_reporte(
    IN p_id     CHAR(36),
    IN p_estado VARCHAR(15)
)
BEGIN
    UPDATE reporte SET estado = p_estado WHERE ID_Reporte = p_id;
END $$

DROP PROCEDURE IF EXISTS sp_buscar_reporte_por_id $$
CREATE PROCEDURE sp_buscar_reporte_por_id(IN p_id CHAR(36))
BEGIN
    SELECT r.*,
           e.nombre AS emisor_nombre, e.apellido AS emisor_apellido, e.email AS emisor_email,
           d.nombre AS destinatario_nombre, d.apellido AS destinatario_apellido, d.email AS destinatario_email
    FROM reporte r
    JOIN usuario e ON r.ID_Usuario_Emisor = e.ID_Usuario
    LEFT JOIN usuario d ON r.ID_Usuario_Destinatario = d.ID_Usuario
    WHERE r.ID_Reporte = p_id
    LIMIT 1;
END $$

DROP PROCEDURE IF EXISTS sp_reportes_por_usuario $$
CREATE PROCEDURE sp_reportes_por_usuario(IN p_id_usuario CHAR(36))
BEGIN
    SELECT r.*,
           e.nombre AS emisor_nombre, e.apellido AS emisor_apellido, e.email AS emisor_email,
           d.nombre AS destinatario_nombre, d.apellido AS destinatario_apellido, d.email AS destinatario_email
    FROM reporte r
    JOIN usuario e ON r.ID_Usuario_Emisor = e.ID_Usuario
    LEFT JOIN usuario d ON r.ID_Usuario_Destinatario = d.ID_Usuario
    WHERE r.ID_Usuario_Emisor = p_id_usuario
       OR r.ID_Usuario_Destinatario = p_id_usuario
    ORDER BY r.fecha_hora DESC;
END $$

DROP PROCEDURE IF EXISTS sp_chat_entre_usuarios $$
CREATE PROCEDURE sp_chat_entre_usuarios(
    IN p_emisor      CHAR(36),
    IN p_destinatario CHAR(36)
)
BEGIN
    SELECT r.*,
           e.nombre AS emisor_nombre, e.apellido AS emisor_apellido, e.email AS emisor_email,
           d.nombre AS destinatario_nombre, d.apellido AS destinatario_apellido, d.email AS destinatario_email
    FROM reporte r
    JOIN usuario e ON r.ID_Usuario_Emisor = e.ID_Usuario
    LEFT JOIN usuario d ON r.ID_Usuario_Destinatario = d.ID_Usuario
    WHERE (r.ID_Usuario_Emisor = p_emisor AND r.ID_Usuario_Destinatario = p_destinatario)
       OR (r.ID_Usuario_Emisor = p_destinatario AND r.ID_Usuario_Destinatario = p_emisor)
    ORDER BY r.fecha_hora ASC;
END $$

DROP PROCEDURE IF EXISTS sp_usuarios_chat $$
CREATE PROCEDURE sp_usuarios_chat(IN p_id_usuario CHAR(36))
BEGIN
    SELECT DISTINCT u.* FROM usuario u
    WHERE u.ID_Usuario IN (
        SELECT DISTINCT ID_Usuario_Emisor      FROM reporte WHERE ID_Usuario_Destinatario = p_id_usuario
        UNION
        SELECT DISTINCT ID_Usuario_Destinatario FROM reporte WHERE ID_Usuario_Emisor       = p_id_usuario
    )
    AND u.ID_Usuario != p_id_usuario
    ORDER BY u.nombre ASC;
END $$

-- ==============================================================
-- 9. MÓDULO COMENTARIO
-- ==============================================================

DROP PROCEDURE IF EXISTS sp_insertar_comentario $$
CREATE PROCEDURE sp_insertar_comentario(
    IN p_id          CHAR(36),
    IN p_id_reporte  CHAR(36),
    IN p_id_emisor   CHAR(36),
    IN p_comentario  TEXT,
    IN p_fecha_hora  DATETIME
)
BEGIN
    INSERT INTO comentario (ID_Comentario, ID_Reporte, ID_Usuario_Emisor, comentario, fecha_hora, fecha_edicion, eliminado)
    VALUES (p_id, p_id_reporte, p_id_emisor, p_comentario, p_fecha_hora, NULL, FALSE);
END $$

DROP PROCEDURE IF EXISTS sp_buscar_comentario_por_id $$
CREATE PROCEDURE sp_buscar_comentario_por_id(IN p_id CHAR(36))
BEGIN
    SELECT * FROM comentario WHERE ID_Comentario = p_id LIMIT 1;
END $$

DROP PROCEDURE IF EXISTS sp_editar_comentario $$
CREATE PROCEDURE sp_editar_comentario(
    IN p_id          CHAR(36),
    IN p_comentario  TEXT,
    IN p_fecha       DATETIME
)
BEGIN
    UPDATE comentario
    SET comentario = p_comentario,
        fecha_edicion = p_fecha
    WHERE ID_Comentario = p_id;
END $$

DROP PROCEDURE IF EXISTS sp_eliminar_comentario $$
CREATE PROCEDURE sp_eliminar_comentario(IN p_id CHAR(36))
BEGIN
    UPDATE comentario SET eliminado = 1 WHERE ID_Comentario = p_id;
END $$

DROP PROCEDURE IF EXISTS sp_comentarios_por_reporte $$
CREATE PROCEDURE sp_comentarios_por_reporte(
    IN p_id_reporte CHAR(36),
    IN p_id_usuario CHAR(36)
)
BEGIN
    SELECT c.*,
           u.nombre AS nombre_emisor,
           u.apellido AS apellido_emisor,
           u.email AS email_emisor,
           u.tipo AS tipo_emisor,
           IF(u.ID_Usuario = p_id_usuario, 1, 0) AS es_propio
    FROM comentario c
    JOIN usuario u ON c.ID_Usuario_Emisor = u.ID_Usuario
    WHERE c.ID_Reporte = p_id_reporte
      AND c.eliminado = 0
    ORDER BY c.fecha_hora ASC;
END $$

DROP PROCEDURE IF EXISTS sp_eliminar_comentarios_reporte $$
CREATE PROCEDURE sp_eliminar_comentarios_reporte(IN p_id_reporte CHAR(36))
BEGIN
    DELETE FROM comentario WHERE ID_Reporte = p_id_reporte;
END $$

-- ==============================================================
-- 10. MÓDULO RECAUDACIÓN
-- ==============================================================

DROP PROCEDURE IF EXISTS sp_insertar_recaudacion $$
CREATE PROCEDURE sp_insertar_recaudacion(
    IN p_id                 CHAR(36),
    IN p_tipo_comercio      ENUM('Minorista','Mayorista'),
    IN p_id_maquina         CHAR(36),
    IN p_id_usuario         CHAR(36),
    IN p_monto_total        DECIMAL(10,2),
    IN p_monto_empresa      DECIMAL(10,2),
    IN p_monto_comercio     DECIMAL(10,2),
    IN p_porcentaje_comercio DECIMAL(5,2),
    IN p_fecha              DATETIME,
    IN p_detalle            TEXT
)
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
END $$

DROP PROCEDURE IF EXISTS sp_actualizar_recaudacion $$
CREATE PROCEDURE sp_actualizar_recaudacion(
    IN p_id                 CHAR(36),
    IN p_tipo_comercio      VARCHAR(20),
    IN p_id_maquina         CHAR(36),
    IN p_monto_total        DECIMAL(10,2),
    IN p_monto_empresa      DECIMAL(10,2),
    IN p_monto_comercio     DECIMAL(10,2),
    IN p_porcentaje_comercio DECIMAL(5,2),
    IN p_fecha              DATETIME,
    IN p_detalle            TEXT
)
BEGIN
    UPDATE recaudaciones
    SET Tipo_Comercio       = p_tipo_comercio,
        ID_Maquina          = p_id_maquina,
        Monto_Total         = p_monto_total,
        Monto_Empresa       = p_monto_empresa,
        Monto_Comercio      = p_monto_comercio,
        Porcentaje_Comercio = p_porcentaje_comercio,
        fecha               = p_fecha,
        detalle             = p_detalle
    WHERE ID_Recaudacion = p_id;
END $$

DROP PROCEDURE IF EXISTS sp_eliminar_recaudacion $$
CREATE PROCEDURE sp_eliminar_recaudacion(IN p_id CHAR(36))
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    DELETE d FROM informe_detalle d
    INNER JOIN informes_recaudacion i ON d.ID_Informe = i.ID_Informe
    WHERE i.ID_Recaudacion = p_id;

    DELETE FROM informes_recaudacion WHERE ID_Recaudacion = p_id;

    DELETE FROM recaudaciones WHERE ID_Recaudacion = p_id;

    COMMIT;
END $$

DROP PROCEDURE IF EXISTS sp_buscar_recaudacion_por_id $$
CREATE PROCEDURE sp_buscar_recaudacion_por_id(IN p_id CHAR(36))
BEGIN
    SELECT r.*,
           m.Nombre_Maquina,
           c.Nombre AS Nombre_Comercio,
           u.nombre AS nombre_usuario,
           u.apellido AS apellido_usuario
    FROM recaudaciones r
    INNER JOIN MaquinaRecreativa m ON r.ID_Maquina = m.ID_Maquina
    INNER JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    INNER JOIN usuario u ON r.ID_Usuario = u.ID_Usuario
    WHERE r.ID_Recaudacion = p_id
    LIMIT 1;
END $$

DROP PROCEDURE IF EXISTS sp_listar_recaudaciones $$
CREATE PROCEDURE sp_listar_recaudaciones(
    IN p_fecha_inicio  DATE,
    IN p_fecha_fin     DATE,
    IN p_id_maquina    CHAR(36),
    IN p_tipo_comercio VARCHAR(20),
    IN p_limit         INT,
    IN p_offset        INT
)
BEGIN
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
      AND (p_fecha_fin    IS NULL OR DATE(r.fecha) <= p_fecha_fin)
      AND (p_id_maquina   IS NULL OR r.ID_Maquina  = p_id_maquina)
      AND (p_tipo_comercio IS NULL OR r.Tipo_Comercio = p_tipo_comercio)
    ORDER BY r.fecha DESC
    LIMIT p_limit OFFSET p_offset;
END $$

DROP PROCEDURE IF EXISTS sp_resumen_recaudaciones $$
CREATE PROCEDURE sp_resumen_recaudaciones(IN p_limit INT)
BEGIN
    IF p_limit IS NULL THEN
        SELECT Tipo_Comercio,
               COUNT(*)          AS TotalRecaudaciones,
               SUM(Monto_Total)  AS TotalRecaudado,
               SUM(Monto_Empresa) AS TotalEmpresa,
               SUM(Monto_Comercio) AS TotalComercio
        FROM recaudaciones
        GROUP BY Tipo_Comercio
        ORDER BY TotalRecaudado DESC;
    ELSE
        SELECT Tipo_Comercio,
               COUNT(*)          AS TotalRecaudaciones,
               SUM(Monto_Total)  AS TotalRecaudado,
               SUM(Monto_Empresa) AS TotalEmpresa,
               SUM(Monto_Comercio) AS TotalComercio
        FROM recaudaciones
        GROUP BY Tipo_Comercio
        ORDER BY TotalRecaudado DESC
        LIMIT p_limit;
    END IF;
END $$

DROP PROCEDURE IF EXISTS sp_maquinas_operativas_recaudacion $$
CREATE PROCEDURE sp_maquinas_operativas_recaudacion()
BEGIN
    SELECT m.*, c.Nombre AS NombreComercio, c.Direccion AS DireccionComercio,
           c.Telefono AS TelefonoComercio, c.Tipo AS TipoComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.Etapa = 'Recaudacion' AND m.Estado = 'Operativa'
    ORDER BY m.Fecha_Registro DESC;
END $$

DROP PROCEDURE IF EXISTS sp_guardar_informe_recaudacion $$
CREATE PROCEDURE sp_guardar_informe_recaudacion(
    IN p_id                 CHAR(36),
    IN p_id_recaudacion     CHAR(36),
    IN p_ci_usuario         VARCHAR(100),
    IN p_nombre_maquina     VARCHAR(100),
    IN p_id_comercio        CHAR(36),
    IN p_nombre_comercio    VARCHAR(100),
    IN p_direccion_comercio TEXT,
    IN p_telefono_comercio  VARCHAR(15),
    IN p_pago_ensamblador   DECIMAL(10,2),
    IN p_pago_comprobador   DECIMAL(10,2),
    IN p_pago_mantenimiento DECIMAL(10,2),
    IN p_empresa_nombre     VARCHAR(100),
    IN p_empresa_descripcion VARCHAR(255)
)
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
    ON DUPLICATE KEY UPDATE
        CI_Usuario          = VALUES(CI_Usuario),
        Nombre_Maquina      = VALUES(Nombre_Maquina),
        Nombre_Comercio     = VALUES(Nombre_Comercio),
        Pago_Ensamblador    = VALUES(Pago_Ensamblador),
        Pago_Comprobador    = VALUES(Pago_Comprobador),
        Pago_Mantenimiento  = VALUES(Pago_Mantenimiento);
END $$

DROP PROCEDURE IF EXISTS sp_guardar_detalle_informe $$
CREATE PROCEDURE sp_guardar_detalle_informe(
    IN p_id_detalle    CHAR(36),
    IN p_id_informe    CHAR(36),
    IN p_id_componente CHAR(36)
)
BEGIN
    INSERT INTO informe_detalle (ID_Informe_Detalle, ID_Informe, ID_Componente)
    VALUES (p_id_detalle, p_id_informe, p_id_componente);
END $$

DROP PROCEDURE IF EXISTS sp_obtener_informe_por_recaudacion $$
CREATE PROCEDURE sp_obtener_informe_por_recaudacion(IN p_id_recaudacion CHAR(36))
BEGIN
    SELECT * FROM informes_recaudacion WHERE ID_Recaudacion = p_id_recaudacion LIMIT 1;
END $$

DROP PROCEDURE IF EXISTS sp_detalles_informe $$
CREATE PROCEDURE sp_detalles_informe(IN p_id_informe CHAR(36))
BEGIN
    SELECT c.*
    FROM informe_detalle id
    JOIN componente c ON id.ID_Componente = c.ID_Componente
    WHERE id.ID_Informe = p_id_informe;
END $$

-- ==============================================================
-- 11. MÓDULO DISTRIBUCIÓN
-- ==============================================================

DROP PROCEDURE IF EXISTS sp_guardar_informe_distribucion $$
CREATE PROCEDURE sp_guardar_informe_distribucion(
    IN p_id                 CHAR(36),
    IN p_id_maquina         CHAR(36),
    IN p_id_comprobador     CHAR(36),
    IN p_id_comercio        CHAR(36),
    IN p_fecha_alta         DATETIME,
    IN p_estado             VARCHAR(20)
)
BEGIN
    INSERT INTO informe_distribucion
        (ID_Distribucion, ID_Maquina, ID_Usuario_Comprobador, ID_Comercio, fecha_alta, estado)
    VALUES
        (p_id, p_id_maquina, p_id_comprobador, p_id_comercio, p_fecha_alta, p_estado)
    ON DUPLICATE KEY UPDATE
        estado = VALUES(estado),
        fecha_baja = IF(VALUES(estado) = 'Retirada', NOW(), fecha_baja);
END $$

DROP PROCEDURE IF EXISTS sp_actualizar_estado_distribucion $$
CREATE PROCEDURE sp_actualizar_estado_distribucion(
    IN p_id_maquina CHAR(36),
    IN p_estado     VARCHAR(20)
)
BEGIN
    UPDATE informe_distribucion
    SET estado     = p_estado,
        fecha_baja = IF(p_estado = 'Retirada', NOW(), fecha_baja)
    WHERE ID_Maquina = p_id_maquina;
END $$

DROP PROCEDURE IF EXISTS sp_buscar_distribucion_por_maquina $$
CREATE PROCEDURE sp_buscar_distribucion_por_maquina(IN p_id_maquina CHAR(36))
BEGIN
    SELECT * FROM informe_distribucion WHERE ID_Maquina = p_id_maquina LIMIT 1;
END $$

DROP PROCEDURE IF EXISTS sp_listar_distribuciones $$
CREATE PROCEDURE sp_listar_distribuciones(
    IN p_estado      VARCHAR(20),
    IN p_id_comercio CHAR(36),
    IN p_id_maquina  CHAR(36),
    IN p_fecha_inicio DATE,
    IN p_fecha_fin    DATE,
    IN p_limit        INT,
    IN p_offset       INT
)
BEGIN
    SELECT id.*, m.Nombre_Maquina,
           CONCAT(u.nombre, ' ', u.apellido) AS Nombre_Tecnico,
           c.Nombre AS Nombre_Comercio,
           c.Direccion AS Direccion_Comercio,
           c.Telefono  AS Telefono_Comercio,
           c.Tipo      AS Tipo_Comercio
    FROM informe_distribucion id
    INNER JOIN MaquinaRecreativa m ON id.ID_Maquina = m.ID_Maquina
    INNER JOIN usuario u ON id.ID_Usuario_Comprobador = u.ID_Usuario
    INNER JOIN Comercio c ON id.ID_Comercio = c.ID_Comercio
    WHERE id.fecha_alta = (
        SELECT MAX(id2.fecha_alta)
        FROM informe_distribucion id2
        WHERE id2.ID_Maquina = id.ID_Maquina
    )
    AND (p_estado IS NULL      OR id.estado      = p_estado)
    AND (p_id_comercio IS NULL OR id.ID_Comercio = p_id_comercio)
    AND (p_id_maquina  IS NULL OR id.ID_Maquina  = p_id_maquina)
    AND (p_fecha_inicio IS NULL OR DATE(id.fecha_alta) >= p_fecha_inicio)
    AND (p_fecha_fin    IS NULL OR DATE(id.fecha_alta) <= p_fecha_fin)
    ORDER BY id.fecha_alta DESC
    LIMIT p_limit OFFSET p_offset;
END $$

-- ==============================================================
-- 12. MÓDULO HISTORIAL DE MÁQUINAS
-- ==============================================================

DROP PROCEDURE IF EXISTS sp_insertar_historial_maquina $$
CREATE PROCEDURE sp_insertar_historial_maquina(
    IN p_id_maquina      CHAR(36),
    IN p_id_usuario      CHAR(36),
    IN p_tipo_usuario    ENUM('Tecnico','Logistica','Administrador'),
    IN p_accion          VARCHAR(100),
    IN p_descripcion     TEXT,
    IN p_estado_anterior VARCHAR(50),
    IN p_estado_nuevo    VARCHAR(50),
    IN p_etapa_anterior  VARCHAR(50),
    IN p_etapa_nueva     VARCHAR(50),
    IN p_ip_address      VARCHAR(45),
    IN p_detalles_json   JSON
)
BEGIN
    INSERT INTO historial_maquinas (
        ID_Maquina, ID_Usuario, tipo_usuario, accion, descripcion,
        estado_anterior, estado_nuevo, etapa_anterior, etapa_nueva,
        ip_address, detalles_adicionales, fecha_hora
    ) VALUES (
        p_id_maquina, p_id_usuario, p_tipo_usuario, p_accion, p_descripcion,
        p_estado_anterior, p_estado_nuevo, p_etapa_anterior, p_etapa_nueva,
        p_ip_address, p_detalles_json, NOW()
    );
END $$

DROP PROCEDURE IF EXISTS sp_historial_por_maquina $$
CREATE PROCEDURE sp_historial_por_maquina(
    IN p_id_maquina CHAR(36),
    IN p_limit      INT,
    IN p_offset     INT
)
BEGIN
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
END $$

DROP PROCEDURE IF EXISTS sp_historial_por_usuario $$
CREATE PROCEDURE sp_historial_por_usuario(
    IN p_id_usuario CHAR(36),
    IN p_limit      INT,
    IN p_offset     INT
)
BEGIN
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
END $$

DROP PROCEDURE IF EXISTS sp_historial_general $$
CREATE PROCEDURE sp_historial_general(
    IN p_id_maquina   CHAR(36),
    IN p_id_usuario   CHAR(36),
    IN p_tipo_usuario VARCHAR(20),
    IN p_accion       VARCHAR(100),
    IN p_fecha_inicio DATE,
    IN p_fecha_fin    DATE,
    IN p_limit        INT,
    IN p_offset       INT
)
BEGIN
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
    WHERE (p_id_maquina IS NULL OR h.ID_Maquina = p_id_maquina)
      AND (p_id_usuario IS NULL OR h.ID_Usuario = p_id_usuario)
      AND (p_tipo_usuario IS NULL OR h.tipo_usuario = p_tipo_usuario)
      AND (p_accion IS NULL OR h.accion LIKE CONCAT('%', p_accion, '%'))
      AND (p_fecha_inicio IS NULL OR DATE(h.fecha_hora) >= p_fecha_inicio)
      AND (p_fecha_fin IS NULL OR DATE(h.fecha_hora) <= p_fecha_fin)
    ORDER BY h.fecha_hora DESC
    LIMIT p_limit OFFSET p_offset;
END $$

DROP PROCEDURE IF EXISTS sp_contar_historial $$
CREATE PROCEDURE sp_contar_historial(
    IN  p_id_maquina   CHAR(36),
    IN  p_id_usuario   CHAR(36),
    IN  p_tipo_usuario VARCHAR(20),
    IN  p_accion       VARCHAR(100),
    IN  p_fecha_inicio DATE,
    IN  p_fecha_fin    DATE,
    OUT p_total        INT
)
BEGIN
    SELECT COUNT(*) INTO p_total
    FROM historial_maquinas h
    WHERE (p_id_maquina   IS NULL OR h.ID_Maquina    = p_id_maquina)
      AND (p_id_usuario   IS NULL OR h.ID_Usuario    = p_id_usuario)
      AND (p_tipo_usuario IS NULL OR h.tipo_usuario  = p_tipo_usuario)
      AND (p_accion       IS NULL OR h.accion        LIKE CONCAT('%', p_accion, '%'))
      AND (p_fecha_inicio IS NULL OR DATE(h.fecha_hora) >= p_fecha_inicio)
      AND (p_fecha_fin    IS NULL OR DATE(h.fecha_hora) <= p_fecha_fin);
END $$

DROP PROCEDURE IF EXISTS sp_resumen_historial_reciente $$
CREATE PROCEDURE sp_resumen_historial_reciente(IN p_limite INT)
BEGIN
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
END $$

-- ==============================================================
-- TRIGGERS
-- ==============================================================

DROP TRIGGER IF EXISTS after_maquina_distribucion $$
CREATE TRIGGER after_maquina_distribucion
AFTER UPDATE ON MaquinaRecreativa
FOR EACH ROW
BEGIN
    IF NEW.Etapa = 'Distribucion' AND NEW.Estado = 'Distribuyendose' AND 
       (OLD.Etapa != 'Distribucion' OR OLD.Estado != 'Distribuyendose') THEN
        
        SET @existe_informe = (SELECT COUNT(*) FROM informe_distribucion 
                              WHERE ID_Maquina = NEW.ID_Maquina);
        
        IF @existe_informe = 0 THEN
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
            UPDATE informe_distribucion 
            SET estado = 'Distribuyendose',
                fecha_alta = CURRENT_TIMESTAMP,
                fecha_baja = NULL
            WHERE ID_Maquina = NEW.ID_Maquina;
        END IF;
    END IF;
END $$

DROP TRIGGER IF EXISTS after_maquina_estado_change $$
CREATE TRIGGER after_maquina_estado_change
AFTER UPDATE ON MaquinaRecreativa
FOR EACH ROW
BEGIN
    IF NEW.Estado != OLD.Estado THEN
        IF NEW.Estado IN ('Operativa', 'No operativa', 'Retirada') THEN
            UPDATE informe_distribucion 
            SET estado = NEW.Estado,
                fecha_baja = CASE WHEN NEW.Estado = 'Retirada' THEN NOW() ELSE NULL END
            WHERE ID_Maquina = NEW.ID_Maquina;
        END IF;
    END IF;
END $$

DELIMITER ;