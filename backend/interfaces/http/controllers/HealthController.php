<?php
/**
 * interfaces/http/controllers/HealthController.php
 */

namespace maquinas_recreativas\Interfaces\Http\Controllers;

use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class HealthController
{
    public function check(Request $request): Response
    {
        return (new Response())->json([
            'success' => true,
            'status' => 'ok',
            'message' => 'API de Máquinas Recreativas funcionando correctamente',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0'
        ]);
    }
    
    public function testDb(Request $request): Response
    {
        try {
            $db = new \maquinas_recreativas\Infrastructure\Database\Database();
            $conn = $db->getConnection();
            
            // Probar la conexión con una consulta simple
            $result = $conn->query("SELECT 1 as test");
            
            if ($result) {
                return (new Response())->json([
                    'success' => true,
                    'message' => 'Conexión a base de datos exitosa',
                    'database' => DB_NAME ?? 'unknown'
                ]);
            } else {
                return (new Response())->json([
                    'success' => false,
                    'message' => 'Error en la consulta de prueba'
                ], 500);
            }
        } catch (\Exception $e) {
            return (new Response())->json([
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage()
            ], 500);
        }
    }
}