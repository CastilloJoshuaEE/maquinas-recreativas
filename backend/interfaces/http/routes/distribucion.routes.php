<?php
/**
 * RecreaSys - Distribucion Routes
 * 
 * Rutas para gestión de distribución.
 * 
 * @package RecreaSys\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Obtener informes de distribución
    [
        'method' => 'GET',
        'path' => '/distribucion/informes',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\DistribucionController::class, 'obtenerInformesDistribucion'],
        'middleware' => []
    ]
];