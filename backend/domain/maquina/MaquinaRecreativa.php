<?php
/**
 * domain/maquina/MaquinaRecreativa.php
 *
 * Entidad que representa una máquina recreativa en el dominio.
 *
 * @package Reconocimiento\Domain\Maquina
 */

namespace Reconocimiento\Domain\Maquina;

use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use DateTimeImmutable;

/**
 * Clase MaquinaRecreativa
 *
 * Entidad raíz del agregado de máquinas.
 */
class MaquinaRecreativa
{
    private Uuid $id;
    private string $nombre;
    private string $tipo;
    private Uuid $idComercio;
    private Uuid $idTecnicoEnsamblador;
    private Uuid $idTecnicoComprobador;
    private ?Uuid $idTecnicoMantenimiento;
    private EstadoMaquina $estado;
    private EtapaMaquina $etapa;
    private DateTimeImmutable $fechaRegistro;

    /**
     * Constructor privado. Usar el método estático `crear` para instanciar.
     */
    private function __construct(
        Uuid $id,
        string $nombre,
        string $tipo,
        Uuid $idComercio,
        Uuid $idTecnicoEnsamblador,
        Uuid $idTecnicoComprobador,
        EstadoMaquina $estado,
        EtapaMaquina $etapa,
        DateTimeImmutable $fechaRegistro,
        ?Uuid $idTecnicoMantenimiento = null
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->tipo = $tipo;
        $this->idComercio = $idComercio;
        $this->idTecnicoEnsamblador = $idTecnicoEnsamblador;
        $this->idTecnicoComprobador = $idTecnicoComprobador;
        $this->idTecnicoMantenimiento = $idTecnicoMantenimiento;
        $this->estado = $estado;
        $this->etapa = $etapa;
        $this->fechaRegistro = $fechaRegistro;
    }

    /**
     * Crea una nueva instancia de MaquinaRecreativa.
     *
     * @param string $nombre Nombre de la máquina.
     * @param string $tipo Tipo de máquina.
     * @param Uuid $idComercio ID del comercio.
     * @param Uuid $idTecnicoEnsamblador ID del técnico ensamblador.
     * @param Uuid $idTecnicoComprobador ID del técnico comprobador.
     * @return self
     */
    public static function crear(
        string $nombre,
        string $tipo,
        Uuid $idComercio,
        Uuid $idTecnicoEnsamblador,
        Uuid $idTecnicoComprobador
    ): self {
        return new self(
            Uuid::v4(),
            $nombre,
            $tipo,
            $idComercio,
            $idTecnicoEnsamblador,
            $idTecnicoComprobador,
            EstadoMaquina::ENSAMBLANDOSE(),
            EtapaMaquina::MONTAJE(),
            new DateTimeImmutable()
        );
    }

    /**
     * Reconstruye una entidad desde datos persistentes.
     *
     * @param array $data Datos de la base de datos.
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            new Uuid($data['ID_Maquina']),
            $data['Nombre_Maquina'],
            $data['Tipo'],
            new Uuid($data['ID_Comercio']),
            new Uuid($data['ID_Tecnico_Ensamblador']),
            new Uuid($data['ID_Tecnico_Comprobador']),
            EstadoMaquina::fromString($data['Estado']),
            EtapaMaquina::fromString($data['Etapa']),
            new DateTimeImmutable($data['Fecha_Registro']),
            isset($data['ID_Tecnico_Mantenimiento']) ? new Uuid($data['ID_Tecnico_Mantenimiento']) : null
        );
    }

    /**
     * Convierte la entidad a un array para persistencia.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'ID_Maquina' => $this->id->value(),
            'Nombre_Maquina' => $this->nombre,
            'Tipo' => $this->tipo,
            'ID_Comercio' => $this->idComercio->value(),
            'ID_Tecnico_Ensamblador' => $this->idTecnicoEnsamblador->value(),
            'ID_Tecnico_Comprobador' => $this->idTecnicoComprobador->value(),
            'ID_Tecnico_Mantenimiento' => $this->idTecnicoMantenimiento?->value(),
            'Estado' => $this->estado->value(),
            'Etapa' => $this->etapa->value(),
            'Fecha_Registro' => $this->fechaRegistro->format('Y-m-d H:i:s'),
        ];
    }

    // --- Getters ---
    public function id(): Uuid { return $this->id; }
    public function nombre(): string { return $this->nombre; }
    public function tipo(): string { return $this->tipo; }
    public function idComercio(): Uuid { return $this->idComercio; }
    public function idTecnicoEnsamblador(): Uuid { return $this->idTecnicoEnsamblador; }
    public function idTecnicoComprobador(): Uuid { return $this->idTecnicoComprobador; }
    public function idTecnicoMantenimiento(): ?Uuid { return $this->idTecnicoMantenimiento; }
    public function estado(): EstadoMaquina { return $this->estado; }
    public function etapa(): EtapaMaquina { return $this->etapa; }

    // --- Comportamiento del agregado ---

    /**
     * Envía la máquina a comprobación.
     */
    public function enviarAComprobacion(): void
    {
        $this->estado = EstadoMaquina::COMPROBANDOSE();
        $this->etapa = EtapaMaquina::MONTAJE();
    }

    /**
     * Envía la máquina a reensamblar.
     */
    public function enviarAReensamblar(): void
    {
        $this->estado = EstadoMaquina::REENSAMBLANDOSE();
        $this->etapa = EtapaMaquina::MONTAJE();
    }

    /**
     * Envía la máquina a distribución.
     */
    public function enviarADistribucion(): void
    {
        $this->estado = EstadoMaquina::DISTRIBUYENDOSE();
        $this->etapa = EtapaMaquina::DISTRIBUCION();
    }

    /**
     * Pone la máquina como operativa (en recaudación).
     */
    public function ponerOperativa(): void
    {
        $this->estado = EstadoMaquina::OPERATIVA();
        $this->etapa = EtapaMaquina::RECAUDACION();
    }

    /**
     * Solicita mantenimiento para la máquina.
     *
     * @param Uuid $idTecnicoMantenimiento ID del técnico asignado.
     */
    public function solicitarMantenimiento(Uuid $idTecnicoMantenimiento): void
    {
        $this->estado = EstadoMaquina::NO_OPERATIVA();
        $this->etapa = EtapaMaquina::MONTAJE();
        $this->idTecnicoMantenimiento = $idTecnicoMantenimiento;
    }

    /**
     * Finaliza el mantenimiento de la máquina.
     *
     * @param bool $exito Indica si el mantenimiento fue exitoso.
     */
    public function finalizarMantenimiento(bool $exito): void
    {
        if ($exito) {
            $this->estado = EstadoMaquina::OPERATIVA();
            $this->etapa = EtapaMaquina::RECAUDACION();
        } else {
            $this->estado = EstadoMaquina::RETIRADA();
            $this->etapa = EtapaMaquina::DISTRIBUCION();
        }
        $this->idTecnicoMantenimiento = null;
    }
}