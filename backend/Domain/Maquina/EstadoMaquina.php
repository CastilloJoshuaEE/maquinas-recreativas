<?php
/**
 * domain/maquina/EstadoMaquina.php
 *
 * Value Object que representa los posibles estados de una máquina recreativa.
 *
 * @package maquinas_recreativas\Domain\Maquina
 */

namespace maquinas_recreativas\Domain\Maquina;

use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Clase EstadoMaquina
 */
class EstadoMaquina
{
    private const ENSAMBLANDOSE = 'Ensamblandose';
    private const REENSAMBLANDOSE = 'Reensamblandose';
    private const COMPROBANDOSE = 'Comprobandose';
    private const DISTRIBUYENDOSE = 'Distribuyendose';
    private const OPERATIVA = 'Operativa';
    private const NO_OPERATIVA = 'No operativa';
    private const RETIRADA = 'Retirada';

    private string $value;

    /**
     * Constructor privado.
     */
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
        $validStates = [
            self::ENSAMBLANDOSE,
            self::REENSAMBLANDOSE,
            self::COMPROBANDOSE,
            self::DISTRIBUYENDOSE,
            self::OPERATIVA,
            self::NO_OPERATIVA,
            self::RETIRADA
        ];

        if (!in_array($estado, $validStates, true)) {
            throw new DomainException("Estado de máquina no válido: {$estado}");
        }

        return new self($estado);
    }

    // --- Fábricas para cada estado ---
    public static function ENSAMBLANDOSE(): self { return new self(self::ENSAMBLANDOSE); }
    public static function REENSAMBLANDOSE(): self { return new self(self::REENSAMBLANDOSE); }
    public static function COMPROBANDOSE(): self { return new self(self::COMPROBANDOSE); }
    public static function DISTRIBUYENDOSE(): self { return new self(self::DISTRIBUYENDOSE); }
    public static function OPERATIVA(): self { return new self(self::OPERATIVA); }
    public static function NO_OPERATIVA(): self { return new self(self::NO_OPERATIVA); }
    public static function RETIRADA(): self { return new self(self::RETIRADA); }

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
}