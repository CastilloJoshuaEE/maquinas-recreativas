<?php
/**
 * domain/notificacion/NotificacionReporte.php
 *
 * Entidad que representa una notificación relacionada con reportes.
 *
 * @package maquinas_recreativas\Domain\Notificacion
 */

namespace maquinas_recreativas\Domain\Notificacion;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use DateTimeImmutable;

/**
 * Class NotificacionReporte
 */
class NotificacionReporte
{
    private Uuid $id;
    private Uuid $idReporte;
    private Uuid $idUsuario;
    private string $mensaje;
    private DateTimeImmutable $fechaHora;
    private bool $leida;

    private function __construct(
        Uuid $id,
        Uuid $idReporte,
        Uuid $idUsuario,
        string $mensaje,
        DateTimeImmutable $fechaHora,
        bool $leida
    ) {
        $this->id = $id;
        $this->idReporte = $idReporte;
        $this->idUsuario = $idUsuario;
        $this->mensaje = $mensaje;
        $this->fechaHora = $fechaHora;
        $this->leida = $leida;
    }

    public static function crear(Uuid $idReporte, Uuid $idUsuario, string $mensaje): self
    {
        return new self(
            Uuid::v4(),
            $idReporte,
            $idUsuario,
            $mensaje,
            new DateTimeImmutable(),
            false
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            new Uuid($data['ID_Notificaciones']),
            new Uuid($data['ID_Reporte']),
            new Uuid($data['ID_Usuario']),
            $data['mensaje'],
            new DateTimeImmutable($data['fecha_hora']),
            (bool)$data['leida']
        );
    }

    public function toArray(): array
    {
        return [
            'ID_Notificaciones' => $this->id->value(),
            'ID_Reporte' => $this->idReporte->value(),
            'ID_Usuario' => $this->idUsuario->value(),
            'mensaje' => $this->mensaje,
            'fecha_hora' => $this->fechaHora->format('Y-m-d H:i:s'),
            'leida' => $this->leida ? 1 : 0
        ];
    }

    public function marcarComoLeida(): void
    {
        $this->leida = true;
    }

    public function estaLeida(): bool
    {
        return $this->leida;
    }

    // --- Getters ---
    public function id(): Uuid { return $this->id; }
    public function idReporte(): Uuid { return $this->idReporte; }
    public function idUsuario(): Uuid { return $this->idUsuario; }
    public function mensaje(): string { return $this->mensaje; }
    public function fechaHora(): DateTimeImmutable { return $this->fechaHora; }
    public function leida(): bool { return $this->leida; }
}