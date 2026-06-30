<?php
/**
 * domain/componente/Componente.php
 *
 * Entidad que representa un componente de máquina recreativa.
 *
 * @package maquinas_recreativas\Domain\Componente
 */

namespace maquinas_recreativas\Domain\Componente;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use DateTimeImmutable;

/**
 * Clase Componente
 *
 * Entidad raíz del agregado de componentes.
 */
class Componente
{
    private Uuid $id;
    private TipoComponente $tipo;
    private string $nombre;
    private float $precio;
    private ?Uuid $usuarioAsignado;
    private ?Uuid $maquinaAsignada;
    private ?DateTimeImmutable $fechaAsignacion;
    private ?DateTimeImmutable $fechaLiberacion;

    /**
     * Constructor privado.
     */
    private function __construct(
        Uuid $id,
        TipoComponente $tipo,
        string $nombre,
        float $precio,
        ?Uuid $usuarioAsignado = null,
        ?Uuid $maquinaAsignada = null,
        ?DateTimeImmutable $fechaAsignacion = null,
        ?DateTimeImmutable $fechaLiberacion = null
    ) {
        $this->id = $id;
        $this->tipo = $tipo;
        $this->nombre = $nombre;
        $this->precio = $precio;
        $this->usuarioAsignado = $usuarioAsignado;
        $this->maquinaAsignada = $maquinaAsignada;
        $this->fechaAsignacion = $fechaAsignacion;
        $this->fechaLiberacion = $fechaLiberacion;
    }

    /**
     * Crea un nuevo componente (sin asignar).
     *
     * @param TipoComponente $tipo
     * @param string $nombre
     * @param float $precio
     * @return self
     */
    public static function crear(TipoComponente $tipo, string $nombre, float $precio): self
    {
        return new self(
            Uuid::v4(),
            $tipo,
            $nombre,
            $precio
        );
    }

    /**
     * Genera una placa (componente logístico con nomenclatura especial).
     *
     * @param string $numeroPlaca
     * @return self
     */
    public static function generarPlaca(string $numeroPlaca): self
    {
        return new self(
            Uuid::v4(),
            TipoComponente::LOGISTICO(),
            $numeroPlaca,
            120.00
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
            new Uuid($data['ID_Componente']),
            TipoComponente::fromString($data['tipo']),
            $data['nombre'],
            (float)$data['precio'],
            isset($data['usuario_uso']) ? new Uuid($data['usuario_uso']) : null,
            isset($data['maquina_uso']) ? new Uuid($data['maquina_uso']) : null,
            isset($data['fecha_asignacion']) ? new DateTimeImmutable($data['fecha_asignacion']) : null,
            isset($data['fecha_liberacion']) ? new DateTimeImmutable($data['fecha_liberacion']) : null
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
            'ID_Componente' => $this->id->value(),
            'tipo' => $this->tipo->value(),
            'nombre' => $this->nombre,
            'precio' => $this->precio,
            'usuario_uso' => $this->usuarioAsignado?->value(),
            'maquina_uso' => $this->maquinaAsignada?->value(),
            'fecha_asignacion' => $this->fechaAsignacion?->format('Y-m-d H:i:s'),
            'fecha_liberacion' => $this->fechaLiberacion?->format('Y-m-d H:i:s')
        ];
    }

    // --- Getters ---
 public function id(): Uuid { return $this->id; }
    public function tipo(): TipoComponente { return $this->tipo; }
    public function nombre(): string { return $this->nombre; }
    public function precio(): float { return $this->precio; }
    public function estaDisponible(): bool { return $this->usuarioAsignado === null; }
    public function estaAsignado(): bool { return $this->usuarioAsignado !== null && $this->fechaLiberacion === null; }
    public function usuarioAsignado(): ?Uuid { return $this->usuarioAsignado; }
    public function maquinaAsignada(): ?Uuid { return $this->maquinaAsignada; }
    public function fechaAsignacion(): ?DateTimeImmutable { return $this->fechaAsignacion; }
    public function fechaLiberacion(): ?DateTimeImmutable { return $this->fechaLiberacion; }
    // --- Comportamiento ---

    /**
     * Asigna el componente a un usuario y opcionalmente a una máquina.
     *
     * @param Uuid $idUsuario
     * @param Uuid|null $idMaquina
     * @throws \RuntimeException Si el componente ya está asignado.
     */
    public function asignarAUso(Uuid $idUsuario, ?Uuid $idMaquina = null): void
    {
        if (!$this->estaDisponible()) {
            throw new \RuntimeException('El componente no está disponible para asignación');
        }

        $this->usuarioAsignado = $idUsuario;
        $this->maquinaAsignada = $idMaquina;
        $this->fechaAsignacion = new DateTimeImmutable();
        $this->fechaLiberacion = null;
    }


    /**
     * Libera el componente (lo deja disponible nuevamente).
     *
     * @throws \RuntimeException Si el componente no está asignado.
     */
     public function liberar(): void
    {
        if ($this->estaDisponible()) {
            throw new \RuntimeException('El componente ya está disponible');
        }

        $this->fechaLiberacion = new DateTimeImmutable();
    }
}