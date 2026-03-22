<?php
/**
 * domain/comentario/Comentario.php
 *
 * Entidad que representa un comentario en un reporte.
 *
 * @package Reconocimiento\Domain\Comentario
 */

namespace Reconocimiento\Domain\Comentario;

use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use DateTimeImmutable;

/**
 * Class Comentario
 *
 * Entidad que representa un mensaje dentro de un reporte/chat.
 */
class Comentario
{
    private Uuid $id;
    private Uuid $idReporte;
    private Uuid $idUsuarioEmisor;
    private string $comentario;
    private DateTimeImmutable $fechaHora;

    /**
     * Constructor privado.
     */
    private function __construct(
        Uuid $id,
        Uuid $idReporte,
        Uuid $idUsuarioEmisor,
        string $comentario,
        DateTimeImmutable $fechaHora
    ) {
        $this->id = $id;
        $this->idReporte = $idReporte;
        $this->idUsuarioEmisor = $idUsuarioEmisor;
        $this->comentario = $comentario;
        $this->fechaHora = $fechaHora;
    }

    /**
     * Crea un nuevo comentario.
     *
     * @param Uuid $idReporte
     * @param Uuid $idUsuarioEmisor
     * @param string $comentario
     * @return self
     */
    public static function crear(
        Uuid $idReporte,
        Uuid $idUsuarioEmisor,
        string $comentario
    ): self {
        if (empty(trim($comentario))) {
            throw new \InvalidArgumentException('El comentario no puede estar vacío');
        }

        return new self(
            Uuid::v4(),
            $idReporte,
            $idUsuarioEmisor,
            $comentario,
            new DateTimeImmutable()
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
            new Uuid($data['ID_Comentario']),
            new Uuid($data['ID_Reporte']),
            new Uuid($data['ID_Usuario_Emisor']),
            $data['comentario'],
            new DateTimeImmutable($data['fecha_hora'])
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
            'ID_Comentario' => $this->id->value(),
            'ID_Reporte' => $this->idReporte->value(),
            'ID_Usuario_Emisor' => $this->idUsuarioEmisor->value(),
            'comentario' => $this->comentario,
            'fecha_hora' => $this->fechaHora->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Verifica si el comentario fue enviado por un usuario específico.
     *
     * @param Uuid $idUsuario
     * @return bool
     */
    public function esDeUsuario(Uuid $idUsuario): bool
    {
        return $this->idUsuarioEmisor->equals($idUsuario);
    }

    // --- Getters ---
    public function id(): Uuid { return $this->id; }
    public function idReporte(): Uuid { return $this->idReporte; }
    public function idUsuarioEmisor(): Uuid { return $this->idUsuarioEmisor; }
    public function comentario(): string { return $this->comentario; }
    public function fechaHora(): DateTimeImmutable { return $this->fechaHora; }
}