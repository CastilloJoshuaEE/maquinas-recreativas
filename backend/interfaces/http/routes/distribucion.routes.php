<?php
/**
 * maquinas_recreativas - Distribucion Routes
 * 
 * Rutas para gestión de distribución.
 * 
 * @package maquinas_recreativas\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Obtener informes de distribución
    [
        'method' => 'GET',
        'path' => '/distribucion/informes',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controller\DistribucionController::class, 'obtenerInformesDistribucion'],
        'middleware' => []
    ]
];