<?php
/**
 * Domain/Comercio/Comercio.php
 */

namespace maquinas_recreativas\Domain\Comercio;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

class Comercio
{
    private string $id;
    private string $nombre;
    private string $tipo;
    private string $direccion;
    private string $telefono;
    private int $cantidadMaquinas;
    private string $fechaRegistro;

    public function __construct(string $nombre, string $tipo, string $direccion, string $telefono)
    {
        $this->id = Uuid::random()->value();
        $this->nombre = $nombre;
        $this->tipo = $tipo;
        $this->direccion = $direccion;
        $this->telefono = $telefono;
        $this->cantidadMaquinas = 0;
        $this->fechaRegistro = date('Y-m-d H:i:s');
    }

    public function getId(): string { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getTipo(): string { return $this->tipo; }
    public function getDireccion(): string { return $this->direccion; }
    public function getTelefono(): string { return $this->telefono; }
    public function getCantidadMaquinas(): int { return $this->cantidadMaquinas; }
    public function getFechaRegistro(): string { return $this->fechaRegistro; }

    public function incrementarMaquinas(): void { $this->cantidadMaquinas++; }
    public function decrementarMaquinas(): void { if ($this->cantidadMaquinas > 0) $this->cantidadMaquinas--; }
    
    public function actualizar(string $nombre, string $tipo, string $direccion, string $telefono): void
    {
        $this->nombre = $nombre;
        $this->tipo = $tipo;
        $this->direccion = $direccion;
        $this->telefono = $telefono;
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'tipo' => $this->tipo,
            'direccion' => $this->direccion,
            'telefono' => $this->telefono,
            'cantidad_maquinas' => $this->cantidadMaquinas,
            'fecha_registro' => $this->fechaRegistro
        ];
    }
}