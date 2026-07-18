<?php
/**
 * domain/reporte/EstadoReporte.php
 *
 * Value Object para los estados de un reporte.
 *
 * @package maquinas_recreativas\Domain\Reporte
 */

namespace maquinas_recreativas\Domain\Reporte;

use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class EstadoReporte
 */
class EstadoReporte
{
    private const PENDIENTE = 'Pendiente';
    private const EN_PROCESO = 'En proceso';
    private const RESUELTO = 'Resuelto';

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Crea una instancia desde un string.
     *
     * @param string $estado
     * @return self
     * @throws DomainException
     */
    public static function fromString(string $estado): self
    {
        $validStates = [self::PENDIENTE, self::EN_PROCESO, self::RESUELTO];

        if (!in_array($estado, $validStates, true)) {
            throw new DomainException("Estado de reporte no válido: {$estado}");
        }

        return new self($estado);
    }

    // --- Fábricas para cada estado ---
    public static function PENDIENTE(): self { return new self(self::PENDIENTE); }
    public static function EN_PROCESO(): self { return new self(self::EN_PROCESO); }
    public static function RESUELTO(): self { return new self(self::RESUELTO); }

    /**
     * Devuelve el valor del estado.
     *
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Compara si dos estados son iguales.
     *
     * @param self $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Verifica si el estado es pendiente.
     */
    public function esPendiente(): bool
    {
        return $this->value === self::PENDIENTE;
    }

    /**
     * Verifica si el estado está en proceso.
     */
    public function esEnProceso(): bool
    {
        return $this->value === self::EN_PROCESO;
    }

    /**
     * Verifica si el estado es resuelto.
     */
    public function esResuelto(): bool
    {
        return $this->value === self::RESUELTO;
    }
}