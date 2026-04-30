<?php
/**
 * domain/comentario/Comentario.php
 *
 * Entidad que representa un comentario en un reporte.
 *
 * @package maquinas_recreativas\Domain\Comentario
 */

namespace maquinas_recreativas\Domain\Comentario;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
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
    private ?DateTimeImmutable $fechaEdicion;
    private bool $eliminado;
    /**
     * Constructor privado.
     */
    private function __construct(
        Uuid $id,
        Uuid $idReporte,
        Uuid $idUsuarioEmisor,
        string $comentario,
        DateTimeImmutable $fechaHora,
         ?DateTimeImmutable $fechaEdicion = null,
        bool $eliminado = false
    ) {
        $this->id = $id;
        $this->idReporte = $idReporte;
        $this->idUsuarioEmisor = $idUsuarioEmisor;
        $this->comentario = $comentario;
        $this->fechaHora = $fechaHora;
        $this->fechaEdicion = $fechaEdicion;
        $this->eliminado = $eliminado;
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
            'fecha_hora' => $this->fechaHora->format('Y-m-d H:i:s'),
                    'fecha_edicion' => $this->fechaEdicion?->format('Y-m-d H:i:s'),
                        'eliminado' => $this->eliminado ? 1:0

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

    /**
     * Verifica si el comentario puede ser editado (menos de 15 minutos)
     */
    public function puedeSerEditado(): bool
    {
        if ($this->eliminado) return false;
        
        $ahora = new DateTimeImmutable();
        $diferencia = $ahora->getTimestamp() - $this->fechaHora->getTimestamp();
        return $diferencia <= 900; // 15 minutos = 900 segundos
    }

    /**
     * Verifica si el comentario puede ser eliminado (menos de 15 minutos)
     */
    public function puedeSerEliminado(): bool
    {
        return $this->puedeSerEditado(); // Misma regla
    }

    /**
     * Edita el contenido del comentario
     */
    public function editar(string $nuevoComentario): void
    {
        if (!$this->puedeSerEditado()) {
            throw new \DomainException('Ya no puedes editar este comentario. Solo tienes 15 minutos.');
        }
        
        if (empty(trim($nuevoComentario))) {
            throw new \InvalidArgumentException('El comentario no puede estar vacío');
        }
        
        $this->comentario = $nuevoComentario;
        $this->fechaEdicion = new DateTimeImmutable();
    }

    /**
     * Marca el comentario como eliminado (soft delete)
     */
    public function eliminar(): void
    {
        if (!$this->puedeSerEliminado()) {
            throw new \DomainException('Ya no puedes eliminar este comentario. Solo tienes 15 minutos.');
        }
        
        $this->eliminado = true;
        $this->comentario = '[Mensaje eliminado]';
    }
    // --- Getters ---
    public function id(): Uuid { return $this->id; }
    public function idReporte(): Uuid { return $this->idReporte; }
    public function idUsuarioEmisor(): Uuid { return $this->idUsuarioEmisor; }
    public function comentario(): string { return $this->comentario; }
    public function fechaHora(): DateTimeImmutable { return $this->fechaHora; }
    public function fechaEdicion(): ?DateTimeImmutable { return $this->fechaEdicion; }
    public function estaEditado(): bool { return $this->fechaEdicion !== null; }
    public function estaEliminado(): bool { return $this->eliminado; }

    }