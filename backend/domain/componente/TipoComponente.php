<?php
/**
 * domain/componente/TipoComponente.php
 *
 * Value Object para los tipos de componente.
 *
 * @package maquinas_recreativas\Domain\Componente
 */

namespace maquinas_recreativas\Domain\Componente;

use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class TipoComponente
 */
class TipoComponente
{
    private const LOGISTICO = 'Logistico';
    private const ELECTRONICO = 'Electronico';
    private const ESTRUCTURAL = 'Estructural';
    private const ACCESORIO = 'Accesorio';

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $tipo): self
    {
        $validTypes = [self::LOGISTICO, self::ELECTRONICO, self::ESTRUCTURAL, self::ACCESORIO];

        if (!in_array($tipo, $validTypes, true)) {
            throw new DomainException("Tipo de componente no válido: {$tipo}");
        }

        return new self($tipo);
    }

    public static function LOGISTICO(): self { return new self(self::LOGISTICO); }
    public static function ELECTRONICO(): self { return new self(self::ELECTRONICO); }
    public static function ESTRUCTURAL(): self { return new self(self::ESTRUCTURAL); }
    public static function ACCESORIO(): self { return new self(self::ACCESORIO); }

    public function value(): string { return $this->value; }
    public function equals(self $other): bool { return $this->value === $other->value; }
}