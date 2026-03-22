<?php
/**
 * Controlador HTTP para operaciones de comercios
 * 
 * @package Interfaces\Http\Controllers
 * @author Tu Nombre
 * @version 2.0.0 (DDD + CQRS)
 */

namespace Interfaces\Http\Controllers;

use Application\Commands\Comercio\RegistrarComercioCommand;
use Application\Commands\Comercio\RegistrarComercioHandler;
use Application\Queries\Comercio\ObtenerComerciosQuery;
use Application\Queries\Comercio\ObtenerComerciosHandler;
use Domain\Shared\Exceptions\DomainException;
use Infrastructure\Security\ValidationHelper;

/**
 * @package Interfaces\Http\Controllers
 * 
 * Controlador que maneja las peticiones HTTP relacionadas con comercios.
 * Implementa el patrón CQRS separando comandos (escritura) de queries (lectura).
 */
class ComercioController {
    
    /**
     * @var ObtenerComerciosHandler
     */
    private $obtenerComerciosHandler;
    
    /**
     * @var RegistrarComercioHandler
     */
    private $registrarComercioHandler;
    
    /**
     * Constructor con inyección de dependencias
     * 
     * @param ObtenerComerciosHandler $obtenerComerciosHandler
     * @param RegistrarComercioHandler $registrarComercioHandler
     */
    public function __construct(
        ObtenerComerciosHandler $obtenerComerciosHandler,
        RegistrarComercioHandler $registrarComercioHandler
    ) {
        $this->obtenerComerciosHandler = $obtenerComerciosHandler;
        $this->registrarComercioHandler = $registrarComercioHandler;
    }
    
    /**
     * Registra un nuevo comercio
     * 
     * @route POST /comercio/register
     * @return void
     */
    public function register(): void {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new DomainException(
                    'Datos JSON inválidos',
                    DomainException::HTTP_BAD_REQUEST
                );
            }
            
            // Validar campos requeridos
            $required = ['nombre', 'tipo', 'direccion', 'telefono'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new DomainException(
                        "El campo {$field} es requerido",
                        DomainException::HTTP_BAD_REQUEST
                    );
                }
            }
            
            // Validar tipo de comercio
            $tiposPermitidos = ['Minorista', 'Mayorista'];
            if (!in_array($data['tipo'], $tiposPermitidos)) {
                throw new DomainException(
                    'Tipo de comercio no válido. Debe ser Minorista o Mayorista',
                    DomainException::HTTP_BAD_REQUEST
                );
            }
            
            // Validar teléfono (solo números)
            if (!preg_match('/^[0-9+\-\s]+$/', $data['telefono'])) {
                throw new DomainException(
                    'Formato de teléfono inválido',
                    DomainException::HTTP_BAD_REQUEST
                );
            }
            
            // Sanitizar entrada
            $data = ValidationHelper::sanitizeInput($data);
            
            // Crear comando
            $command = new RegistrarComercioCommand(
                $data['nombre'],
                $data['tipo'],
                $data['direccion'],
                $data['telefono']
            );
            
            // Ejecutar handler
            $result = $this->registrarComercioHandler->handle($command);
            
            $this->sendResponse([
                'success' => true,
                'message' => 'Comercio registrado correctamente',
                'idComercio' => $result['id']
            ], 201);
            
        } catch (DomainException $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getHttpCode());
        } catch (\Exception $e) {
            error_log("Error en register comercio: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }
    
    /**
     * Obtiene lista de comercios con filtros opcionales
     * 
     * @route GET /comercio/all
     * @return void
     */
    public function obtenerComercios(): void {
        try {
            // Construir filtros desde query params
            $filtros = [];
            
            if (!empty($_GET['nombre'])) {
                $filtros['nombre'] = ValidationHelper::sanitizeInput($_GET['nombre']);
            }
            
            if (!empty($_GET['tipo'])) {
                $tipo = $_GET['tipo'];
                if (!in_array($tipo, ['Minorista', 'Mayorista'])) {
                    throw new DomainException(
                        'Tipo de comercio no válido',
                        DomainException::HTTP_BAD_REQUEST
                    );
                }
                $filtros['tipo'] = $tipo;
            }
            
            // Parámetros de paginación
            $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : ITEMS_POR_PAGINA;
            
            // Validar límites
            if ($porPagina > MAX_ITEMS_POR_PAGINA) {
                $porPagina = MAX_ITEMS_POR_PAGINA;
            }
            
            // Crear query
            $query = new ObtenerComerciosQuery(
                $filtros,
                $pagina,
                $porPagina,
                $_GET['ordenar_por'] ?? 'nombre',
                $_GET['direccion'] ?? 'ASC'
            );
            
            // Ejecutar handler
            $result = $this->obtenerComerciosHandler->handle($query);
            
            $this->sendResponse($result);
            
        } catch (DomainException $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getHttpCode());
        } catch (\Exception $e) {
            error_log("Error en obtenerComercios: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Error al obtener comercios'
            ], 500);
        }
    }
    
    /**
     * Obtiene un comercio específico por ID
     * 
     * @route GET /comercio/{id}
     * @param string $id ID del comercio
     * @return void
     */
    public function obtenerComercioPorId(string $id): void {
        try {
            if (!ValidationHelper::isValidUUID($id)) {
                throw new DomainException(
                    'ID de comercio inválido',
                    DomainException::HTTP_BAD_REQUEST
                );
            }
            
            // Crear query con filtro por ID
            $query = new ObtenerComerciosQuery(
                ['id' => $id],
                1,
                1
            );
            
            $result = $this->obtenerComerciosHandler->handle($query);
            
            if (empty($result['data'])) {
                throw new DomainException(
                    'Comercio no encontrado',
                    DomainException::HTTP_NOT_FOUND
                );
            }
            
            $this->sendResponse([
                'success' => true,
                'comercio' => $result['data'][0]
            ]);
            
        } catch (DomainException $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getHttpCode());
        } catch (\Exception $e) {
            error_log("Error en obtenerComercioPorId: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Error al obtener comercio'
            ], 500);
        }
    }
    
    /**
     * Envía respuesta JSON al cliente
     * 
     * @param mixed $data Datos a enviar
     * @param int $statusCode Código HTTP
     * @return void
     */
    private function sendResponse($data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }
}