<?php
/**
 * maquinas_recreativas - Infrastructure Security
 *
 * Interfaz para el servicio de hashing de contraseñas.
 *
 * @package maquinas_recreativas\Infrastructure\Security
 * @author Tu Equipo
 * @version 1.0
 */

namespace maquinas_recreativas\Infrastructure\Security;

/**
 * Interface PasswordHasher
 *
 * Define el contrato para los servicios de hashing de contraseñas.
 */
interface PasswordHasher
{
    /**
     * Hashea una contraseña en texto plano.
     *
     * @param string $plainPassword
     * @return string
     */
    public function hash(string $plainPassword): string;

    /**
     * Verifica si una contraseña en texto plano coincide con un hash.
     *
     * @param string $plainPassword
     * @param string $hashedPassword
     * @return bool
     */
    public function verify(string $plainPassword, string $hashedPassword): bool;
}