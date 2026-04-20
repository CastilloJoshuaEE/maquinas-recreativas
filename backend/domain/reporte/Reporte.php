<?php
/**
 * domain/reporte/Reporte.php
 *
 * Entidad que representa un reporte/chat entre usuarios.
 *
 * @package maquinas_recreativas\Domain\Reporte
 */

namespace maquinas_recreativas\Domain\Reporte;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use DateTimeImmutable;

/**
 * Class Reporte
 *
 * Entidad raíz del agregado de reportes (chat entre usuarios).
 */
class Reporte
{
    private Uuid $id;
    private Uuid $idUsuarioEmisor;
    private ?Uuid $idUsuarioDestinatario;
    private string $descripcion;
    private DateTimeImmutable $fechaHora;
    private EstadoReporte $estado;

    /**
     * Constructor privado.
     */
    private function __construct(
        Uuid $id,
        Uuid $idUsuarioEmisor,
        ?Uuid $idUsuarioDestinatario,
        string $descripcion,
        DateTimeImmutable $fechaHora,
        EstadoReporte $estado
    ) {
        $this->id = $id;
        $this->idUsuarioEmisor = $idUsuarioEmisor;
        $this->idUsuarioDestinatario = $idUsuarioDestinatario;
        $this->descripcion = $descripcion;
        $this->fechaHora = $fechaHora;
        $this->estado = $estado;
    }

    /**
     * Crea un nuevo reporte.
     *
     * @param Uuid $idUsuarioEmisor
     * @param Uuid|null $idUsuarioDestinatario
     * @param string $descripcion
     * @return self
     */
    public static function crear(
        Uuid $idUsuarioEmisor,
        ?Uuid $idUsuarioDestinatario,
        string $descripcion
    ): self {
        return new self(
            Uuid::v4(),
            $idUsuarioEmisor,
            $idUsuarioDestinatario,
            $descripcion,
            new DateTimeImmutable(),
            EstadoReporte::PENDIENTE()
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
            new Uuid($data['ID_Reporte']),
            new Uuid($data['ID_Usuario_Emisor']),
            isset($data['ID_Usuario_Destinatario']) ? new Uuid($data['ID_Usuario_Destinatario']) : null,
            $data['descripcion'],
            new DateTimeImmutable($data['fecha_hora']),
            EstadoReporte::fromString($data['estado'])
        );
    }

    /**
     * Convierte a array para persistencia.
     *
     * @return array
     */
public function toArray(): array
{
    return [
        'ID_Reporte' => $this->id->value(),
        'ID_Usuario_Emisor' => $this->idUsuarioEmisor->value(),
        'ID_Usuario_Destinatario' => $this->idUsuarioDestinatario?->value(),
        'descripcion' => $this->descripcion,
        'estado' => $this->estado->value(),
        'fecha_hora' => $this->fechaHora->format('Y-m-d H:i:s'),
        'emisor_nombre' => $this->emisorNombre ?? '',
        'emisor_apellido' => $this->emisorApellido ?? '',
        'destinatario_nombre' => $this->destinatarioNombre ?? '',
        'destinatario_apellido' => $this->destinatarioApellido ?? '',
    ];
}

    /**
     * Actualiza el estado del reporte.
     *
     * @param EstadoReporte $nuevoEstado
     * @throws \InvalidArgumentException
     */
    public function actualizarEstado(EstadoReporte $nuevoEstado): void
    {
        if ($this->estado->equals(EstadoReporte::RESUELTO())) {
            throw new \InvalidArgumentException('No se puede modificar un reporte ya resuelto');
        }

        $this->estado = $nuevoEstado;
    }

    /**
     * Verifica si un usuario tiene permiso para interactuar con este reporte.
     *
     * @param Uuid $idUsuario
     * @return bool
     */
    public function usuarioTienePermiso(Uuid $idUsuario): bool
    {
        return $this->idUsuarioEmisor->equals($idUsuario) ||
            ($this->idUsuarioDestinatario !== null && $this->idUsuarioDestinatario->equals($idUsuario));
    }

    // --- Getters ---
    public function id(): Uuid { return $this->id; }
    public function idUsuarioEmisor(): Uuid { return $this->idUsuarioEmisor; }
    public function idUsuarioDestinatario(): ?Uuid { return $this->idUsuarioDestinatario; }
    public function descripcion(): string { return $this->descripcion; }
    public function fechaHora(): DateTimeImmutable { return $this->fechaHora; }
    public function estado(): EstadoReporte { return $this->estado; }

    /**
     * Verifica si el reporte es un chat entre dos usuarios.
     */
    public function esChat(): bool
    {
        return $this->idUsuarioDestinatario !== null;
    }
}