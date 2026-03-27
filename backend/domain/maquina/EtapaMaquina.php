<?php
/**
 * domain/maquina/EtapaMaquina.php
 *
 * Value Object que representa las etapas del ciclo de vida de una máquina recreativa.
 *
 * @package maquinas_recreativas\Domain\Maquina
 */

namespace maquinas_recreativas\Domain\Maquina;

use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class EtapaMaquina
 *
 * Representa una etapa inmutable dentro del proceso de la máquina.
 * Las etapas posibles son: Montaje, Distribución y Recaudación.
 */
final class EtapaMaquina
{
    private const MONTAJE = 'Montaje';
    private const DISTRIBUCION = 'Distribucion';
    private const RECAUDACION = 'Recaudacion';

    private string $value;

    /**
     * Constructor privado.
     *
     * @param string $value
     */
    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Crea una instancia desde un string.
     *
     * @param string $etapa
     * @return self
     * @throws DomainException
     */
    public static function fromString(string $etapa): self
    {
        $validStages = [self::MONTAJE, self::DISTRIBUCION, self::RECAUDACION];

        if (!in_array($etapa, $validStages, true)) {
            throw new DomainException("Etapa de máquina no válida: {$etapa}");
        }

        return new self($etapa);
    }

    // --- Fábricas para cada etapa ---
    public static function MONTAJE(): self { return new self(self::MONTAJE); }
    public static function DISTRIBUCION(): self { return new self(self::DISTRIBUCION); }
    public static function RECAUDACION(): self { return new self(self::RECAUDACION); }

    /**
     * Devuelve el valor de la etapa.
     *
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Compara si dos etapas son iguales.
     *
     * @param self $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}