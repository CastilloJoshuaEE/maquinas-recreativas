<?php
/**
 * domain/shared/valueobjects/Uuid.php
 *
 * Value Object para UUIDs.
 *
 * @package maquinas_recreativas\Domain\Shared\ValueObjects
 */

namespace maquinas_recreativas\Domain\Shared\ValueObjects;

/**
 * Class Uuid
 */
final class Uuid
{
    private string $value;

    public function __construct(string $value)
    {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value)) {
            throw new \InvalidArgumentException('Invalid UUID format');
        }
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(Uuid $other): bool
    {
        return $this->value === $other->value();
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Genera un nuevo UUID v4.
     *
     * @return self
     */
    public static function v4(): self
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        
        return new self(vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4)));
    }

    /**
     * Alias de v4() para compatibilidad.
     *
     * @return self
     */
    public static function generar(): self
    {
        return self::v4();
    }
}