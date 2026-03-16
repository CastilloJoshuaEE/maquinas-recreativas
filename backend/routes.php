<?php
/**
 * API propia (es decir, una API REST personalizada).
 * Es el enrutador principal que maneja todas las
 * solicitudes HTTP entrantes y las dirige a los
 * controladores adecuados.
 */
// =============================================
// INCLUIR CONTROLADORES
// =============================================
require_once __DIR__ . '/controllers/UsuarioController.php';
require_once __DIR__ . '/controllers/ComercioController.php';
require_once __DIR__ . '/controllers/MaquinaController.php';
require_once __DIR__ . '/controllers/NotificacionController.php';
require_once __DIR__ . '/controllers/AdministradorController.php';
require_once __DIR__ . '/controllers/ReporteController.php';
require_once __DIR__ . '/controllers/ComentarioController.php';
require_once __DIR__ . '/controllers/InformeController.php';
require_once __DIR__ . '/controllers/DistribucionController.php';
require_once __DIR__ . '/controllers/ComponenteController.php';
// =============================================
// FUNCIÓN PRINCIPAL DE ENRUTAMIENTO
// =============================================
function sendResponse($data, $statusCode = 200) {

    http_response_code($statusCode);

    header("Content-Type: application/json; charset=utf-8");
    header("X-Content-Type-Options: nosniff");

    echo json_encode($data, JSON_UNESCAPED_UNICODE);

    exit;
}
function routeRequest($apiRoute, $requestMethod) {
    // Depuración - quitar en producción
    error_log("Ruta solicitada: " . $apiRoute . " Método: " . $requestMethod);
    
    switch ($apiRoute) {
          // ENDPOINT DE SALUD
case '/health':

    if ($requestMethod === 'GET') {

        sendResponse([
            "status" => "ok",
            "message" => "Backend funcionando",
            "time" => date("Y-m-d H:i:s")
        ]);

    } else {

        sendResponse([
            "success" => false,
            "message" => "Método no permitido"
        ], 405);

    }

break;


case '/test-db':

    if ($requestMethod === 'GET') {

        $db = new Database();

        sendResponse([
            "success" => true,
            "message" => "Conexión a DB exitosa"
        ]);

    } else {

        sendResponse([
            "success" => false,
            "message" => "Método no permitido"
        ], 405);

    }

break;
    // --------------------------------
        // ENDPOINTS DE USUARIO
        // --------------------------------
case '/usuario/register':

    if ($requestMethod !== 'POST') {
        sendResponse([
            'success' => false,
            'message' => 'Método no permitido'
        ], 405);
    }

    $controller = new UsuarioController();
    $controller->register();

break;
case '/usuario/buscar-email':

    if ($requestMethod !== 'POST') {
        sendResponse([
            'success' => false,
            'message' => 'Método no permitido'
        ], 405);
    }

    $controller = new UsuarioController();
    $controller->buscarPorEmail();

break;

case '/usuario/login':

    if ($requestMethod !== 'POST') {
        sendResponse([
            'success' => false,
            'message' => 'Método no permitido'
        ], 405);
    }

    $controller = new UsuarioController();
    $controller->login();

break;

        case (preg_match('/\/usuario\/profile\/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/i', $apiRoute, $matches) ? true : false):
            if ($requestMethod === 'GET') {
                $controller = new UsuarioController();
                $controller->getProfile($matches[1]);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            }
            break;

        case (preg_match('/\/usuario\/tecnicos\/(\w+)/', $apiRoute, $matches) ? true : false):
            if ($requestMethod === 'GET') {
                $controller = new UsuarioController();
                $controller->obtenerTecnicos($matches[1]);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            }
            break;

        // --------------------------------
        // ENDPOINTS DE COMERCIO
        // --------------------------------
        case '/comercio/register':
            if ($requestMethod === 'POST') {
                $controller = new ComercioController();
                $controller->register();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            }
            break;

        case '/comercio/all':
            if ($requestMethod === 'GET') {
                $controller = new ComercioController();
                $controller->obtenerComercios();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            }
            break;

        // --------------------------------
        // ENDPOINTS DE MÁQUINAS RECREATIVAS
        // --------------------------------
        case '/maquina/register':
            if ($requestMethod === 'POST') {
                $controller = new MaquinaController();
                $controller->register();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            }
            break;

        case '/maquina/mandar-comprobacion':
            if ($requestMethod === 'POST') {
                $controller = new MaquinaController();
                $controller->mandarAComprobacion();
            }
            break;

        case '/maquina/mandar-reensamblar':
            if ($requestMethod === 'POST') {
                $controller = new MaquinaController();
                $controller->mandarAReensamblar();
            }
            break;

        case '/maquina/mandar-distribucion':
            if ($requestMethod === 'POST') {
                $controller = new MaquinaController();
                $controller->mandarADistribucion();
            }
            break;

        case '/maquina/poner-operativa':
            if ($requestMethod === 'POST') {
                $controller = new MaquinaController();
                $controller->ponerOperativa();
            }
            break;

        case (preg_match('/\/maquina\/ensamblador\/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/i', $apiRoute, $matches) ? true : false):
            if ($requestMethod === 'GET') {
                $controller = new MaquinaController();
                $controller->obtenerPorTecnicoEnsamblador($matches[1]);
            }
            break;

        case (preg_match('/\/maquina\/comprobador\/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/i', $apiRoute, $matches) ? true : false):
            if ($requestMethod === 'GET') {
                $controller = new MaquinaController();
                $controller->obtenerPorTecnicoComprobador($matches[1]);
            }
            break;

        case (preg_match('/\/maquina\/mantenimiento\/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/i', $apiRoute, $matches) ? true : false):
            if ($requestMethod === 'GET') {
                $controller = new MaquinaController();
                $controller->obtenerPorTecnicoMantenimiento($matches[1]);
            }
            break;

        case '/maquina/dar-mantenimiento':
            if ($requestMethod === 'POST') {
                $controller = new MaquinaController();
                $controller->darMantenimiento();
            }
            break;

        case '/maquina/finalizar-mantenimiento':
            if ($requestMethod === 'POST') {
                $controller = new MaquinaController();
                $controller->finalizarMantenimiento();
            }
            break;

        case (preg_match('/\/maquina\/estado\/(\w+)/', $apiRoute, $matches) ? true : false):
            if ($requestMethod === 'GET') {
                $controller = new MaquinaController();
                $controller->obtenerPorEstado($matches[1]);
            }
            break;
        
        case (preg_match('/\/maquina\/etapa\/(\w+)/', $apiRoute, $matches) ? true : false):
            if ($requestMethod === 'GET') {
                $controller = new MaquinaController();
                $controller->obtenerPorEtapa($matches[1]);
            }
            break;

        // --------------------------------
        // ENDPOINTS DE NOTIFICACIONES
        // --------------------------------
// --------------------------------
// ENDPOINTS DE NOTIFICACIONES 
// --------------------------------

// Notificaciones de máquinas (tabla NotificacionMaquinaRecreativa)
case (preg_match('/\/notificaciones_maquina\/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/i', $apiRoute, $matches) ? true : false):
    if ($requestMethod === 'GET') {
        $controller = new NotificacionController();
        $controller->obtenerPorUsuario($matches[1]);
    }
    break;

// Notificaciones de reportes (tabla notificaciones) - GET con UUID
case (preg_match('#^/notificaciones/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})$#i', $apiRoute, $m) ? true : false):
    if ($requestMethod === 'GET') {
        $controller = new NotificacionController();
        $controller->getNotificaciones($m[1]);
    }
    break;

// Marcar una notificación como leída - POST con UUID
case (preg_match('#^/notificaciones/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/marcarla-leida$#i', $apiRoute, $m) ? true : false):
    if ($requestMethod === 'POST') {
        $controller = new NotificacionController();
        $controller->marcarComoLeidaNotificacion($m[1]);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
    break;

// Marcar todas como leídas
case '/notificaciones/marcarla-todas-leidas':
    if ($requestMethod === 'POST') {
        $controller = new NotificacionController();
        $controller->marcarTodasComoLeidas();
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
    break;

// Crear notificación
case '/notificaciones/create':
    if ($requestMethod === 'POST') {
        $controller = new NotificacionController();
        $controller->create();
    }
    break;

// Marcar como leída (versión antigua, mantener por compatibilidad)
case '/notificaciones/marcar-leida':
    if ($requestMethod === 'POST') {
        $controller = new NotificacionController();
        $controller->marcarComoLeida();
    }
    break;

// Obtener no leídas
case (preg_match('/\/notificaciones\/no-leidas\/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/i', $apiRoute, $matches) ? true : false):
    if ($requestMethod === 'GET') {
        $controller = new NotificacionController();
        $controller->obtenerNoLeidas($matches[1]);
    }
    break;

        // --------------------------------
        // PERFIL DE USUARIO ENDPOINTS
        // --------------------------------
        case '/usuario/perfil':
            if ($requestMethod === 'GET') {
                $controller = new UsuarioController();
                $id = isset($_GET['id']) ? $_GET['id'] : null;
                $controller->getProfile($id);
            }
            break;
            
        case '/usuario/recuperar-contrasena':
            if ($requestMethod === 'POST') {
                $controller = new UsuarioController();
                $controller->resetPassword();
            }
            break;
            
        case '/usuario/recuperar-usuario':
            if ($requestMethod === 'POST') {
                $controller = new UsuarioController();
                $controller->updateUsername();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            }
            break;
            
        case (preg_match('#^/administrador/usuarios/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})$#i', $apiRoute, $matches) ? true : false):
            $controller = new AdministradorController();
            $userId = $matches[1];
            
            if ($requestMethod === 'GET') {
                $controller->getUser($userId);
            } elseif ($requestMethod === 'PUT') {
                $input = json_decode(file_get_contents('php://input'), true);
                $controller->updateUser($userId, $input);
            } elseif ($requestMethod === 'PATCH') {
                $input = json_decode(file_get_contents('php://input'), true);
                $controller->partialUpdateUser($userId, $input);
            } elseif ($requestMethod === 'DELETE') {
                $controller->deleteUser($userId);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            }
            break;

        // Luego la ruta SIN UUID
        case '/administrador/usuarios':
            $controller = new AdministradorController();
            if ($requestMethod === 'GET') {
                $filters = $_GET;
                $controller->getAllUsers($filters);
            } elseif ($requestMethod === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $controller->registerAdmin($input);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            }
            break;

        case '/usuario/logout':
            if ($requestMethod === 'POST') {
                $controller = new UsuarioController();
                $controller->logout();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            }
            break;

        case '/usuario/actualizar-perfil':
            if ($requestMethod === 'POST') {
                $controller = new UsuarioController();
                $input = json_decode(file_get_contents('php://input'), true);
                $controller->updateProfile();
            }
            break;
            
        case '/usuarios/por-tipo':
            if ($requestMethod === 'GET') {
                $controller = new UsuarioController();
                $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : null;
                $emisorId = isset($_GET['emisorId']) ? $_GET['emisorId'] : null;
                $controller->getByTipo($tipo, $emisorId);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            }
            break;
            
        case '/historial-actividades':
            session_start();

            if ($requestMethod === 'GET') {
                if (!isset($_SESSION['ID_Usuario'])) {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'message' => 'No autorizado']);
                    break;
                }

                $usuarioId = $_GET['usuarioId'] ?? null;
                if ($usuarioId && !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $usuarioId)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'ID de usuario no válido']);
                    break;
                }
                
                if ($_SESSION['rol'] === 'Administrador') {
                    $controller = new AdministradorController();
                    $controller->obtenerHistorialActividades($usuarioId);
                } else {
                    if (!$usuarioId || $_SESSION['ID_Usuario'] !== $usuarioId) {
                        http_response_code(403);
                        echo json_encode(['success' => false, 'message' => 'No autorizado']);
                        break;
                    }

                    $controller = new UsuarioController();
                    $controller->obtenerHistorialActividades($usuarioId);
                }
            }
            else if ($requestMethod === 'POST') {
                if (!isset($_SESSION['ID_Usuario'])) {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'message' => 'No autorizado']);
                    break;
                }
                $controller = new UsuarioController();
                $controller->registrarActividad();
            }
            else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            }
            break;  
            
        // --------------------------------
        // ENDPOINTS DE REPORTES
        // --------------------------------
        case '/reportes/crear':
            if ($requestMethod === 'POST') {
                (new ReporteController())->create();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            }
            break;

        case (preg_match('#^/reportes/usuario/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})$#i', $apiRoute, $m) ? true : false):
            if ($requestMethod === 'GET') {
                (new ReporteController())->getByUser($m[1]);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            }
            break;

        case (preg_match('#^/reportes/chat/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})$#i', $apiRoute, $m) ? true : false):
            if ($requestMethod === 'GET') {
                (new ReporteController())->getChat($m[1], $m[2]);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            }
            break;
            
        case (preg_match('#^/reportes/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/estado$#i', $apiRoute, $m) ? true : false):
            if ($requestMethod === 'PUT') {
                (new ReporteController())->updateStatus($m[1]);
            }
            break;
            
        case '/reportes/usuarios-chat':
            if ($requestMethod === 'GET') {
                $userId = $_GET['userId'] ?? null;
                if (!$userId) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Se requiere userId']);
                    break;
                }
                (new ReporteController())->getUsuariosChat($userId);
            }
            break;
            
        case '/reportes/chat-completo':
            if ($requestMethod === 'GET') {
                $emisorId = $_GET['emisorId'] ?? null;
                $destinatarioId = $_GET['destinatarioId'] ?? null;
                $reporteId = $_GET['reporteId'] ?? null;
                
                if (!$emisorId || !$destinatarioId) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Se requieren emisorId y destinatarioId']);
                    break;
                }
                
                (new ReporteController())->getCompleteChat($emisorId, $destinatarioId, $reporteId);
            }
            break;
            
        // --------------------------------
        // ENDPOINTS DE COMENTARIOS
        // --------------------------------
        case '/comentarios':
            if ($requestMethod === 'POST') {
                (new ComentarioController())->create();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            }
            break;

        case (preg_match('#^/comentarios/reporte/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})$#i', $apiRoute, $m) ? true : false):
            if ($requestMethod === 'GET') {
                (new ComentarioController())->getByReporte($m[1]);
            }
            break;
            
        // --------------------------------
        // ENDPOINTS DE LOGISTICA Y MANTENIMIENTO 
        // --------------------------------
        case '/maquina/distribucion':
            if ($requestMethod === 'GET') {
                (new MaquinaController())->obtenerMaquinasParaDistribucion();
            }
            break;

        case '/distribucion/informes':
            if ($requestMethod === 'GET') {
                (new DistribucionController())->obtenerInformesDistribucion();
            }
            break;
            

        // --------------------------------
        // ENDPOINTS DE COMPONENTES
        // --------------------------------
        case '/componentes':
            if ($requestMethod === 'GET') {
                $controller = new ComponenteController();
                $tipo = $_GET['tipo'] ?? null;
                $controller->obtenerComponentes($tipo);
            }
            break;
            
        case '/componentes/disponibles':
            if ($requestMethod === 'GET') {
                $controller = new ComponenteController();
                $tipo = $_GET['tipo'] ?? null;
                $controller->obtenerComponentesDisponibles($tipo);
            }
            break;

        case '/maquina/generar-placa':
            if ($requestMethod === 'POST') {
                $controller = new MaquinaController();
                $controller->generarPlaca();
            }
            break;
            
        case '/componentes/usar':
            if ($requestMethod === 'POST') {
                $controller = new ComponenteController();
                $controller->usarComponente();
            }
            break;

        case '/componentes/liberar':
            if ($requestMethod === 'POST') {
                $controller = new ComponenteController();
                $controller->liberarComponente();
            }
            break;
            
        case '/componentes/asignar-carcasa':
            if ($requestMethod === 'POST') {
                $controller = new ComponenteController();
                $controller->asignarCarcasa();
            }
            break;
            
        case (preg_match('/\/componentes\/en-uso\/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/i', $apiRoute, $matches) ? true : false):
            if ($requestMethod === 'GET') {
                $controller = new ComponenteController();
                $controller->obtenerComponentesEnUso($matches[1]);
            }
            break;
            
        case '/componentes/liberar-cancelacion':
            if ($requestMethod === 'POST') {
                $controller = new ComponenteController();
                $controller->liberarComponentesCancelacion();
            }
            break;
            
        // --------------------------------
        // ENDPOINTS DE INFORMES DE CONTABILIDAD
        // --------------------------------
        case '/contabilidad/registrar-recaudacion':
            if ($requestMethod === 'POST') {
                (new InformeController())->registrarRecaudacion();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            }
            break;

        case '/contabilidad/recaudaciones':
            if ($requestMethod === 'GET') {
                (new InformeController())->obtenerRecaudaciones();
            }
            break;

        case '/contabilidad/resumen-recaudaciones':
            if ($requestMethod === 'GET') {
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 2;
                (new InformeController())->obtenerResumenRecaudaciones($limit);
            }
            break;

        case '/contabilidad/actualizar-recaudacion':
            if ($requestMethod === 'PUT') {
                (new InformeController())->actualizarRecaudacion();
            }
            break;

        case (preg_match('#^/contabilidad/eliminar-recaudacion/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})$#i', $apiRoute, $matches) ? true : false):
            if ($requestMethod === 'DELETE') {
                (new InformeController())->eliminarRecaudacion($matches[1]);
            }
            break;

        case '/contabilidad/maquinas-recaudacion':
            if ($requestMethod === 'GET') {
                (new InformeController())->obtenerMaquinasRecaudacion();
            }
            break;
            
        case '/contabilidad/maquinas-operativas-por-comercio':
            if ($requestMethod === 'GET' && isset($_GET['ID_Comercio'])) {
                (new InformeController())->obtenerMaquinasOperativasPorComercio();
            }
            break;

        case (preg_match('#^/maquina/componentes/([a-f0-9\-]{36})$#i', $apiRoute, $matches) ? true : false):
            if ($requestMethod === 'GET') {
                (new MaquinaController())->obtenerComponentesPorMaquina($matches[1]);
            }
            break;
            
        case '/contabilidad/guardar-informe':
            if ($requestMethod === 'POST') {
                (new InformeController())->guardarInforme();
            }
            break;
            
        // Obtener recaudación específica
        case (preg_match('#^/contabilidad/recaudaciones/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})$#i', $apiRoute, $matches) ? true : false):
            if ($requestMethod === 'GET') {
                (new InformeController())->obtenerRecaudacion($matches[1]);
            }
            break;

        // Obtener máquina
        case '/contabilidad/maquina-recaudacion':
            if ($requestMethod === 'GET') {
                (new InformeController())->obtenerMaquinaRecaudacion();
            }
            break;

        // Obtener comercio
        case (preg_match('#^/contabilidad/comercio-recaudacion/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})$#i', $apiRoute, $matches) ? true : false):
            if ($requestMethod === 'GET') {
                (new InformeController())->obtenerComercioRecaudacion($matches[1]);
            }
            break;

        // ENDPOINT DE MONTAJE
        case '/maquina/registrar-montaje':
            if ($requestMethod === 'POST') {
                $controller = new MaquinaController();
                $controller->registrarMontaje();
            }
            break;
case (preg_match('#^/contabilidad/informe/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})$#i', $apiRoute, $matches) ? true : false):
    if ($requestMethod === 'GET') {
        (new InformeController())->obtenerInformePorRecaudacion($matches[1]);
    }
    break;
        // --------------------------------
        // ENDPOINT POR DEFECTO (404)
        // --------------------------------
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint no encontrado: ' . $apiRoute]);
            break;
    }
}
?>