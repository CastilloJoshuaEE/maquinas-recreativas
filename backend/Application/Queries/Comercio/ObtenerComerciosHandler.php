<?php
/**
 * Manejador del query para obtener comercios
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
        
        //  Obtener todos los comercios sin paginación para el select
        $comercios = $this->comercioRepository->obtenerTodos();
        
        //  Formatear resultado como array simple
        $items = [];
        foreach ($comercios as $comercio) {
            $items[] = [
                'ID_Comercio' => $comercio->getId(),
                'nombre' => $comercio->getNombre(),
                'tipo' => $comercio->getTipo(),
                'direccion' => $comercio->getDireccion(),
                'telefono' => $comercio->getTelefono()
            ];
        }
        
        //  Retornar directamente el array de comercios
        return $items;
    }
}