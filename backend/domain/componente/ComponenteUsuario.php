<?php
/**
 * domain/componente/ComponenteUsuario.php
 *
 * Entidad que representa la asignación de un componente a un usuario,
 * opcionalmente vinculado a una máquina.
 *
 * @package maquinas_recreativas\Domain\Componente
 */

namespace maquinas_recreativas\Domain\Componente;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use DateTimeImmutable;

/**
 * Class ComponenteUsuario
 *
 * Representa el registro de uso de un componente por parte de un usuario,
 * pudiendo estar asociado a una máquina específica. Contiene las fechas de
 * asignación y liberación.
 */
final class ComponenteUsuario
{
    private Uuid $idRegistro;
    private Uuid $idComponente;
    private Uuid $idUsuario;
    private ?Uuid $idMaquina;
    private DateTimeImmutable $fechaAsignacion;
    private ?DateTimeImmutable $fechaLiberacion;

    /**
     * Constructor privado.
     *
     * @param Uuid $idRegistro
     * @param Uuid $idComponente
     * @param Uuid $idUsuario
     * @param Uuid|null $idMaquina
     * @param DateTimeImmutable $fechaAsignacion
     * @param DateTimeImmutable|null $fechaLiberacion
     */
    private function __construct(
        Uuid $idRegistro,
        Uuid $idComponente,
        Uuid $idUsuario,
        ?Uuid $idMaquina,
        DateTimeImmutable $fechaAsignacion,
        ?DateTimeImmutable $fechaLiberacion
    ) {
        $this->idRegistro = $idRegistro;
        $this->idComponente = $idComponente;
        $this->idUsuario = $idUsuario;
        $this->idMaquina = $idMaquina;
        $this->fechaAsignacion = $fechaAsignacion;
        $this->fechaLiberacion = $fechaLiberacion;
    }

    /**
     * Crea un nuevo registro de asignación de componente a un usuario.
     *
     * @param Uuid $idComponente
     * @param Uuid $idUsuario
     * @param Uuid|null $idMaquina
     * @return self
     */
    public static function asignar(
        Uuid $idComponente,
        Uuid $idUsuario,
        ?Uuid $idMaquina = null
    ): self {
        return new self(
            Uuid::v4(),
            $idComponente,
            $idUsuario,
            $idMaquina,
            new DateTimeImmutable(),
            null
        );
    }

    /**
     * Reconstruye una entidad desde datos persistentes.
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            new Uuid($data['ID_Registro']),
            new Uuid($data['ID_Componente']),
            new Uuid($data['ID_Usuario']),
            isset($data['ID_Maquina']) ? new Uuid($data['ID_Maquina']) : null,
            new DateTimeImmutable($data['fecha_asignacion']),
            isset($data['fecha_liberacion']) ? new DateTimeImmutable($data['fecha_liberacion']) : null
        );
    }

    /**
     * Convierte la entidad a array para persistencia.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'ID_Registro' => $this->idRegistro->value(),
            'ID_Componente' => $this->idComponente->value(),
            'ID_Usuario' => $this->idUsuario->value(),
            'ID_Maquina' => $this->idMaquina?->value(),
            'fecha_asignacion' => $this->fechaAsignacion->format('Y-m-d H:i:s'),
            'fecha_liberacion' => $this->fechaLiberacion?->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Marca el componente como liberado.
     *
     * @return void
     * @throws \DomainException Si ya está liberado
     */
    public function liberar(): void
    {
        if ($this->fechaLiberacion !== null) {
            throw new \DomainException('El componente ya ha sido liberado.');
        }
        $this->fechaLiberacion = new DateTimeImmutable();
    }

    /**
     * Indica si el componente está actualmente asignado (no liberado).
     *
     * @return bool
     */
    public function estaAsignado(): bool
    {
        return $this->fechaLiberacion === null;
    }

    // --- Getters ---
    public function idRegistro(): Uuid { return $this->idRegistro; }
    public function idComponente(): Uuid { return $this->idComponente; }
    public function idUsuario(): Uuid { return $this->idUsuario; }
    public function idMaquina(): ?Uuid { return $this->idMaquina; }
    public function fechaAsignacion(): DateTimeImmutable { return $this->fechaAsignacion; }
    public function fechaLiberacion(): ?DateTimeImmutable { return $this->fechaLiberacion; }
}