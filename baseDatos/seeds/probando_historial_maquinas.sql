USE bd_recrea_sys;
-- Tabla para historial de actividades de máquinas recreativas
SELECT * FROM historial_maquinas;
-- Insertar algunos registros de ejemplo en el historial
INSERT INTO historial_maquinas 
(ID_Historial, ID_Maquina, ID_Usuario, tipo_usuario, accion, descripcion, 
 estado_anterior, estado_nuevo, etapa_anterior, etapa_nueva, fecha_hora, ip_address, detalles_adicionales)
SELECT 
    UUID(), 
    m.ID_Maquina,
    (SELECT ID_Usuario FROM usuario WHERE tipo = 'Logistica' LIMIT 1),
    'Logistica',
    'Registro de máquina',
    CONCAT('Nueva máquina registrada: ', m.Nombre_Maquina),
    NULL,
    'Ensamblandose',
    NULL,
    'Montaje',
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 30) DAY),
    '192.168.1.' || FLOOR(RAND() * 255),
    JSON_OBJECT('comercio', (SELECT Nombre FROM Comercio WHERE ID_Comercio = m.ID_Comercio))
FROM MaquinaRecreativa m
WHERE NOT EXISTS (
    SELECT 1 FROM historial_maquinas h WHERE h.ID_Maquina = m.ID_Maquina AND h.accion = 'Registro de máquina'
)
LIMIT 5;

-- Insertar registros de envío a comprobación
INSERT INTO historial_maquinas 
(ID_Historial, ID_Maquina, ID_Usuario, tipo_usuario, accion, descripcion, 
 estado_anterior, estado_nuevo, fecha_hora)
SELECT 
    UUID(), 
    m.ID_Maquina,
    (SELECT ID_Usuario FROM Tecnico t JOIN usuario u ON t.ID_Tecnico = u.ID_Usuario WHERE t.Especialidad = 'Ensamblador' LIMIT 1),
    'Tecnico',
    'Envío a comprobación',
    'Máquina enviada a comprobación',
    'Ensamblandose',
    'Comprobandose',
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 25) DAY)
FROM MaquinaRecreativa m
WHERE m.Estado IN ('Comprobandose', 'Distribuyendose', 'Operativa')
LIMIT 5;

-- Insertar registros de puesta en operativa
INSERT INTO historial_maquinas 
(ID_Historial, ID_Maquina, ID_Usuario, tipo_usuario, accion, descripcion, 
 estado_anterior, estado_nuevo, etapa_anterior, etapa_nueva, fecha_hora)
SELECT 
    UUID(), 
    m.ID_Maquina,
    (SELECT ID_Usuario FROM usuario WHERE tipo = 'Logistica' LIMIT 1),
    'Logistica',
    'Puesta en operativa',
    'Máquina marcada como operativa',
    'Distribuyendose',
    'Operativa',
    'Distribucion',
    'Recaudacion',
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 20) DAY)
FROM MaquinaRecreativa m
WHERE m.Estado = 'Operativa'
LIMIT 5;

-- Insertar registros de mantenimiento
INSERT INTO historial_maquinas 
(ID_Historial, ID_Maquina, ID_Usuario, tipo_usuario, accion, descripcion, 
 estado_anterior, estado_nuevo, fecha_hora, detalles_adicionales)
SELECT 
    UUID(), 
    m.ID_Maquina,
    (SELECT ID_Usuario FROM Tecnico t JOIN usuario u ON t.ID_Tecnico = u.ID_Usuario WHERE t.Especialidad = 'Mantenimiento' LIMIT 1),
    'Tecnico',
    'Solicitud de mantenimiento',
    CONCAT('Mantenimiento solicitado. Motivo: Revisión periódica'),
    'Operativa',
    'No operativa',
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 15) DAY),
    JSON_OBJECT('tecnico_asignado', 'Técnico de mantenimiento', 'motivo', 'Revisión periódica')
FROM MaquinaRecreativa m
WHERE m.ID_Tecnico_Mantenimiento IS NOT NULL
LIMIT 3;