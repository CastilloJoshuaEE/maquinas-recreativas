<?php
/**
 * domain/montaje/Montaje.php
 *
 * Entidad que representa el montaje de un componente en una máquina.
 *
 * @package maquinas_recreativas\Domain\Montaje
 */

namespace maquinas_recreativas\Domain\Montaje;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use DateTimeImmutable;

/**
 * Class Montaje
 */
class Montaje
{
    private Uuid $id;
    private Uuid $idMaquina;
    private Uuid $idComponente;
    private Uuid $idTecnico;
    private string $detalle;
    private DateTimeImmutable $fecha;

    private function __construct(
        Uuid $id,
        Uuid $idMaquina,
        Uuid $idComponente,
        Uuid $idTecnico,
        string $detalle,
        DateTimeImmutable $fecha
    ) {
        $this->id = $id;
        $this->idMaquina = $idMaquina;
        $this->idComponente = $idComponente;
        $this->idTecnico = $idTecnico;
        $this->detalle = $detalle;
        $this->fecha = $fecha;
    }

    public static function registrar(
        Uuid $idMaquina,
        Uuid $idComponente,
        Uuid $idTecnico,
        string $detalle = ''
    ): self {
        return new self(
            Uuid::v4(),
            $idMaquina,
            $idComponente,
            $idTecnico,
            $detalle,
            new DateTimeImmutable()
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            new Uuid($data['ID_Montaje']),
            new Uuid($data['ID_Maquina']),
            new Uuid($data['ID_Componente']),
            new Uuid($data['ID_Tecnico']),
            $data['detalle'] ?? '',
            new DateTimeImmutable($data['fecha'])
        );
    }

    public function toArray(): array
    {
        return [
            'ID_Montaje' => $this->id->value(),
            'ID_Maquina' => $this->idMaquina->value(),
            'ID_Componente' => $this->idComponente->value(),
            'ID_Tecnico' => $this->idTecnico->value(),
            'detalle' => $this->detalle,
            'fecha' => $this->fecha->format('Y-m-d H:i:s')
        ];
    }

    // Getters
    public function id(): Uuid { return $this->id; }
    public function idMaquina(): Uuid { return $this->idMaquina; }
    public function idComponente(): Uuid { return $this->idComponente; }
    public function idTecnico(): Uuid { return $this->idTecnico; }
    public function detalle(): string { return $this->detalle; }
    public function fecha(): DateTimeImmutable { return $this->fecha; }
}