-- =============================================
-- SCRIPT PARA INSERTAR USUARIOS INICIALES EN POSTGRESQL/SUPABASE
-- =============================================

-- 1. PRIMERO, CREAMOS LA FUNCIÓN PARA GENERAR UUID (si no existe)
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 2. INSERTAR USUARIOS CON DATOS ENCRIPTADOS Y CONTRASEÑAS HASHEADAS
-- NOTA: Los valores encriptados se generan con el mismo método que PHP:
-- AES-256-CBC con SHA256 para key e IV

DO $$
DECLARE
    v_user_id UUID;
    -- Definir constantes para encriptación (deben coincidir con PHP)
    v_encrypt_key TEXT := 'sha256(''tu_secret_key_aqui'')'; -- Reemplazar con SECRET_KEY real
    v_encrypt_iv TEXT := 'sha256(''tu_secret_iv_aqui'')';   -- Reemplazar con SECRET_IV real
BEGIN
    -- =============================================
    -- ADMINISTRADOR: Jean Castro
    -- =============================================
    v_user_id := uuid_generate_v4();
    INSERT INTO usuario (
        ID_Usuario,
        nombre,
        apellido,
        ci,
        email,
        contrasena,
        tipo,
        usuario_asignado,
        estado,
        fecha_registro
    ) VALUES (
        v_user_id,
        'Jean',
        'Castro',
        -- CI encriptado: '1111111111' (ejemplo, usar encriptación real)
        encode(encrypt(
            '1111111111'::bytea,
            digest('tu_secret_key_aqui'::text, 'sha256'),
            'aes-cbc/pad:pkcs'
        ), 'base64'),
        -- Email encriptado: 'jean@admin.com' (ejemplo)
        encode(encrypt(
            'jean@admin.com'::bytea,
            digest('tu_secret_key_aqui'::text, 'sha256'),
            'aes-cbc/pad:pkcs'
        ), 'base64'),
        -- Contraseña hasheada con BCRYPT (costo 10)
        crypt('12345678', gen_salt('bf', 10)),
        'Administrador',
        'admin1',
        'Activo',
        CURRENT_TIMESTAMP
    );

    -- =============================================
    -- CONTABILIDAD: Sebastián Ramírez
    -- =============================================
    v_user_id := uuid_generate_v4();
    INSERT INTO usuario (
        ID_Usuario,
        nombre,
        apellido,
        ci,
        email,
        contrasena,
        tipo,
        usuario_asignado,
        estado,
        fecha_registro
    ) VALUES (
        v_user_id,
        'Sebastián',
        'Ramírez',
        encode(encrypt(
            '0987765499'::bytea,
            digest('tu_secret_key_aqui'::text, 'sha256'),
            'aes-cbc/pad:pkcs'
        ), 'base64'),
        encode(encrypt(
            'sebas@admin.com'::bytea,
            digest('tu_secret_key_aqui'::text, 'sha256'),
            'aes-cbc/pad:pkcs'
        ), 'base64'),
        crypt('12345678', gen_salt('bf', 10)),
        'Contabilidad',
        'sebas',
        'Activo',
        CURRENT_TIMESTAMP
    );

    -- =============================================
    -- LOGÍSTICA: Edú Sabando
    -- =============================================
    v_user_id := uuid_generate_v4();
    INSERT INTO usuario (
        ID_Usuario,
        nombre,
        apellido,
        ci,
        email,
        contrasena,
        tipo,
        usuario_asignado,
        estado,
        fecha_registro
    ) VALUES (
        v_user_id,
        'Edú',
        'Sabando',
        encode(encrypt(
            '1316789914'::bytea,
            digest('tu_secret_key_aqui'::text, 'sha256'),
            'aes-cbc/pad:pkcs'
        ), 'base64'),
        encode(encrypt(
            'esb@gmail.com'::bytea,
            digest('tu_secret_key_aqui'::text, 'sha256'),
            'aes-cbc/pad:pkcs'
        ), 'base64'),
        crypt('12345678', gen_salt('bf', 10)),
        'Logistica',
        'esb',
        'Activo',
        CURRENT_TIMESTAMP
    );

    -- Insertar en tabla Logistica
    INSERT INTO Logistica (ID_Logistica) VALUES (v_user_id);

    -- =============================================
    -- TÉCNICO: Joshúa Castillo (Ensamblador)
    -- =============================================
    v_user_id := uuid_generate_v4();
    INSERT INTO usuario (
        ID_Usuario,
        nombre,
        apellido,
        ci,
        email,
        contrasena,
        tipo,
        usuario_asignado,
        estado,
        fecha_registro
    ) VALUES (
        v_user_id,
        'Joshúa',
        'Castillo',
        encode(encrypt(
            '0987654321'::bytea,
            digest('tu_secret_key_aqui'::text, 'sha256'),
            'aes-cbc/pad:pkcs'
        ), 'base64'),
        encode(encrypt(
            'joshua@gmail.com'::bytea,
            digest('tu_secret_key_aqui'::text, 'sha256'),
            'aes-cbc/pad:pkcs'
        ), 'base64'),
        crypt('12345678', gen_salt('bf', 10)),
        'Tecnico',
        'joshua',
        'Activo',
        CURRENT_TIMESTAMP
    );

    -- Insertar en tabla Tecnico
    INSERT INTO Tecnico (ID_Tecnico, Especialidad) 
    VALUES (v_user_id, 'Ensamblador');

    -- =============================================
    -- TÉCNICO: Euro Quiroz (Comprobador)
    -- =============================================
    v_user_id := uuid_generate_v4();
    INSERT INTO usuario (
        ID_Usuario,
        nombre,
        apellido,
        ci,
        email,
        contrasena,
        tipo,
        usuario_asignado,
        estado,
        fecha_registro
    ) VALUES (
        v_user_id,
        'Euro',
        'Quiroz',
        encode(encrypt(
            '0987667890'::bytea,
            digest('tu_secret_key_aqui'::text, 'sha256'),
            'aes-cbc/pad:pkcs'
        ), 'base64'),
        encode(encrypt(
            'euro@gmail.com'::bytea,
            digest('tu_secret_key_aqui'::text, 'sha256'),
            'aes-cbc/pad:pkcs'
        ), 'base64'),
        crypt('12345678', gen_salt('bf', 10)),
        'Tecnico',
        'euro',
        'Activo',
        CURRENT_TIMESTAMP
    );

    -- Insertar en tabla Tecnico
    INSERT INTO Tecnico (ID_Tecnico, Especialidad) 
    VALUES (v_user_id, 'Comprobador');

    -- =============================================
    -- TÉCNICO: Joel Gabino (Mantenimiento)
    -- =============================================
    v_user_id := uuid_generate_v4();
    INSERT INTO usuario (
        ID_Usuario,
        nombre,
        apellido,
        ci,
        email,
        contrasena,
        tipo,
        usuario_asignado,
        estado,
        fecha_registro
    ) VALUES (
        v_user_id,
        'Joel',
        'Gabino',
        encode(encrypt(
            '0980980987'::bytea,
            digest('tu_secret_key_aqui'::text, 'sha256'),
            'aes-cbc/pad:pkcs'
        ), 'base64'),
        encode(encrypt(
            'joel@gmail.com'::bytea,
            digest('tu_secret_key_aqui'::text, 'sha256'),
            'aes-cbc/pad:pkcs'
        ), 'base64'),
        crypt('12345678', gen_salt('bf', 10)),
        'Tecnico',
        'joel',
        'Activo',
        CURRENT_TIMESTAMP
    );

    -- Insertar en tabla Tecnico
    INSERT INTO Tecnico (ID_Tecnico, Especialidad) 
    VALUES (v_user_id, 'Mantenimiento');

    -- =============================================
    -- VERIFICAR INSERCIÓN
    -- =============================================
    RAISE NOTICE 'Usuarios insertados correctamente';
END $$;