<?php

class HistorialHelper {
    private static $instance = null;
    private $service;
    
    private function __construct() {
        $this->service = new HistorialMaquinaService();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Registrar actividad de máquina - Versión corregida con 12 parámetros
     */
    public function registrar($idMaquina, $idUsuario, $tipo_usuario, $accion, $descripcion = '',
                               $estadoAnterior = null, $estadoNuevo = null,
                               $etapaAnterior = null, $etapaNueva = null,
                               $ip_address = null, $detalles = []) {
        try {
            return $this->service->registrarActividad(
                $idMaquina, $idUsuario, $tipo_usuario, $accion, $descripcion,
                $estadoAnterior, $estadoNuevo, $etapaAnterior, $etapaNueva,
                $ip_address, $detalles
            );
        } catch (Exception $e) {
            error_log("Error registrando historial: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Registrar envío a comprobación
     */
    public function registrarEnvioComprobacion($idMaquina, $idUsuario, $mensaje) {
        // Obtener IP
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        return $this->registrar(
            $idMaquina, 
            $idUsuario, 
            $this->getTipoUsuario($idUsuario), // tipo_usuario
            'Envío a comprobación', // accion
            "Máquina enviada a comprobación. Mensaje: $mensaje", // descripcion
            'Ensamblandose/Reensamblandose', // estado_anterior
            'Comprobandose', // estado_nuevo
            'Montaje', // etapa_anterior
            'Montaje', // etapa_nueva
            $ip_address, // ip_address
            ['mensaje' => $mensaje] // detalles
        );
    }
    
    /**
     * Registrar envío a reensamblar
     */
    public function registrarEnvioReensamblar($idMaquina, $idUsuario, $mensaje) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        return $this->registrar(
            $idMaquina, 
            $idUsuario, 
            $this->getTipoUsuario($idUsuario),
            'Envío a reensamblar',
            "Máquina rechazada, enviada a reensamblar. Motivo: $mensaje",
            'Comprobandose',
            'Reensamblandose',
            'Montaje',
            'Montaje',
            $ip_address,
            ['motivo' => $mensaje]
        );
    }
    
    /**
     * Registrar envío a distribución
     */
    public function registrarEnvioDistribucion($idMaquina, $idUsuario, $comercio, $mensaje) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        return $this->registrar(
            $idMaquina, 
            $idUsuario, 
            $this->getTipoUsuario($idUsuario),
            'Envío a distribución',
            "Máquina aprobada y enviada a distribución. Comercio: $comercio. Mensaje: $mensaje",
            'Comprobandose',
            'Distribuyendose',
            'Montaje',
            'Distribucion',
            $ip_address,
            ['comercio' => $comercio, 'mensaje' => $mensaje]
        );
    }
    
    /**
     * Registrar puesta en operativa
     */
    public function registrarPuestaOperativa($idMaquina, $idUsuario) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        return $this->registrar(
            $idMaquina, 
            $idUsuario, 
            $this->getTipoUsuario($idUsuario),
            'Puesta en operativa',
            "Máquina marcada como operativa y en etapa de recaudación",
            'Distribuyendose',
            'Operativa',
            'Distribucion',
            'Recaudacion',
            $ip_address,
            []
        );
    }
    
    /**
     * Registrar solicitud de mantenimiento
     */
    public function registrarSolicitudMantenimiento($idMaquina, $idUsuario, $tecnicoAsignado, $mensaje) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        return $this->registrar(
            $idMaquina, 
            $idUsuario, 
            $this->getTipoUsuario($idUsuario),
            'Solicitud de mantenimiento',
            "Máquina enviada a mantenimiento. Técnico asignado: $tecnicoAsignado. Motivo: $mensaje",
            'Operativa',
            'No operativa',
            'Recaudacion',
            'Montaje',
            $ip_address,
            ['tecnico_asignado' => $tecnicoAsignado, 'motivo' => $mensaje]
        );
    }
    
    /**
     * Registrar finalización de mantenimiento
     */
    public function registrarFinMantenimiento($idMaquina, $idUsuario, $exito, $mensaje) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $estadoNuevo = $exito ? 'Operativa' : 'Retirada';
        $etapaNueva = $exito ? 'Recaudacion' : 'Distribucion';
        $descripcion = $exito 
            ? "Mantenimiento completado con éxito. Máquina operativa. Mensaje: $mensaje"
            : "Mantenimiento completado. Máquina retirada. Mensaje: $mensaje";
            
        return $this->registrar(
            $idMaquina, 
            $idUsuario, 
            $this->getTipoUsuario($idUsuario),
            'Finalización de mantenimiento',
            $descripcion,
            'No operativa',
            $estadoNuevo,
            'Montaje',
            $etapaNueva,
            $ip_address,
            ['exito' => $exito, 'mensaje' => $mensaje]
        );
    }
    
    /**
     * Registrar registro de nueva máquina
     */
    public function registrarRegistroMaquina($idMaquina, $idUsuario, $nombreMaquina, $comercio, $ensamblador, $comprobador) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        return $this->registrar(
            $idMaquina, 
            $idUsuario, 
            $this->getTipoUsuario($idUsuario),
            'Registro de máquina',
            "Nueva máquina registrada: $nombreMaquina en comercio: $comercio",
            null,
            'Ensamblandose',
            null,
            'Montaje',
            $ip_address,
            [
                'nombre_maquina' => $nombreMaquina,
                'comercio' => $comercio,
                'ensamblador' => $ensamblador,
                'comprobador' => $comprobador
            ]
        );
    }
    
    /**
     * Registrar montaje de componente
     */
    public function registrarMontajeComponente($idMaquina, $idUsuario, $componente, $detalle = '') {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        return $this->registrar(
            $idMaquina, 
            $idUsuario, 
            $this->getTipoUsuario($idUsuario),
            'Montaje de componente',
            "Componente montado: $componente. $detalle",
            null,
            null,
            null,
            null,
            $ip_address,
            ['componente' => $componente, 'detalle' => $detalle]
        );
    }
    
    /**
     * Obtener tipo de usuario por ID
     */
    private function getTipoUsuario($idUsuario) {
        try {
            $usuarioModel = new UsuarioModel();
            $usuario = $usuarioModel->obtenerUsuarioPorId($idUsuario);
            return $usuario ? $usuario['tipo'] : 'Desconocido';
        } catch (Exception $e) {
            error_log("Error obteniendo tipo usuario: " . $e->getMessage());
            return 'Desconocido';
        }
    }
}
?>