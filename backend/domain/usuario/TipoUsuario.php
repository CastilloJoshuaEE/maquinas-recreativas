<?php

declare(strict_types=1);

namespace maquinas_recreativas\Domain\Usuario;

/**
 * Enum-like class para los tipos de usuario del sistema.
 * 
 * @package maquinas_recreativas\Domain\Usuario
 * @version 1.0
 */
final class TipoUsuario
{
    public const ADMINISTRADOR = 'Administrador';
    public const TECNICO = 'Tecnico';
    public const LOGISTICA = 'Logistica';
    public const CONTABILIDAD = 'Contabilidad';
    public const USUARIO = 'Usuario';
    private const VALID_TIPOS=[
        self::ADMINISTRADOR,
        self::TECNICO,
        self::LOGISTICA,
        self::CONTABILIDAD,
        self::USUARIO,
    ];
    private string $value;
        /**
     * Constructor de TipoUsuario.
     *
     * @param string $tipo Tipo de usuario.
     * @throws \InvalidArgumentException Si el tipo no es válido.
     */
    public function __construct(string $tipo){
        if(!in_array($tipo, self::VALID_TIPOS, true)){
            throw new \InvalidArgumentException("Tipo de usuario '{$tipo}'no es válido.");
            
        }
        $this->value = $tipo;
    }
    /**
     * Constructor de TipoUsuario.
     *
     * @param string $tipo Tipo de usuario.
     * @throws \InvalidArgumentException Si el tipo no es válido.
     */
    public function value():string{
        return $this->value;
    }    
    /**
     * Compara si este TipoUsuario es igual a otro.
     *
     * @param TipoUsuario $other
     * @return bool
     */
    public function equals(TipoUsuario $other):bool{
        return $this->value() === $other->value();
    }    
    /**
     * Retorna el valor como string.
     *
     * @return string
     */
    public function __toString():string{
        return $this->value();
    }
    /**
     * Obtiene todos los tipos de usuario válidos.
     *
     * @return array<string>
     */    
    public static function validValues():array{
        return self::VALID_TIPOS;
    }
    /**
     * Verifica si el tipo de usuario puede gestionar técnicos.
     *
     * @return bool
     */
    public function canManageTecnicos():bool{
        return $this->value() === self::ADMINISTRADOR;  
    }    
    /**
     * Verifica si el tipo de usuario es un tipo técnico.
     *
     * @return bool
     */
    public function isTecnico():bool{
        return $this->value() === self::TECNICO;
    }    
    /**
     * Verifica si el tipo de usuario pertenece a logística.
     *
     * @return bool
     */
    public function isLogistica(): bool
    {
        return $this->value === self::LOGISTICA;
    }   
    /**
     * Verifica si el tipo de usuario pertenece a contabilidad.
     *
     * @return bool
     */
    public function isContabilidad():bool{
        return $this->value() === self::CONTABILIDAD;   
    } 
    /**
     * Verifica si el tipo de usuario es administrador.
     *
     * @return bool
     */
    public function isAdministrador():bool{
        return $this->value() === self::ADMINISTRADOR;
    }    

}