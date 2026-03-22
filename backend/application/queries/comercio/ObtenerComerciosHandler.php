<?php
/**
 * Manejador del query para obtener comercios
 * 
 * @package Application\Queries\Comercio
 * @author Tu Nombre
 * @version 1.0.0
 */

namespace Application\Queries\Comercio;

use Domain\Comercio\ComercioRepository;

/**
 * @package Application\Queries\Comercio
 * 
 * Manejador responsable de obtener la lista de comercios
 * aplicando filtros y paginación.
 */
class ObtenerComerciosHandler {
    
    /**
     * @var ComercioRepository Repositorio de comercios
     */
    private $comercioRepository;
    
    /**
     * Constructor del manejador
     * 
     * @param ComercioRepository $comercioRepository
     */
    public function __construct(ComercioRepository $comercioRepository) {
        $this->comercioRepository = $comercioRepository;
    }
    
    /**
     * Maneja el query de obtener comercios
     * 
     * @param ObtenerComerciosQuery $query Query con filtros
     * @return array Resultado con comercios y metadatos de paginación
     */
    public function handle(ObtenerComerciosQuery $query): array {
        
        // Obtener comercios paginados
        $comercios = $this->comercioRepository->findAll(
            $query->getFiltros(),
            $query->getOffset(),
            $query->getPorPagina(),
            $query->getOrdenarPor(),
            $query->getDireccion()
        );
        
        // Obtener total de registros para paginación
        $total = $this->comercioRepository->count($query->getFiltros());
        
        // Formatear resultado
        $items = array_map(function($comercio) {
            return [
                'id' => $comercio->getId(),
                'nombre' => $comercio->getNombre(),
                'tipo' => $comercio->getTipo(),
                'direccion' => $comercio->getDireccion(),
                'telefono' => $comercio->getTelefono(),
                'cantidad_maquinas' => $comercio->getCantidadMaquinas(),
                'fecha_registro' => $comercio->getFechaRegistro()
            ];
        }, $comercios);
        
        return [
            'success' => true,
            'data' => $items,
            'meta' => [
                'total' => $total,
                'pagina' => $query->getPagina(),
                'por_pagina' => $query->getPorPagina(),
                'total_paginas' => ceil($total / $query->getPorPagina())
            ]
        ];
    }
}