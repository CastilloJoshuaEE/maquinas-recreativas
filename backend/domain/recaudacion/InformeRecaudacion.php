<?php
/**
 * domain/recaudacion/InformeRecaudacion.php
 *
 * Entidad que representa el informe detallado de una recaudación.
 *
 * @package Reconocimiento\Domain\Recaudacion
 */

namespace Reconocimiento\Domain\Recaudacion;

use Reconocimiento\Domain\Shared\ValueObjects\Uuid;

/**
 * Class InformeRecaudacion
 */
class InformeRecaudacion
{
    private Uuid $id;
    private Uuid $idRecaudacion;
    private string $ciUsuario;
    private string $nombreMaquina;
    private Uuid $idComercio;
    private string $nombreComercio;
    private string $direccionComercio;
    private string $telefonoComercio;
    private float $pagoEnsamblador;
    private float $pagoComprobador;
    private float $pagoMantenimiento;
    private string $empresaNombre;
    private string $empresaDescripcion;

    private function __construct(
        Uuid $id,
        Uuid $idRecaudacion,
        string $ciUsuario,
        string $nombreMaquina,
        Uuid $idComercio,
        string $nombreComercio,
        string $direccionComercio,
        string $telefonoComercio,
        float $pagoEnsamblador,
        float $pagoComprobador,
        float $pagoMantenimiento,
        string $empresaNombre,
        string $empresaDescripcion
    ) {
        $this->id = $id;
        $this->idRecaudacion = $idRecaudacion;
        $this->ciUsuario = $ciUsuario;
        $this->nombreMaquina = $nombreMaquina;
        $this->idComercio = $idComercio;
        $this->nombreComercio = $nombreComercio;
        $this->direccionComercio = $direccionComercio;
        $this->telefonoComercio = $telefonoComercio;
        $this->pagoEnsamblador = $pagoEnsamblador;
        $this->pagoComprobador = $pagoComprobador;
        $this->pagoMantenimiento = $pagoMantenimiento;
        $this->empresaNombre = $empresaNombre;
        $this->empresaDescripcion = $empresaDescripcion;
    }

    public static function crear(
        Uuid $idRecaudacion,
        string $ciUsuario,
        string $nombreMaquina,
        Uuid $idComercio,
        string $nombreComercio,
        string $direccionComercio,
        string $telefonoComercio,
        float $pagoEnsamblador = 400.00,
        float $pagoComprobador = 400.00,
        float $pagoMantenimiento = 0.00
    ): self {
        return new self(
            Uuid::v4(),
            $idRecaudacion,
            $ciUsuario,
            $nombreMaquina,
            $idComercio,
            $nombreComercio,
            $direccionComercio,
            $telefonoComercio,
            $pagoEnsamblador,
            $pagoComprobador,
            $pagoMantenimiento,
            'Recrea Sys S.A.',
            'Empresa encargada en el ciclo de vida de las máquinas recreativas'
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            new Uuid($data['ID_Informe']),
            new Uuid($data['ID_Recaudacion']),
            $data['CI_Usuario'],
            $data['Nombre_Maquina'],
            new Uuid($data['ID_Comercio']),
            $data['Nombre_Comercio'],
            $data['Direccion_Comercio'],
            $data['Telefono_Comercio'],
            (float)$data['Pago_Ensamblador'],
            (float)$data['Pago_Comprobador'],
            (float)($data['Pago_Mantenimiento'] ?? 0),
            $data['empresa_nombre'],
            $data['empresa_descripcion']
        );
    }

    public function toArray(): array
    {
        return [
            'ID_Informe' => $this->id->value(),
            'ID_Recaudacion' => $this->idRecaudacion->value(),
            'CI_Usuario' => $this->ciUsuario,
            'Nombre_Maquina' => $this->nombreMaquina,
            'ID_Comercio' => $this->idComercio->value(),
            'Nombre_Comercio' => $this->nombreComercio,
            'Direccion_Comercio' => $this->direccionComercio,
            'Telefono_Comercio' => $this->telefonoComercio,
            'Pago_Ensamblador' => $this->pagoEnsamblador,
            'Pago_Comprobador' => $this->pagoComprobador,
            'Pago_Mantenimiento' => $this->pagoMantenimiento,
            'empresa_nombre' => $this->empresaNombre,
            'empresa_descripcion' => $this->empresaDescripcion
        ];
    }

    // --- Getters ---
    public function id(): Uuid { return $this->id; }
    public function idRecaudacion(): Uuid { return $this->idRecaudacion; }
    public function ciUsuario(): string { return $this->ciUsuario; }
    public function nombreMaquina(): string { return $this->nombreMaquina; }
    public function idComercio(): Uuid { return $this->idComercio; }
    public function nombreComercio(): string { return $this->nombreComercio; }
    public function direccionComercio(): string { return $this->direccionComercio; }
    public function telefonoComercio(): string { return $this->telefonoComercio; }
    public function pagoEnsamblador(): float { return $this->pagoEnsamblador; }
    public function pagoComprobador(): float { return $this->pagoComprobador; }
    public function pagoMantenimiento(): float { return $this->pagoMantenimiento; }
}