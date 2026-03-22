<?php
/**
 * Query para obtener lista de comercios
 * 
 * @package Application\Queries\Comercio
 * @author Tu Nombre
 * @version 1.0.0
 */

namespace Application\Queries\Comercio;

/**
 * @package Application\Queries\Comercio
 * 
 * Query que encapsula los filtros para obtener comercios
 */
class ObtenerComerciosQuery {
    
    /**
     * @var array Filtros de búsqueda
     */
    private $filtros;
    
    /**
     * @var int Página actual
     */
    private $pagina;
    
    /**
     * @var int Items por página
     */
    private $porPagina;
    
    /**
     * @var string Campo de ordenamiento
     */
    private $ordenarPor;
    
    /**
     * @var string Dirección de ordenamiento (ASC/DESC)
     */
    private $direccion;
    
    /**
     * Constructor del query
     * 
     * @param array $filtros Filtros de búsqueda
     * @param int $pagina Página actual
     * @param int $porPagina Items por página
     * @param string $ordenarPor Campo de ordenamiento
     * @param string $direccion Dirección de ordenamiento
     */
    public function __construct(
        array $filtros = [],
        int $pagina = 1,
        int $porPagina = 10,
        string $ordenarPor = 'nombre',
        string $direccion = 'ASC'
    ) {
        $this->filtros = $filtros;
        $this->pagina = max(1, $pagina);
        $this->porPagina = min(100, max(1, $porPagina));
        $this->ordenarPor = $ordenarPor;
        $this->direccion = strtoupper($direccion) === 'DESC' ? 'DESC' : 'ASC';
    }
    
    /**
     * Obtiene los filtros
     * 
     * @return array
     */
    public function getFiltros(): array {
        return $this->filtros;
    }
    
    /**
     * Obtiene la página actual
     * 
     * @return int
     */
    public function getPagina(): int {
        return $this->pagina;
    }
    
    /**
     * Obtiene el límite de items
     * 
     * @return int
     */
    public function getPorPagina(): int {
        return $this->porPagina;
    }
    
    /**
     * Obtiene el offset para la consulta
     * 
     * @return int
     */
    public function getOffset(): int {
        return ($this->pagina - 1) * $this->porPagina;
    }
    
    /**
     * Obtiene el campo de ordenamiento
     * 
     * @return string
     */
    public function getOrdenarPor(): string {
        return $this->ordenarPor;
    }
    
    /**
     * Obtiene la dirección de ordenamiento
     * 
     * @return string
     */
    public function getDireccion(): string {
        return $this->direccion;
    }
}