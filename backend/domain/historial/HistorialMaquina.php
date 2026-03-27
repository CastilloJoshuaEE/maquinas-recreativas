<?php
/**
 * domain/historial/HistorialMaquina.php
 *
 * Entidad que representa el historial de una máquina recreativa.
 *
 * @package maquinas_recreativas\Domain\Historial
 */

namespace maquinas_recreativas\Domain\Historial;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use DateTimeImmutable;

/**
 * Class HistorialMaquina
 *
 * Entidad que registra cada cambio de estado/etapa de una máquina.
 */
class HistorialMaquina
{
    private Uuid $id;
    private Uuid $idMaquina;
    private Uuid $idUsuario;
    private string $tipoUsuario;
    private string $accion;
    private string $descripcion;
    private ?string $estadoAnterior;
    private ?string $estadoNuevo;
    private ?string $etapaAnterior;
    private ?string $etapaNueva;
    private ?string $ipAddress;
    private ?array $detallesAdicionales;
    private DateTimeImmutable $fechaHora;

    /**
     * Constructor privado.
     */
    private function __construct(
        Uuid $id,
        Uuid $idMaquina,
        Uuid $idUsuario,
        string $tipoUsuario,
        string $accion,
        string $descripcion,
        ?string $estadoAnterior,
        ?string $estadoNuevo,
        ?string $etapaAnterior,
        ?string $etapaNueva,
        ?string $ipAddress,
        ?array $detallesAdicionales,
        DateTimeImmutable $fechaHora
    ) {
        $this->id = $id;
        $this->idMaquina = $idMaquina;
        $this->idUsuario = $idUsuario;
        $this->tipoUsuario = $tipoUsuario;
        $this->accion = $accion;
        $this->descripcion = $descripcion;
        $this->estadoAnterior = $estadoAnterior;
        $this->estadoNuevo = $estadoNuevo;
        $this->etapaAnterior = $etapaAnterior;
        $this->etapaNueva = $etapaNueva;
        $this->ipAddress = $ipAddress;
        $this->detallesAdicionales = $detallesAdicionales;
        $this->fechaHora = $fechaHora;
    }

    /**
     * Crea un nuevo registro de historial.
     *
     * @param Uuid $idMaquina
     * @param Uuid $idUsuario
     * @param string $tipoUsuario
     * @param string $accion
     * @param string $descripcion
     * @param string|null $estadoAnterior
     * @param string|null $estadoNuevo
     * @param string|null $etapaAnterior
     * @param string|null $etapaNueva
     * @param string|null $ipAddress
     * @param array|null $detallesAdicionales
     * @return self
     */
    public static function registrar(
        Uuid $idMaquina,
        Uuid $idUsuario,
        string $tipoUsuario,
        string $accion,
        string $descripcion = '',
        ?string $estadoAnterior = null,
        ?string $estadoNuevo = null,
        ?string $etapaAnterior = null,
        ?string $etapaNueva = null,
        ?string $ipAddress = null,
        ?array $detallesAdicionales = null
    ): self {
        return new self(
            Uuid::v4(),
            $idMaquina,
            $idUsuario,
            $tipoUsuario,
            $accion,
            $descripcion,
            $estadoAnterior,
            $estadoNuevo,
            $etapaAnterior,
            $etapaNueva,
            $ipAddress,
            $detallesAdicionales,
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
            new Uuid($data['ID_Historial']),
            new Uuid($data['ID_Maquina']),
            new Uuid($data['ID_Usuario']),
            $data['tipo_usuario'],
            $data['accion'],
            $data['descripcion'],
            $data['estado_anterior'] ?? null,
            $data['estado_nuevo'] ?? null,
            $data['etapa_anterior'] ?? null,
            $data['etapa_nueva'] ?? null,
            $data['ip_address'] ?? null,
            isset($data['detalles_adicionales']) ? json_decode($data['detalles_adicionales'], true) : null,
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
            'ID_Historial' => $this->id->value(),
            'ID_Maquina' => $this->idMaquina->value(),
            'ID_Usuario' => $this->idUsuario->value(),
            'tipo_usuario' => $this->tipoUsuario,
            'accion' => $this->accion,
            'descripcion' => $this->descripcion,
            'estado_anterior' => $this->estadoAnterior,
            'estado_nuevo' => $this->estadoNuevo,
            'etapa_anterior' => $this->etapaAnterior,
            'etapa_nueva' => $this->etapaNueva,
            'ip_address' => $this->ipAddress,
            'detalles_adicionales' => $this->detallesAdicionales ? json_encode($this->detallesAdicionales, JSON_UNESCAPED_UNICODE) : null,
            'fecha_hora' => $this->fechaHora->format('Y-m-d H:i:s')
        ];
    }

    // --- Getters ---
    public function id(): Uuid { return $this->id; }
    public function idMaquina(): Uuid { return $this->idMaquina; }
    public function idUsuario(): Uuid { return $this->idUsuario; }
    public function tipoUsuario(): string { return $this->tipoUsuario; }
    public function accion(): string { return $this->accion; }
    public function descripcion(): string { return $this->descripcion; }
    public function estadoAnterior(): ?string { return $this->estadoAnterior; }
    public function estadoNuevo(): ?string { return $this->estadoNuevo; }
    public function etapaAnterior(): ?string { return $this->etapaAnterior; }
    public function etapaNueva(): ?string { return $this->etapaNueva; }
    public function ipAddress(): ?string { return $this->ipAddress; }
    public function detallesAdicionales(): ?array { return $this->detallesAdicionales; }
    public function fechaHora(): DateTimeImmutable { return $this->fechaHora; }
}