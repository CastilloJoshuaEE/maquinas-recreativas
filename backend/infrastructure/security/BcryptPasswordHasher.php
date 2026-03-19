<?php
/**
 * RecreaSys - Infrastructure Security
 *
 * Implementación de Bcrypt para el hashing de contraseñas.
 *
 * @package RecreaSys\Infrastructure\Security
 * @author Tu Equipo
 * @version 1.0
 */

namespace RecreaSys\Infrastructure\Security;

use InvalidArgumentException;

/**
 * Class BcryptPasswordHasher
 */
class BcryptPasswordHasher implements PasswordHasher
{
    private int $cost;

    /**
     * BcryptPasswordHasher constructor.
     *
     * @param int $cost El costo computacional del algoritmo (4-31).
     */
    public function __construct(int $cost = 12)
    {
        if ($cost < 4 || $cost > 31) {
            throw new InvalidArgumentException('El costo debe estar entre 4 y 31.');
        }
        $this->cost = $cost;
    }

    /**
     * @inheritDoc
     */
    public function hash(string $plainPassword): string
    {
        if (strlen($plainPassword) < 8) {
            throw new InvalidArgumentException('La contraseña debe tener al menos 8 caracteres.');
        }
        return password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => $this->cost]);
    }

    /**
     * @inheritDoc
     */
    public function verify(string $plainPassword, string $hashedPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }
}