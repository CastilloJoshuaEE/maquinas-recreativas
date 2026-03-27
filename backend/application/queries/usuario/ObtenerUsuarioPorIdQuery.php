<?php
/**
 * Query para obtener un usuario por su ID
 * 
 * @package Application\Queries\Usuario
 * @author Tu Nombre
 * @version 1.0.0
 */

namespace maquinas_recreativas\Application\Queries\Usuario;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

/**
 * @package Application\Queries\Usuario
 * 
 * Query que encapsula el ID del usuario a buscar
 */
class ObtenerUsuarioPorIdQuery {
    
    /**
     * @var Uuid ID del usuario
     */
    private $usuarioId;
    
    /**
     * @var bool Si se deben incluir datos sensibles
     */
    private $includeSensitive;
    
    /**
     * Constructor del query
     * 
     * @param string|Uuid $usuarioId ID del usuario
     * @param bool $includeSensitive Incluir datos sensibles (default: false)
     * @throws \InvalidArgumentException Si el ID no es válido
     */
    public function __construct($usuarioId, bool $includeSensitive = false) {
        if (is_string($usuarioId)) {
            $this->usuarioId = new Uuid($usuarioId);
        } elseif ($usuarioId instanceof Uuid) {
            $this->usuarioId = $usuarioId;
        } else {
            throw new \InvalidArgumentException('ID de usuario inválido');
        }
        
        $this->includeSensitive = $includeSensitive;
    }
    
    /**
     * Obtiene el ID del usuario
     * 
     * @return Uuid
     */
    public function getUsuarioId(): Uuid {
        return $this->usuarioId;
    }
    
    /**
     * Indica si se deben incluir datos sensibles
     * 
     * @return bool
     */
    public function shouldIncludeSensitive(): bool {
        return $this->includeSensitive;
    }
}