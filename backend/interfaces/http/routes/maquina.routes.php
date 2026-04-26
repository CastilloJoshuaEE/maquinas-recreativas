<?php
/**
 * maquinas_recreativas - Maquina Routes
 * 
 * Rutas para gestión de máquinas recreativas.
 * 
 * @package maquinas_recreativas\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Registrar máquina
    [
        'method' => 'POST',
        'path' => '/maquina/register',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\MaquinaController::class, 'register'],
        'middleware' => []
    ],

    // Generar placa
    [
        'method' => 'POST',
        'path' => '/maquina/generar-placa',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\MaquinaController::class, 'generarPlaca'],
        'middleware' => []
    ],
    
    // Registrar montaje
    [
        'method' => 'POST',
        'path' => '/maquina/registrar-montaje',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\MaquinaController::class, 'registrarMontaje'],
        'middleware' => []
    ],
    
    // Mandar a comprobación
    [
        'method' => 'POST',
        'path' => '/maquina/mandar-comprobacion',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\MaquinaController::class, 'mandarAComprobacion'],
        'middleware' => []
    ],
    
    // Mandar a reensamblar
    [
        'method' => 'POST',
        'path' => '/maquina/mandar-reensamblar',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\MaquinaController::class, 'mandarAReensamblar'],
        'middleware' => []
    ],
    
    // Mandar a distribución
    [
        'method' => 'POST',
        'path' => '/maquina/mandar-distribucion',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\MaquinaController::class, 'mandarADistribucion'],
        'middleware' => []
    ],
    
    // Poner operativa
    [
        'method' => 'POST',
        'path' => '/maquina/poner-operativa',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\MaquinaController::class, 'ponerOperativa'],
        'middleware' => []
    ],
    
    // Dar mantenimiento
    [
        'method' => 'POST',
        'path' => '/maquina/dar-mantenimiento',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\MaquinaController::class, 'darMantenimiento'],
        'middleware' => []
    ],
    
    // Finalizar mantenimiento
    [
        'method' => 'POST',
        'path' => '/maquina/finalizar-mantenimiento',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\MaquinaController::class, 'finalizarMantenimiento'],
        'middleware' => []
    ],
    
    // Obtener máquinas por técnico ensamblador
    [
        'method' => 'GET',
        'path' => '/maquina/ensamblador/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\MaquinaController::class, 'obtenerPorTecnicoEnsamblador'],
        'middleware' => []
    ],
    
    // Obtener máquinas por técnico comprobador
    [
        'method' => 'GET',
        'path' => '/maquina/comprobador/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\MaquinaController::class, 'obtenerPorTecnicoComprobador'],
        'middleware' => []
    ],
    
    // Obtener máquinas por técnico mantenimiento
    [
        'method' => 'GET',
        'path' => '/maquina/mantenimiento/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\MaquinaController::class, 'obtenerPorTecnicoMantenimiento'],
        'middleware' => []
    ],
        [
    'method' => 'GET',
    'path' => '/maquina/all',
    'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\MaquinaController::class, 'obtenerTodas'],
    'middleware' => []
],    
    // Obtener máquinas por estado
[
    'method' => 'GET',
    'path' => '/maquina/estado/:estado',   
    'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\MaquinaController::class, 'obtenerPorEstado'],
    'middleware' => []
],
    
    // Obtener máquinas por etapa
    [
        'method' => 'GET',
        'path' => '/maquina/etapa/:slug',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\MaquinaController::class, 'obtenerPorEtapa'],
        'middleware' => []
    ],
    
    // Obtener máquinas para distribución
    [
        'method' => 'GET',
        'path' => '/maquina/distribucion',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\MaquinaController::class, 'obtenerMaquinasParaDistribucion'],
        'middleware' => []
    ],
    
    // Obtener componentes de máquina
    [
        'method' => 'GET',
        'path' => '/maquina/componentes/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\MaquinaController::class, 'obtenerComponentesPorMaquina'],
        'middleware' => []
    ]

];