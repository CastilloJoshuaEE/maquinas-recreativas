<?php
/**
 * Manejador del query para obtener comercios
 * 
 * @package Application\Queries\Comercio
 * @author Tu Nombre
 * @version 1.0.0
 */

namespace maquinas_recreativas\Application\Queries\Comercio;

use maquinas_recreativas\Application\Queries\Query;
use maquinas_recreativas\Application\Queries\QueryHandler;
use maquinas_recreativas\Domain\Comercio\ComercioRepository;

class ObtenerComerciosHandler implements QueryHandler
{
    private ComercioRepository $comercioRepository;
    
    public function __construct(ComercioRepository $comercioRepository) 
    {
        $this->comercioRepository = $comercioRepository;
    }
    
    public function handle(Query $query): array
    {
        if (!$query instanceof ObtenerComerciosQuery) {
            throw new \InvalidArgumentException('Query inválido para este handler');
        }
        
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