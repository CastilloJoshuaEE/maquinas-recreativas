<?php
/**
 * interfaces/http/routes/index.php
 */

return [
    // Health check
    [
        'method' => 'GET',
        'path' => '/health',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\HealthController::class, 'check'],
        'middleware' => []
    ],
    
    // Test DB
    [
        'method' => 'GET',
        'path' => '/test-db',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\HealthController::class, 'testDb'],
        'middleware' => []
    ],
 
];