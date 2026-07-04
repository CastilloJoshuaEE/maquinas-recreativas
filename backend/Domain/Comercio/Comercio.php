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
        // Extraer el ID de forma explícita, soportando ambos casos
        $id = $data['ID_Comercio'] ?? $data['id'] ?? null;
        if ($id === null) {
            throw new \InvalidArgumentException('El ID del comercio es obligatorio para reconstruir la entidad.');
        }

        // Extraer el resto de campos, usando las claves en mayúscula de la DB como prioridad
        $nombre = $data['Nombre'] ?? $data['nombre'] ?? '';
        $tipo = $data['Tipo'] ?? $data['tipo'] ?? '';
        $direccion = $data['Direccion'] ?? $data['direccion'] ?? '';
        $telefono = $data['Telefono'] ?? $data['telefono'] ?? '';
        $cantidadMaquinas = (int)($data['Cantidad_Maquinas'] ?? $data['cantidad_maquinas'] ?? 0);
        $fechaRegistro = $data['Fecha_Registro'] ?? $data['fecha_registro'] ?? date('Y-m-d');

        return new self(
            $id,
            $nombre,
            $tipo,
            $direccion,
            $telefono,
            $cantidadMaquinas,
            $fechaRegistro
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