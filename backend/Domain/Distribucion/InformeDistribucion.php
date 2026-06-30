<?php
/**
 * domain/distribucion/InformeDistribucion.php
 *
 * Entidad que representa el informe de distribución de una máquina.
 *
 * @package maquinas_recreativas\Domain\Distribucion
 */

namespace maquinas_recreativas\Domain\Distribucion;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use DateTimeImmutable;

/**
 * Class InformeDistribucion
 */
class InformeDistribucion
{
    private Uuid $id;
    private Uuid $idMaquina;
    private Uuid $idUsuarioComprobador;
    private Uuid $idComercio;
    private DateTimeImmutable $fechaAlta;
    private ?DateTimeImmutable $fechaBaja;
    private string $estado;

    private function __construct(
        Uuid $id,
        Uuid $idMaquina,
        Uuid $idUsuarioComprobador,
        Uuid $idComercio,
        DateTimeImmutable $fechaAlta,
        ?DateTimeImmutable $fechaBaja,
        string $estado
    ) {
        $this->id = $id;
        $this->idMaquina = $idMaquina;
        $this->idUsuarioComprobador = $idUsuarioComprobador;
        $this->idComercio = $idComercio;
        $this->fechaAlta = $fechaAlta;
        $this->fechaBaja = $fechaBaja;
        $this->estado = $estado;
    }

    public static function crear(
        Uuid $idMaquina,
        Uuid $idUsuarioComprobador,
        Uuid $idComercio
    ): self {
        return new self(
            Uuid::v4(),
            $idMaquina,
            $idUsuarioComprobador,
            $idComercio,
            new DateTimeImmutable(),
            null,
            'Distribuyendose'
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            new Uuid($data['ID_Distribucion']),
            new Uuid($data['ID_Maquina']),
            new Uuid($data['ID_Usuario_Comprobador']),
            new Uuid($data['ID_Comercio']),
            new DateTimeImmutable($data['fecha_alta']),
            isset($data['fecha_baja']) ? new DateTimeImmutable($data['fecha_baja']) : null,
            $data['estado']
        );
    }

    public function toArray(): array
    {
        return [
            'ID_Distribucion' => $this->id->value(),
            'ID_Maquina' => $this->idMaquina->value(),
            'ID_Usuario_Comprobador' => $this->idUsuarioComprobador->value(),
            'ID_Comercio' => $this->idComercio->value(),
            'fecha_alta' => $this->fechaAlta->format('Y-m-d H:i:s'),
            'fecha_baja' => $this->fechaBaja?->format('Y-m-d H:i:s'),
            'estado' => $this->estado
        ];
    }

    public function actualizarEstado(string $nuevoEstado): void
    {
        $estadosPermitidos = ['Operativa', 'Retirada', 'No operativa', 'Distribuyendose'];
        if (!in_array($nuevoEstado, $estadosPermitidos, true)) {
            throw new \InvalidArgumentException("Estado no válido: {$nuevoEstado}");
        }

        $this->estado = $nuevoEstado;

        if ($nuevoEstado === 'Retirada' && $this->fechaBaja === null) {
            $this->fechaBaja = new DateTimeImmutable();
        }
    }

    // --- Getters ---
    public function id(): Uuid { return $this->id; }
    public function idMaquina(): Uuid { return $this->idMaquina; }
    public function idUsuarioComprobador(): Uuid { return $this->idUsuarioComprobador; }
    public function idComercio(): Uuid { return $this->idComercio; }
    public function fechaAlta(): DateTimeImmutable { return $this->fechaAlta; }
    public function fechaBaja(): ?DateTimeImmutable { return $this->fechaBaja; }
    public function estado(): string { return $this->estado; }
}