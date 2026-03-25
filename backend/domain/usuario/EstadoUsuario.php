<?php

declare(strict_types=1);

namespace maquinas_recreativas\Domain\Usuario;

/**
 * Enum-like class para los estados de un usuario.
 * 
 * @package maquinas_recreativas\Domain\Usuario
 * @version 1.0
 */
final class EstadoUsuario
{
    private const ACTIVO = 'Activo';
    private const INACTIVO = 'Inactivo';
    private const BLOQUEADO = 'Bloqueado';
    private const VALID_ESTADOS = [
        self::ACTIVO,
        self::INACTIVO,
        self::BLOQUEADO,
    ];
    private string $value;
    /**
     * Constructor de EstadoUsuario.
     *
     * @param string $estado Estado del usuario.
     * @throws \InvalidArgumentException Si el estado no es válido.
     */
    public function __construct(string $estado){
        if(!in_array($estado, self::VALID_ESTADOS, true)){
            throw new \InvalidArgumentException("Estado de usuario '{$estado}' no es válido.");
        }
        $this->value = $estado;
    }    
    /**
     * Obtiene el valor del estado.
     *
     * @return string
     */
    public function value():string{
        return $this->value;
    }
     /**
     * Compara si este EstadoUsuario es igual a otro.
     *
     * @param EstadoUsuario $other
     * @return bool
     */
    public function equals(EstadoUsuario $other):bool{
        return $this->value() === $other->value();
    }   
    /**
     * Retorna el valor como string.
     *
     * @return string
     */
    public function __toString(): string{
        return $this->value();
    }
    /**
     * Obtiene todos los estados válidos.
     *
     * @return array<string>
     */
    public static function validValues():array{
        return self::VALID_ESTADOS;
    }    
    /**
     * Verifica si el estado es 'Activo'.
     *
     * @return bool
     */
    public function isActivo():bool{
        return $this->value===self::ACTIVO;
    }    
    /**
     * Verifica si el estado es 'Inactivo'.
     *
     * @return bool
     */
    public function isInactivo():bool{
        return $this->value===self::INACTIVO;
    }    
    /**
     * Verifica si el estado es 'Bloqueado'.
     *
     * @return bool
     */
    public function isBloqueado(): bool
    {
        return $this->value === self::BLOQUEADO;
    }


}