<?php
/**
 * RecreaSys - Maquina Routes
 * 
 * Rutas para gestión de máquinas recreativas.
 * 
 * @package RecreaSys\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Registrar máquina
    [
        'method' => 'POST',
        'path' => '/maquina/register',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\MaquinaController::class, 'register'],
        'middleware' => []
    ],
    
    // Generar placa
    [
        'method' => 'POST',
        'path' => '/maquina/generar-placa',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\MaquinaController::class, 'generarPlaca'],
        'middleware' => []
    ],
    
    // Registrar montaje
    [
        'method' => 'POST',
        'path' => '/maquina/registrar-montaje',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\MaquinaController::class, 'registrarMontaje'],
        'middleware' => []
    ],
    
    // Mandar a comprobación
    [
        'method' => 'POST',
        'path' => '/maquina/mandar-comprobacion',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\MaquinaController::class, 'mandarAComprobacion'],
        'middleware' => []
    ],
    
    // Mandar a reensamblar
    [
        'method' => 'POST',
        'path' => '/maquina/mandar-reensamblar',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\MaquinaController::class, 'mandarAReensamblar'],
        'middleware' => []
    ],
    
    // Mandar a distribución
    [
        'method' => 'POST',
        'path' => '/maquina/mandar-distribucion',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\MaquinaController::class, 'mandarADistribucion'],
        'middleware' => []
    ],
    
    // Poner operativa
    [
        'method' => 'POST',
        'path' => '/maquina/poner-operativa',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\MaquinaController::class, 'ponerOperativa'],
        'middleware' => []
    ],
    
    // Dar mantenimiento
    [
        'method' => 'POST',
        'path' => '/maquina/dar-mantenimiento',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\MaquinaController::class, 'darMantenimiento'],
        'middleware' => []
    ],
    
    // Finalizar mantenimiento
    [
        'method' => 'POST',
        'path' => '/maquina/finalizar-mantenimiento',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\MaquinaController::class, 'finalizarMantenimiento'],
        'middleware' => []
    ],
    
    // Obtener máquinas por técnico ensamblador
    [
        'method' => 'GET',
        'path' => '/maquina/ensamblador/:uuid',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\MaquinaController::class, 'obtenerPorTecnicoEnsamblador'],
        'middleware' => []
    ],
    
    // Obtener máquinas por técnico comprobador
    [
        'method' => 'GET',
        'path' => '/maquina/comprobador/:uuid',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\MaquinaController::class, 'obtenerPorTecnicoComprobador'],
        'middleware' => []
    ],
    
    // Obtener máquinas por técnico mantenimiento
    [
        'method' => 'GET',
        'path' => '/maquina/mantenimiento/:uuid',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\MaquinaController::class, 'obtenerPorTecnicoMantenimiento'],
        'middleware' => []
    ],
    
    // Obtener máquinas por estado
    [
        'method' => 'GET',
        'path' => '/maquina/estado/:slug',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\MaquinaController::class, 'obtenerPorEstado'],
        'middleware' => []
    ],
    
    // Obtener máquinas por etapa
    [
        'method' => 'GET',
        'path' => '/maquina/etapa/:slug',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\MaquinaController::class, 'obtenerPorEtapa'],
        'middleware' => []
    ],
    
    // Obtener máquinas para distribución
    [
        'method' => 'GET',
        'path' => '/maquina/distribucion',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\MaquinaController::class, 'obtenerMaquinasParaDistribucion'],
        'middleware' => []
    ],
    
    // Obtener componentes de máquina
    [
        'method' => 'GET',
        'path' => '/maquina/componentes/:uuid',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\MaquinaController::class, 'obtenerComponentesPorMaquina'],
        'middleware' => []
    ]
];