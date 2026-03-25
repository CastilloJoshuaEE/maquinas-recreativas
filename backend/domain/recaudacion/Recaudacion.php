<?php
/**
 * domain/recaudacion/Recaudacion.php
 *
 * Entidad que representa una recaudación de máquina recreativa.
 *
 * @package maquinas_recreativas\Domain\Recaudacion
 */

namespace maquinas_recreativas\Domain\Recaudacion;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use DateTimeImmutable;

/**
 * Class Recaudacion
 *
 * Entidad raíz del agregado de recaudaciones.
 */
class Recaudacion
{
    private Uuid $id;
    private Uuid $idMaquina;
    private Uuid $idUsuario;
    private string $tipoComercio;
    private float $montoTotal;
    private float $montoEmpresa;
    private float $montoComercio;
    private float $porcentajeComercio;
    private string $detalle;
    private DateTimeImmutable $fecha;

    /**
     * Constructor privado.
     */
    private function __construct(
        Uuid $id,
        Uuid $idMaquina,
        Uuid $idUsuario,
        string $tipoComercio,
        float $montoTotal,
        float $montoEmpresa,
        float $montoComercio,
        float $porcentajeComercio,
        string $detalle,
        DateTimeImmutable $fecha
    ) {
        $this->id = $id;
        $this->idMaquina = $idMaquina;
        $this->idUsuario = $idUsuario;
        $this->tipoComercio = $tipoComercio;
        $this->montoTotal = $montoTotal;
        $this->montoEmpresa = $montoEmpresa;
        $this->montoComercio = $montoComercio;
        $this->porcentajeComercio = $porcentajeComercio;
        $this->detalle = $detalle;
        $this->fecha = $fecha;
    }

    /**
     * Crea una nueva recaudación.
     *
     * @param Uuid $idMaquina
     * @param Uuid $idUsuario
     * @param string $tipoComercio
     * @param float $montoTotal
     * @param float $porcentajeComercio
     * @param string $detalle
     * @return self
     * @throws \InvalidArgumentException Si los montos son inválidos
     */
    public static function crear(
        Uuid $idMaquina,
        Uuid $idUsuario,
        string $tipoComercio,
        float $montoTotal,
        float $porcentajeComercio,
        string $detalle = ''
    ): self {
        if ($montoTotal <= 0) {
            throw new \InvalidArgumentException('El monto total debe ser mayor a 0');
        }

        if ($porcentajeComercio < 0 || $porcentajeComercio > 100) {
            throw new \InvalidArgumentException('El porcentaje del comercio debe estar entre 0 y 100');
        }

        $montoComercio = $montoTotal * ($porcentajeComercio / 100);
        $montoEmpresa = $montoTotal - $montoComercio;

        return new self(
            Uuid::v4(),
            $idMaquina,
            $idUsuario,
            $tipoComercio,
            $montoTotal,
            $montoEmpresa,
            $montoComercio,
            $porcentajeComercio,
            $detalle,
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
            new Uuid($data['ID_Recaudacion']),
            new Uuid($data['ID_Maquina']),
            new Uuid($data['ID_Usuario']),
            $data['Tipo_Comercio'],
            (float)$data['Monto_Total'],
            (float)$data['Monto_Empresa'],
            (float)$data['Monto_Comercio'],
            (float)($data['Porcentaje_Comercio'] ?? 0),
            $data['detalle'] ?? '',
            new DateTimeImmutable($data['fecha'])
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
            'ID_Recaudacion' => $this->id->value(),
            'ID_Maquina' => $this->idMaquina->value(),
            'ID_Usuario' => $this->idUsuario->value(),
            'Tipo_Comercio' => $this->tipoComercio,
            'Monto_Total' => $this->montoTotal,
            'Monto_Empresa' => $this->montoEmpresa,
            'Monto_Comercio' => $this->montoComercio,
            'Porcentaje_Comercio' => $this->porcentajeComercio,
            'detalle' => $this->detalle,
            'fecha' => $this->fecha->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Actualiza los montos de la recaudación.
     *
     * @param float $montoTotal
     * @param float $porcentajeComercio
     * @throws \InvalidArgumentException
     */
    public function actualizarMontos(float $montoTotal, float $porcentajeComercio): void
    {
        if ($montoTotal <= 0) {
            throw new \InvalidArgumentException('El monto total debe ser mayor a 0');
        }

        if ($porcentajeComercio < 0 || $porcentajeComercio > 100) {
            throw new \InvalidArgumentException('El porcentaje del comercio debe estar entre 0 y 100');
        }

        $this->montoTotal = $montoTotal;
        $this->porcentajeComercio = $porcentajeComercio;
        $this->montoComercio = $montoTotal * ($porcentajeComercio / 100);
        $this->montoEmpresa = $montoTotal - $this->montoComercio;
    }

    // --- Getters ---
    public function id(): Uuid { return $this->id; }
    public function idMaquina(): Uuid { return $this->idMaquina; }
    public function idUsuario(): Uuid { return $this->idUsuario; }
    public function tipoComercio(): string { return $this->tipoComercio; }
    public function montoTotal(): float { return $this->montoTotal; }
    public function montoEmpresa(): float { return $this->montoEmpresa; }
    public function montoComercio(): float { return $this->montoComercio; }
    public function porcentajeComercio(): float { return $this->porcentajeComercio; }
    public function detalle(): string { return $this->detalle; }
    public function fecha(): DateTimeImmutable { return $this->fecha; }
}