<?php

declare(strict_types=1);

namespace maquinas_recreativas\Domain\Usuario;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\ValueObjects\Email;
use InvalidArgumentException;

/**
 * Entidad de dominio que representa a un Técnico (extiende Usuario).
 * 
 * @package maquinas_recreativas\Domain\Usuario
 * @version 1.0
 */
final class Tecnico extends Usuario
{
    private string $especialidad;
    private int $cantidadActividades;
    public const ESPECIALIDAD_ENSAMBLADOR = 'Ensamblador';
    public const ESPECIALIDAD_COMPROBADOR = 'Comprobador';
    public const ESPECIALIDAD_MANTENIMIENTO = 'Mantenimiento';
    private const VALID_ESPECIALIDADES = [
        self::ESPECIALIDAD_ENSAMBLADOR,
        self::ESPECIALIDAD_COMPROBADOR,
        self::ESPECIALIDAD_MANTENIMIENTO,
    ];
    /**
     * Constructor de la entidad Técnico.
     *
     * @param Uuid $id
     * @param string $nombre
     * @param string $apellido
     * @param string $ci
     * @param Email $email
     * @param string $usuarioAsignado
     * @param string $contrasenaHash
     * @param EstadoUsuario $estado
     * @param string $especialidad
     * @param int $cantidadActividades
     * @throws InvalidArgumentException
     */
    public function __construct(
        Uuid $id,
        string $nombre,
        string $apellido,
        string $ci,
        Email $email,
        string $usuarioAsignado,
        string $contrasenaHash,
        EstadoUsuario $estado,
        string $especialidad,
        int $cantidadActividades = 0
    ) {
        parent::__construct(
            $id,
            $nombre,
            $apellido,
            $ci,
            $email,
            $usuarioAsignado,
            $contrasenaHash,
            new TipoUsuario(TipoUsuario::TECNICO),
            $estado
        );

        $this->setEspecialidad($especialidad);
        $this->cantidadActividades = max(0, $cantidadActividades);
    }
    /**
     * Valida y asigna la especialidad.
     *
     * @param string $especialidad
     * @return void
     * @throws InvalidArgumentException
     */        
    private function setEspecialidad(string $especialidad): void{
        if(!in_array($especialidad, self:: VALID_ESPECIALIDADES, true)){
            throw new InvalidArgumentException("Especialidad '{$especialidad}' no válida. Valores permitidos:".implode(',', self::VALID_ESPECIALIDADES));
        }
        $this->especialidad = $especialidad;
    }
    /**
     * Obtiene la especialidad del técnico.
     *
     * @return string
     */
    public function getEspecialidad(): string{
        return $this->especialidad;
    }    
    /**
     * Obtiene la cantidad de actividades realizadas.
     *
     * @return int
     */
    public function getCantidadActividades():int{
        return $this->cantidadActividades;
    }    
    /**
     * Incrementa en uno la cantidad de actividades.
     *
     * @return void
     */
    public function incrementarActividades():void{
        $this->cantidadActividades++;
    }    
    /**
     * Verifica si el técnico es ensamblador.
     *
     * @return bool
     */    
    public function esEnsamblador():bool{
        return $this->especialidad === self::ESPECIALIDAD_ENSAMBLADOR;
    }
    /**
     * Verifica si el técnico es comprobador.
     *
     * @return bool
     */
    public function esComprobador():bool{
        return $this->especialidad=== self::ESPECIALIDAD_COMPROBADOR;
    }    
    /**
     * Verifica si el técnico es de mantenimiento.
     *
     * @return bool
     */
    public function esMantenimiento():bool{
        return $this->especialidad === self::ESPECIALIDAD_MANTENIMIENTO;
    }
    /**
     * Convierte la entidad a un array para persistencia o respuesta.
     *
     * @return array
     */ 
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['especialidad'] = $this->especialidad;
        $data['cantidad_actividades'] = $this->cantidadActividades;
        return $data;
    }           
}