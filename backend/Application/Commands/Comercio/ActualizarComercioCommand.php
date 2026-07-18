<?php

declare(strict_types=1);

namespace maquinas_recreativas\Application\Commands\Comercio;

use maquinas_recreativas\Application\Commands\Command;

/**
 * Comando para actualizar un comercio existente
 */
final class ActualizarComercioCommand implements Command
{
    private string $id;
    private string $nombre;
    private string $tipo;
    private string $direccion;
    private string $telefono;

    public function __construct(
        string $id,
        string $nombre,
        string $tipo,
        string $direccion,
        string $telefono
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->tipo = $tipo;
        $this->direccion = $direccion;
        $this->telefono = $telefono;
    }

    public function getId(): string { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getTipo(): string { return $this->tipo; }
    public function getDireccion(): string { return $this->direccion; }
    public function getTelefono(): string { return $this->telefono; }
}