<?php
/**
 * domain/notificacion/NotificacionMaquina.php
 *
 * Entidad que representa una notificación relacionada con máquinas.
 *
 * @package maquinas_recreativas\Domain\Notificacion
 */

namespace maquinas_recreativas\Domain\Notificacion;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use DateTimeImmutable;

/**
 * Class NotificacionMaquina
 */
class NotificacionMaquina
{
    private Uuid $id;
    private Uuid $idRemitente;
    private Uuid $idDestinatario;
    private Uuid $idMaquina;
    private string $tipo;
    private string $mensaje;
    private DateTimeImmutable $fecha;
    private string $estado;

    private function __construct(
        Uuid $id,
        Uuid $idRemitente,
        Uuid $idDestinatario,
        Uuid $idMaquina,
        string $tipo,
        string $mensaje,
        DateTimeImmutable $fecha,
        string $estado
    ) {
        $this->id = $id;
        $this->idRemitente = $idRemitente;
        $this->idDestinatario = $idDestinatario;
        $this->idMaquina = $idMaquina;
        $this->tipo = $tipo;
        $this->mensaje = $mensaje;
        $this->fecha = $fecha;
        $this->estado = $estado;
    }

    public static function crear(
        Uuid $idRemitente,
        Uuid $idDestinatario,
        Uuid $idMaquina,
        string $tipo,
        string $mensaje
    ): self {
        return new self(
            Uuid::v4(),
            $idRemitente,
            $idDestinatario,
            $idMaquina,
            $tipo,
            $mensaje,
            new DateTimeImmutable(),
            'No leido'
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            new Uuid($data['ID_Notificacion']),
            new Uuid($data['ID_Remitente']),
            new Uuid($data['ID_Destinatario']),
            new Uuid($data['ID_Maquina']),
            $data['Tipo'],
            $data['Mensaje'],
            new DateTimeImmutable($data['Fecha']),
            $data['Estado']
        );
    }

    public function toArray(): array
    {
        return [
            'ID_Notificacion' => $this->id->value(),
            'ID_Remitente' => $this->idRemitente->value(),
            'ID_Destinatario' => $this->idDestinatario->value(),
            'ID_Maquina' => $this->idMaquina->value(),
            'Tipo' => $this->tipo,
            'Mensaje' => $this->mensaje,
            'Fecha' => $this->fecha->format('Y-m-d H:i:s'),
            'Estado' => $this->estado
        ];
    }

    public function marcarComoLeida(): void
    {
        $this->estado = 'Leido';
    }

    public function estaLeida(): bool
    {
        return $this->estado === 'Leido';
    }

    // --- Getters ---
    public function id(): Uuid { return $this->id; }
    public function idRemitente(): Uuid { return $this->idRemitente; }
    public function idDestinatario(): Uuid { return $this->idDestinatario; }
    public function idMaquina(): Uuid { return $this->idMaquina; }
    public function tipo(): string { return $this->tipo; }
    public function mensaje(): string { return $this->mensaje; }
    public function fecha(): DateTimeImmutable { return $this->fecha; }
    public function estado(): string { return $this->estado; }
}