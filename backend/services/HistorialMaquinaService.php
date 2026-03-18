<?php
require_once __DIR__ . '/../models/HistorialMaquinaModel.php';

class HistorialMaquinaService {
    private $model;
    
    public function __construct() {
        $this->model = new HistorialMaquinaModel();
    }
    
    /**
     * Registrar actividad en máquina
     */
    public function registrarActividad($idMaquina, $idUsuario, $tipo_usuario, $accion, $descripcion = '', 
                                        $estadoAnterior = null, $estadoNuevo = null,
                                        $etapaAnterior = null, $etapaNueva = null,
                                        $ip_address = null, $detallesAdicionales = []) {
        
        $data = [
            'ID_Maquina' => $idMaquina,
            'ID_Usuario' => $idUsuario,
            'tipo_usuario' => $tipo_usuario,
            'accion' => $accion,
            'descripcion' => $descripcion,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'etapa_anterior' => $etapaAnterior,
            'etapa_nueva' => $etapaNueva,
            'ip_address' => $ip_address,
            'detalles_adicionales' => !empty($detallesAdicionales) ? $detallesAdicionales : null
        ];
        
        return $this->model->registrarActividad($data);
    }
    
    /**
     * Obtener historial de una máquina
     */
    public function obtenerHistorialPorMaquina($idMaquina, $pagina = 1, $porPagina = 50) {
        if (empty($idMaquina)) {
            return ['success' => false, 'message' => 'ID de máquina requerido'];
        }
        
        $offset = ($pagina - 1) * $porPagina;
        $historial = $this->model->obtenerHistorialPorMaquina($idMaquina, $porPagina, $offset);
        $total = $this->model->contarHistorial(['id_maquina' => $idMaquina]);
        
        return [
            'success' => true,
            'historial' => $historial,
            'paginacion' => [
                'pagina_actual' => $pagina,
                'por_pagina' => $porPagina,
                'total' => $total,
                'total_paginas' => ceil($total / $porPagina)
            ]
        ];
    }
    
    /**
     * Obtener historial de un usuario
     */
    public function obtenerHistorialPorUsuario($idUsuario, $pagina = 1, $porPagina = 50) {
        if (empty($idUsuario)) {
            return ['success' => false, 'message' => 'ID de usuario requerido'];
        }
        
        $offset = ($pagina - 1) * $porPagina;
        $historial = $this->model->obtenerHistorialPorUsuario($idUsuario, $porPagina, $offset);
        $total = $this->model->contarHistorial(['id_usuario' => $idUsuario]);
        
        return [
            'success' => true,
            'historial' => $historial,
            'paginacion' => [
                'pagina_actual' => $pagina,
                'por_pagina' => $porPagina,
                'total' => $total,
                'total_paginas' => ceil($total / $porPagina)
            ]
        ];
    }
    
    /**
     * Obtener historial general con filtros
     */
    public function obtenerHistorialGeneral($filtros = [], $pagina = 1, $porPagina = 100) {
        $offset = ($pagina - 1) * $porPagina;
        $historial = $this->model->obtenerHistorialGeneral($filtros, $porPagina, $offset);
        $total = $this->model->contarHistorial($filtros);
        
        return [
            'success' => true,
            'historial' => $historial,
            'paginacion' => [
                'pagina_actual' => $pagina,
                'por_pagina' => $porPagina,
                'total' => $total,
                'total_paginas' => ceil($total / $porPagina)
            ]
        ];
    }
    
    /**
     * Obtener resumen de actividades recientes
     */
    public function obtenerResumenReciente($limite = 20) {
        $historial = $this->model->obtenerHistorialGeneral([], $limite, 0);
        
        // Agrupar por tipo de acción
        $resumen = [
            'total' => count($historial),
            'por_accion' => [],
            'recientes' => array_slice($historial, 0, 10)
        ];
        
        foreach ($historial as $item) {
            $accion = $item['accion'];
            if (!isset($resumen['por_accion'][$accion])) {
                $resumen['por_accion'][$accion] = 0;
            }
            $resumen['por_accion'][$accion]++;
        }
        
        return $resumen;
    }
}
?>