<?php
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Attributes as OA;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class HealthController
{
    #[OA\Get(
        path: "/v1/health",
        summary: "Verificar estado de la API",
        tags: ["Health"],
        responses: [
            new OA\Response(
                response: 200,
                description: "API funcionando correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "status", type: "string", example: "ok"),
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(property: "timestamp", type: "string", format: "date-time"),
                        new OA\Property(property: "version", type: "string", example: "1.0.0")
                    ]
                )
            )
        ]
    )]
    public function check(Request $request): Response
    {
        return (new Response())->json([
            'success'   => true,
            'status'    => 'ok',
            'message'   => 'API de Máquinas Recreativas funcionando correctamente',
            'timestamp' => date('Y-m-d H:i:s'),
            'version'   => '1.0.0',
        ]);
    }

    #[OA\Get(
        path: "/v1/health/db",
        summary: "Verificar conexión a la base de datos",
        tags: ["Health"],
        responses: [
            new OA\Response(response: 200, description: "Conexión exitosa"),
            new OA\Response(response: 500, description: "Error de conexión")
        ]
    )]
    public function testDb(Request $request): Response
    {
        try {
            $db     = new \maquinas_recreativas\Infrastructure\Database\Database();
            $conn   = $db->getConnection();
            $result = $conn->query("SELECT 1 as test");

            if ($result) {
                return (new Response())->json(['success' => true, 'message' => 'Conexión a base de datos exitosa', 'database' => DB_NAME ?? 'unknown']);
            }

            return (new Response())->json(['success' => false, 'message' => 'Error en la consulta de prueba'], 500);
        } catch (\Exception $e) {
            return (new Response())->json(['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()], 500);
        }
    }
}