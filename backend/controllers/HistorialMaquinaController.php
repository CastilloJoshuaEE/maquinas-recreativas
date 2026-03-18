<?php
require_once __DIR__ . '/../services/HistorialMaquinaService.php';

class HistorialMaquinaController {
    private $service;
    
    public function __construct() {
        $this->service = new HistorialMaquinaService();
    }
    
    /**
     * Obtener historial de una máquina específica
     */
    public function getHistorialPorMaquina($idMaquina) {
        session_start();
        
        if (!isset($_SESSION['ID_Usuario'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 50;
        
        $response = $this->service->obtenerHistorialPorMaquina($idMaquina, $pagina, $porPagina);
        $this->sendResponse($response);
    }
    
    /**
     * Obtener historial de un usuario específico
     */
    public function getHistorialPorUsuario($idUsuario) {
        session_start();
        
        if (!isset($_SESSION['ID_Usuario'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        
        // Verificar permisos: solo admin o el propio usuario
        if ($_SESSION['rol'] !== 'Administrador' && $_SESSION['ID_Usuario'] !== $idUsuario) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado para ver este historial']);
            return;
        }
        
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 50;
        
        $response = $this->service->obtenerHistorialPorUsuario($idUsuario, $pagina, $porPagina);
        $this->sendResponse($response);
    }
    
    /**
     * Obtener historial general con filtros (solo para administradores y logística)
     */
    public function getHistorialGeneral() {
        session_start();
        
        if (!isset($_SESSION['ID_Usuario'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        
        // Solo administradores y logística pueden ver el historial general
        $rolesPermitidos = ['Administrador', 'Logistica', 'Tecnico'];
        if (!in_array($_SESSION['rol'], $rolesPermitidos)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado para ver el historial general']);
            return;
        }
        
        $filtros = [
            'id_maquina' => $_GET['id_maquina'] ?? null,
            'id_usuario' => $_GET['id_usuario'] ?? null,
            'tipo_usuario' => $_GET['tipo_usuario'] ?? null,
            'accion' => $_GET['accion'] ?? null,
            'fecha_inicio' => $_GET['fecha_inicio'] ?? null,
            'fecha_fin' => $_GET['fecha_fin'] ?? null
        ];
        
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 100;
        
        $response = $this->service->obtenerHistorialGeneral($filtros, $pagina, $porPagina);
        $this->sendResponse($response);
    }
    
    /**
     * Obtener resumen de actividades recientes
     */
    public function getResumenReciente() {
        session_start();
        
        if (!isset($_SESSION['ID_Usuario'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        
        $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 20;
        $resumen = $this->service->obtenerResumenReciente($limite);
        
        $this->sendResponse([
            'success' => true,
            'resumen' => $resumen
        ]);
    }
    
    private function sendResponse($response, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}
?>