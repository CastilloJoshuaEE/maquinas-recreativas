<?php
/**
 * domain/historial/HistorialActividad.php
 *
 * Entidad que representa el historial de actividades de usuario.
 *
 * @package maquinas_recreativas\Domain\Historial
 */

namespace maquinas_recreativas\Domain\Historial;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use DateTimeImmutable;

/**
 * Class HistorialActividad
 */
class HistorialActividad
{
    private Uuid $id;
    private Uuid $idUsuario;
    private string $descripcion;
    private DateTimeImmutable $fechaRegistro;

    private function __construct(
        Uuid $id,
        Uuid $idUsuario,
        string $descripcion,
        DateTimeImmutable $fechaRegistro
    ) {
        $this->id = $id;
        $this->idUsuario = $idUsuario;
        $this->descripcion = $descripcion;
        $this->fechaRegistro = $fechaRegistro;
    }

    public static function registrar(Uuid $idUsuario, string $descripcion): self
    {
        return new self(
            Uuid::v4(),
            $idUsuario,
            $descripcion,
            new DateTimeImmutable()
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            new Uuid($data['ID_Actividad']),
            new Uuid($data['ID_Usuario']),
            $data['descripcion'],
            new DateTimeImmutable($data['fecha_registro'])
        );
    }

    public function toArray(): array
    {
        return [
            'ID_Actividad' => $this->id->value(),
            'ID_Usuario' => $this->idUsuario->value(),
            'descripcion' => $this->descripcion,
            'fecha_registro' => $this->fechaRegistro->format('Y-m-d H:i:s')
        ];
    }

    public function id(): Uuid { return $this->id; }
    public function idUsuario(): Uuid { return $this->idUsuario; }
    public function descripcion(): string { return $this->descripcion; }
    public function fechaRegistro(): DateTimeImmutable { return $this->fechaRegistro; }
}