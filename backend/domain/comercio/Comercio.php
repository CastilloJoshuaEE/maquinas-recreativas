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

    /**
     * Constructor privado - usar método estático crear()
     */
    private function __construct(
        string $id,
        string $nombre,
        string $tipo,
        string $direccion,
        string $telefono,
        int $cantidadMaquinas = 0,
        ?string $fechaRegistro = null
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->tipo = $tipo;
        $this->direccion = $direccion;
        $this->telefono = $telefono;
        $this->cantidadMaquinas = $cantidadMaquinas;
        $this->fechaRegistro = $fechaRegistro ?? date('Y-m-d H:i:s');
    }

    /**
     * Crea un nuevo comercio
     */
    public static function crear(
        Uuid $id,
        string $nombre,
        string $tipo,
        string $direccion,
        string $telefono
    ): self {
        return new self(
            $id->value(),
            $nombre,
            $tipo,
            $direccion,
            $telefono
        );
    }

    /**
     *  Reconstruye un comercio desde datos persistidos
     * Los datos de la base de datos vienen con mayúsculas: Nombre, Tipo, Direccion, Telefono
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['ID_Comercio'],
            $data['Nombre'] ?? $data['nombre'] ?? '',           //  Priorizar mayúscula
            $data['Tipo'] ?? $data['tipo'] ?? '',               //  Priorizar mayúscula
            $data['Direccion'] ?? $data['direccion'] ?? '',     //  Priorizar mayúscula
            $data['Telefono'] ?? $data['telefono'] ?? '',       //  Priorizar mayúscula
            (int)($data['cantidad_maquinas'] ?? $data['Cantidad_Maquinas'] ?? 0),
            $data['fecha_registro'] ?? $data['Fecha_Registro'] ?? null
        );
    }

    public function getId(): string { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getTipo(): string { return $this->tipo; }
    public function getDireccion(): string { return $this->direccion; }
    public function getTelefono(): string { return $this->telefono; }
    public function getCantidadMaquinas(): int { return $this->cantidadMaquinas; }
    public function getFechaRegistro(): string { return $this->fechaRegistro; }

    public function incrementarMaquinas(): void { 
        $this->cantidadMaquinas++; 
    }
    
    public function decrementarMaquinas(): void { 
        if ($this->cantidadMaquinas > 0) {
            $this->cantidadMaquinas--;
        }
    }
    
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