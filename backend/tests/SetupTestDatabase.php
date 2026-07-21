<?php
/**
 * Script para configurar la base de datos de pruebas
 * 
 * Este script se ejecuta después de que TestDatabase cree las tablas,
 * para crear los procedimientos almacenados y otorgar permisos.
 * 
 * @package maquinas_recreativas\Tests
 */

namespace maquinas_recreativas\Tests;

use PDO;
use PDOException;

class SetupTestDatabase
{
    private PDO $conn;
    private string $dbName;

    public function __construct(PDO $connection, string $dbName)
    {
        $this->conn = $connection;
        $this->dbName = $dbName;
    }

    /**
     * Ejecuta toda la configuración
     */
    public function run(): void
    {
        echo "Configurando base de datos de pruebas...\n";
        
        // 1. Asegurar que estamos en la base de datos correcta
        $this->selectDatabase();
        
        // 2. Crear procedimientos almacenados
        $this->createStoredProcedures();
        // 3. Crear triggers
        $this->createTriggers();
       
        
        echo "Configuración completada.\n";
    }

    /**
     * Selecciona la base de datos de pruebas
     */
    private function selectDatabase(): void
    {
        try {
            $this->conn->exec("USE `{$this->dbName}`");
            echo "   Base de datos seleccionada: {$this->dbName}\n";
        } catch (PDOException $e) {
            throw new \RuntimeException("Error al seleccionar la base de datos: " . $e->getMessage());
        }
    }

    /**
     * Crea todos los procedimientos almacenados
     */
    private function createStoredProcedures(): void
    {
        echo "   Creando procedimientos almacenados...\n";
        
        $procedures = $this->getStoredProceduresSQL();
        $successCount = 0;
        $errorCount = 0;

        foreach ($procedures as $name => $sql) {
            try {
                // Eliminar el procedimiento si existe
                $this->conn->exec("DROP PROCEDURE IF EXISTS `{$name}`");
                
                // Crear el procedimiento
                $this->conn->exec($sql);
                $successCount++;
                echo "     Creado: {$name}\n";
            } catch (PDOException $e) {
                $errorCount++;
                echo "     Error en {$name}: " . $e->getMessage() . "\n";
            }
        }

        echo "   Procedimientos: {$successCount} creados, {$errorCount} errores\n";
    }
 /**
     * Crea los triggers
     */
    private function createTriggers(): void
    {
        echo "  🔄 Creando triggers...\n";
        
        $triggers = $this->getTriggersSQL();
        $successCount = 0;
        $errorCount = 0;

        foreach ($triggers as $name => $sql) {
            try {
                // Eliminar el trigger si existe
                $this->conn->exec("DROP TRIGGER IF EXISTS `{$name}`");
                
                // Crear el trigger
                $this->conn->exec($sql);
                $successCount++;
                echo "     Creado: {$name}\n";
            } catch (PDOException $e) {
                $errorCount++;
                echo "     Error en {$name}: " . $e->getMessage() . "\n";
            }
        }

        echo "   Triggers: {$successCount} creados, {$errorCount} errores\n";
    }
private function getTriggersSQL(): array
    {
        return [
            // Trigger 1: Crear/Actualizar informe de distribución cuando una máquina cambia a etapa Distribucion
            'after_maquina_distribucion' => "
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
END",

            // Trigger 2: Actualizar estado del informe de distribución cuando cambia el estado de la máquina
            'after_maquina_estado_change' => "
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
END"
        ];
    }

    /**
     * Retorna todos los procedimientos almacenados
     */
    private function getStoredProceduresSQL(): array
    {
        return [
            // ==============================================================
            // 1. MÓDULO USUARIO
            // ==============================================================
            
            'sp_buscar_usuario_por_username' => "
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
END",

            'sp_buscar_usuario_por_email' => "
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
END",

            'sp_buscar_usuario_por_id' => "
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
END",

            'sp_insertar_usuario' => "
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
END",

            'sp_actualizar_usuario' => "
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
END",

            'sp_cambiar_estado_usuario' => "
CREATE PROCEDURE sp_cambiar_estado_usuario(
    IN p_id     CHAR(36),
    IN p_estado ENUM('Pendiente de asignacion','Activo','Inhabilitado')
)
BEGIN
    UPDATE usuario SET estado = p_estado WHERE ID_Usuario = p_id;
END",

            'sp_cambiar_contrasena_usuario' => "
CREATE PROCEDURE sp_cambiar_contrasena_usuario(
    IN p_id         CHAR(36),
    IN p_contrasena VARCHAR(255)
)
BEGIN
    UPDATE usuario SET contrasena = p_contrasena WHERE ID_Usuario = p_id;
END",

            'sp_actualizar_username' => "
CREATE PROCEDURE sp_actualizar_username(
    IN p_id       CHAR(36),
    IN p_username VARCHAR(25)
)
BEGIN
    UPDATE usuario SET usuario_asignado = p_username WHERE ID_Usuario = p_id;
END",

            'sp_eliminar_usuario' => "
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
END",

            'sp_listar_usuarios' => "
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
END",

            'sp_listar_usuarios_por_tipo' => "
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
END",

            'sp_existe_email' => "
CREATE PROCEDURE sp_existe_email(IN p_email VARCHAR(255), OUT p_existe TINYINT)
BEGIN
    SELECT COUNT(*) INTO p_existe FROM usuario WHERE email = p_email;
END",

            'sp_existe_ci' => "
CREATE PROCEDURE sp_existe_ci(IN p_ci VARCHAR(100), OUT p_existe TINYINT)
BEGIN
    SELECT COUNT(*) INTO p_existe FROM usuario WHERE ci = p_ci;
END",

            'sp_existe_username' => "
CREATE PROCEDURE sp_existe_username(IN p_username VARCHAR(25), OUT p_existe TINYINT)
BEGIN
    SELECT COUNT(*) INTO p_existe FROM usuario WHERE usuario_asignado = p_username;
END",

            'sp_existe_username_excluyendo_id' => "
CREATE PROCEDURE sp_existe_username_excluyendo_id(
    IN  p_username VARCHAR(25),
    IN  p_id       CHAR(36),
    OUT p_existe   TINYINT
)
BEGIN
    SELECT COUNT(*) INTO p_existe
    FROM usuario
    WHERE usuario_asignado = p_username AND ID_Usuario != p_id;
END",

            'sp_estadisticas_usuarios' => "
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
END",

            'sp_registrar_actividad' => "
CREATE PROCEDURE sp_registrar_actividad(
    IN p_id_usuario  CHAR(36),
    IN p_descripcion TEXT
)
BEGIN
    INSERT INTO historial_actividades (ID_Usuario, descripcion, fecha_registro)
    VALUES (p_id_usuario, p_descripcion, NOW());
END",

            'sp_obtener_historial_actividades' => "
CREATE PROCEDURE sp_obtener_historial_actividades(
    IN p_id_usuario CHAR(36),
    IN p_limite     INT
)
BEGIN
    SELECT * FROM historial_actividades
    WHERE ID_Usuario = p_id_usuario
    ORDER BY fecha_registro DESC
    LIMIT p_limite;
END",

            'sp_registrar_logout' => "
CREATE PROCEDURE sp_registrar_logout(IN p_id_usuario CHAR(36))
BEGIN
    UPDATE inicio_sesion
    SET fecha_ultima_sesion = NOW()
    WHERE ID_Usuario = p_id_usuario
      AND fecha_ultima_sesion IS NULL
    ORDER BY fecha_inicio DESC
    LIMIT 1;
END",

            // ==============================================================
            // 2. MÓDULO TÉCNICO
            // ==============================================================

            'sp_insertar_tecnico' => "
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
END",

            'sp_incrementar_actividades_tecnico' => "
CREATE PROCEDURE sp_incrementar_actividades_tecnico(IN p_id CHAR(36))
BEGIN
    UPDATE Tecnico
    SET Cantidad_Actividades = Cantidad_Actividades + 1
    WHERE ID_Tecnico = p_id;
END",

            'sp_tecnicos_por_especialidad' => "
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
END",

            'sp_tecnicos_disponibles_por_especialidad' => "
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
END",

            // ==============================================================
            // 3. MÓDULO COMERCIO
            // ==============================================================

            'sp_insertar_comercio' => "
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
END",

            'sp_actualizar_comercio' => "
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
END",

            'sp_eliminar_comercio' => "
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
END",

            'sp_buscar_comercio_por_id' => "
CREATE PROCEDURE sp_buscar_comercio_por_id(IN p_id CHAR(36))
BEGIN
    SELECT * FROM Comercio WHERE ID_Comercio = p_id LIMIT 1;
END",

            'sp_buscar_comercio_por_nombre' => "
CREATE PROCEDURE sp_buscar_comercio_por_nombre(IN p_nombre VARCHAR(100))
BEGIN
    SELECT * FROM Comercio WHERE Nombre = p_nombre LIMIT 1;
END",

            'sp_listar_comercios' => "
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
END",

            // ==============================================================
            // 4. MÓDULO MÁQUINA RECREATIVA
            // ==============================================================
            'sp_listar_recaudaciones' => "
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
            END",
            'sp_actualizar_estado_distribucion' => "
CREATE PROCEDURE sp_actualizar_estado_distribucion(
    IN p_id_maquina CHAR(36),
    IN p_estado     VARCHAR(20)
)
BEGIN
    UPDATE informe_distribucion
    SET estado     = p_estado,
        fecha_baja = IF(p_estado = 'Retirada', NOW(), fecha_baja)
    WHERE ID_Maquina = p_id_maquina;
END",

            'sp_actualizar_maquina' => "
CREATE PROCEDURE sp_actualizar_maquina(
    IN p_id        CHAR(36),
    IN p_nombre    VARCHAR(100),
    IN p_tipo      VARCHAR(50),
    IN p_id_comercio CHAR(36),
    IN p_estado    VARCHAR(50),
    IN p_etapa     VARCHAR(50)
)
BEGIN
    UPDATE MaquinaRecreativa
    SET Nombre_Maquina = p_nombre,
        Tipo           = p_tipo,
        ID_Comercio    = p_id_comercio,
        Estado         = COALESCE(p_estado, Estado),
        Etapa          = COALESCE(p_etapa, Etapa)
    WHERE ID_Maquina = p_id;
END",

            'sp_insertar_maquina' => "
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
END",

            'sp_actualizar_estado_maquina' => "
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
END",

            'sp_buscar_maquina_por_id' => "
CREATE PROCEDURE sp_buscar_maquina_por_id(IN p_id CHAR(36))
BEGIN
    SELECT m.*, c.Nombre AS NombreComercio, c.Direccion AS DireccionComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.ID_Maquina = p_id
    LIMIT 1;
END",

            'sp_maquinas_por_tecnico_ensamblador' => "
CREATE PROCEDURE sp_maquinas_por_tecnico_ensamblador(IN p_id_tecnico CHAR(36))
BEGIN
    SELECT m.*, c.Nombre AS NombreComercio, c.Direccion AS DireccionComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.ID_Tecnico_Ensamblador = p_id_tecnico
    ORDER BY m.Fecha_Registro DESC;
END",

            'sp_maquinas_por_tecnico_comprobador' => "
CREATE PROCEDURE sp_maquinas_por_tecnico_comprobador(IN p_id_tecnico CHAR(36))
BEGIN
    SELECT m.*, c.Nombre AS NombreComercio, c.Direccion AS DireccionComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.ID_Tecnico_Comprobador = p_id_tecnico
      AND m.Estado = 'Comprobandose'
    ORDER BY m.Fecha_Registro DESC;
END",

            'sp_maquinas_por_estado' => "
CREATE PROCEDURE sp_maquinas_por_estado(IN p_estado VARCHAR(50))
BEGIN
    SELECT m.*, c.Nombre AS NombreComercio, c.Direccion AS DireccionComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.Estado = p_estado
    ORDER BY m.Fecha_Registro DESC;
END",

            'sp_maquinas_por_etapa' => "
CREATE PROCEDURE sp_maquinas_por_etapa(IN p_etapa VARCHAR(50))
BEGIN
    SELECT m.*, c.Nombre AS NombreComercio, c.Direccion AS DireccionComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.Etapa = p_etapa
    ORDER BY m.Fecha_Registro DESC;
END",

            'sp_todas_las_maquinas' => "
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
END",

            // ==============================================================
            // 5. MÓDULO COMPONENTE
            // ==============================================================

            'sp_listar_componentes' => "
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
END",

            'sp_componentes_disponibles' => "
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
END",

            'sp_generar_numero_placa' => "
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
END",

            // ==============================================================
            // 6. MÓDULO MONTAJE
            // ==============================================================

            'sp_insertar_montaje' => "
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
END",

            'sp_montajes_por_maquina' => "
CREATE PROCEDURE sp_montajes_por_maquina(IN p_id_maquina CHAR(36))
BEGIN
    SELECT * FROM montaje WHERE ID_Maquina = p_id_maquina ORDER BY fecha DESC;
END",

            // ==============================================================
            // 7. MÓDULO NOTIFICACIONES
            // ==============================================================


            'sp_marcar_leida_reporte' => "
CREATE PROCEDURE sp_marcar_leida_reporte(
    IN p_id_notificacion CHAR(36),
    IN p_id_usuario      CHAR(36)
)
BEGIN
    UPDATE notificaciones
    SET leida = 1
    WHERE ID_Notificaciones = p_id_notificacion AND ID_Usuario = p_id_usuario;
END",
            'sp_contar_no_leidas_reporte' => "
 CREATE PROCEDURE sp_contar_no_leidas_reporte(
    IN  p_id_usuario CHAR(36),
    OUT p_total      INT
)
BEGIN
    SELECT COUNT(*) INTO p_total
    FROM notificaciones
    WHERE ID_Usuario = p_id_usuario AND leida = 0;
END",
            'sp_notificaciones_reporte_por_usuario' => "
CREATE PROCEDURE sp_notificaciones_reporte_por_usuario(IN p_id_usuario CHAR(36))
BEGIN
    SELECT n.*, r.descripcion AS reporte_descripcion
    FROM notificaciones n
    LEFT JOIN reporte r ON n.ID_Reporte = r.ID_Reporte
    WHERE n.ID_Usuario = p_id_usuario
    ORDER BY n.fecha_hora DESC;
END",

            'sp_crear_notificacion_maquina' => "
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
END",

            'sp_notificaciones_maquina_por_destinatario' => "
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
END",

            'sp_marcar_leida_maquina' => "
CREATE PROCEDURE sp_marcar_leida_maquina(IN p_id CHAR(36))
BEGIN
    UPDATE NotificacionMaquinaRecreativa
    SET Estado = 'Leido' WHERE ID_Notificacion = p_id;
END",

            'sp_contar_no_leidas_maquina' => "
CREATE PROCEDURE sp_contar_no_leidas_maquina(
    IN  p_id_destinatario CHAR(36),
    OUT p_total INT
)
BEGIN
    SELECT COUNT(*) INTO p_total
    FROM NotificacionMaquinaRecreativa
    WHERE ID_Destinatario = p_id_destinatario AND Estado = 'No leido';
END",

            // ==============================================================
            // 8. MÓDULO REPORTE / CHAT
            // ==============================================================

            'sp_insertar_reporte' => "
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
END",

            'sp_actualizar_estado_reporte' => "
CREATE PROCEDURE sp_actualizar_estado_reporte(
    IN p_id     CHAR(36),
    IN p_estado VARCHAR(15)
)
BEGIN
    UPDATE reporte SET estado = p_estado WHERE ID_Reporte = p_id;
END",

            'sp_buscar_reporte_por_id' => "
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
END",

            'sp_reportes_por_usuario' => "
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
END",

            'sp_chat_entre_usuarios' => "
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
END",

            // ==============================================================
            // 9. MÓDULO COMENTARIO
            // ==============================================================

            'sp_insertar_comentario' => "
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
END",
'sp_buscar_comentario_por_id' => "
CREATE PROCEDURE sp_buscar_comentario_por_id(IN p_id CHAR(36))
BEGIN
    SELECT * FROM comentario WHERE ID_Comentario = p_id LIMIT 1;
END",
            'sp_editar_comentario' => "
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
END",

            'sp_eliminar_comentario' => "
CREATE PROCEDURE sp_eliminar_comentario(IN p_id CHAR(36))
BEGIN
    UPDATE comentario SET eliminado = 1 WHERE ID_Comentario = p_id;
END",

            'sp_comentarios_por_reporte' => "
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
END",

            // ==============================================================
            // 10. MÓDULO RECAUDACIÓN
            // ==============================================================

            'sp_insertar_recaudacion' => "
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
END",

            'sp_buscar_recaudacion_por_id' => "
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
END",

            'sp_maquinas_operativas_recaudacion' => "
CREATE PROCEDURE sp_maquinas_operativas_recaudacion()
BEGIN
    SELECT m.*, c.Nombre AS NombreComercio, c.Direccion AS DireccionComercio,
           c.Telefono AS TelefonoComercio, c.Tipo AS TipoComercio
    FROM MaquinaRecreativa m
    LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
    WHERE m.Etapa = 'Recaudacion' AND m.Estado = 'Operativa'
    ORDER BY m.Fecha_Registro DESC;
END",

            // ==============================================================
            // 11. MÓDULO DISTRIBUCIÓN
            // ==============================================================

            'sp_guardar_informe_distribucion' => "
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
END",

            'sp_buscar_distribucion_por_maquina' => "
CREATE PROCEDURE sp_buscar_distribucion_por_maquina(IN p_id_maquina CHAR(36))
BEGIN
    SELECT * FROM informe_distribucion WHERE ID_Maquina = p_id_maquina LIMIT 1;
END",

            // ==============================================================
            // 12. MÓDULO HISTORIAL
            // ==============================================================

            'sp_insertar_historial_maquina' => "
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
END",

            'sp_historial_por_maquina' => "
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
END",

            'sp_historial_general' => "
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
END",
     'sp_asignar_componente' => "
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
END",

            'sp_crear_notificacion_reporte' => "
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
END"
        ];
    }
}