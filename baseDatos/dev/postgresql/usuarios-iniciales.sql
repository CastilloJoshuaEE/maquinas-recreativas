-- ============================================================
-- USUARIOS INICIALES (VERSIÓN CORREGIDA)
-- ============================================================

-- 1. ADMINISTRADOR
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
    gen_random_uuid(),
    'Jean',
    'Castro',
    '1111111111',
    'jean@admin.com',
    crypt('12345678', gen_salt('bf')),
    'Administrador',
    'admin1',
    'Activo',
    NOW()
) ON CONFLICT (usuario_asignado) DO NOTHING;

-- 2. CONTABILIDAD
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
    gen_random_uuid(),
    'Sebastian',
    'Ramirez',
    '0987765499',
    'sebas@admin.com',
    crypt('12345678', gen_salt('bf')),
    'Contabilidad',
    'sebas',
    'Activo',
    NOW()
) ON CONFLICT (usuario_asignado) DO NOTHING;

-- 3. LOGISTICA
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
    gen_random_uuid(),
    'Edu',
    'Sabando',
    '1316789914',
    'esb@gmail.com',
    crypt('12345678', gen_salt('bf')),
    'Logistica',
    'esb',
    'Activo',
    NOW()
) ON CONFLICT (usuario_asignado) DO NOTHING;

-- 4. TECNICO ENSAMBLADOR
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
    gen_random_uuid(),
    'Joshua',
    'Castillo',
    '0987654321',
    'joshua@gmail.com',
    crypt('12345678', gen_salt('bf')),
    'Tecnico',
    'joshua',
    'Activo',
    NOW()
) ON CONFLICT (usuario_asignado) DO NOTHING;

-- 5. TECNICO COMPROBADOR
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
    gen_random_uuid(),
    'Euro',
    'Quiroz',
    '0987667890',
    'euro@gmail.com',
    crypt('12345678', gen_salt('bf')),
    'Tecnico',
    'euro',
    'Activo',
    NOW()
) ON CONFLICT (usuario_asignado) DO NOTHING;

-- 6. TECNICO MANTENIMIENTO
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
    gen_random_uuid(),
    'Joel',
    'Gabino',
    '0980980987',
    'joel@gmail.com',
    crypt('12345678', gen_salt('bf')),
    'Tecnico',
    'joel',
    'Activo',
    NOW()
) ON CONFLICT (usuario_asignado) DO NOTHING;

-- Insertar tecnicos
INSERT INTO Tecnico (ID_Tecnico, Especialidad, Cantidad_Actividades)
SELECT ID_Usuario, 
       CASE usuario_asignado
           WHEN 'joshua' THEN 'Ensamblador'
           WHEN 'euro' THEN 'Comprobador'
           WHEN 'joel' THEN 'Mantenimiento'
       END,
       0
FROM usuario
WHERE usuario_asignado IN ('joshua', 'euro', 'joel')
ON CONFLICT (ID_Tecnico) DO NOTHING;

-- Insertar logistica
INSERT INTO Logistica (ID_Logistica)
SELECT ID_Usuario
FROM usuario
WHERE usuario_asignado = 'esb'
ON CONFLICT (ID_Logistica) DO NOTHING;

-- Verificar
SELECT 
    usuario_asignado,
    nombre,
    apellido,
    tipo,
    CASE 
        WHEN tipo = 'Tecnico' THEN (
            SELECT Especialidad 
            FROM Tecnico 
            WHERE ID_Tecnico = usuario.ID_Usuario
        )
        ELSE NULL
    END AS especialidad
FROM usuario
WHERE usuario_asignado IN ('admin1', 'sebas', 'esb', 'joshua', 'euro', 'joel');