<?php
return [
    [
        'method' => 'GET',
        'path' => '/health',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\HealthController::class, 'check'],
        'middleware' => []
    ],
    [
        'method' => 'GET',
        'path' => '/test-db',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\HealthController::class, 'testDb'],
        'middleware' => []
    ],
];