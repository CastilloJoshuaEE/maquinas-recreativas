<?php
/**
 * backend/infrastructure/security/ValidationHelper.php
 *
 * Helper para validaciones comunes.
 *
 * @package maquinas_recreativas\Infrastructure\Security
 * @author Tu Equipo
 * @version 1.0
 */

namespace maquinas_recreativas\Infrastructure\Security;

/**
 * Class ValidationHelper
 *
 * Proporciona métodos estáticos para validar datos comunes
 * como UUID, email, contraseñas, etc.
 */
class ValidationHelper
{
    /**
     * Valida si un string es un UUID válido.
     *
     * @param string $uuid UUID a validar.
     * @return bool True si es válido, false en caso contrario.
     */
    public static function isValidUUID(string $uuid): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid) === 1;
    }

    /**
     * Sanitiza una entrada para prevenir XSS.
     *
     * @param mixed $data Datos a sanitizar.
     * @return mixed Datos sanitizados.
     */
    public static function sanitizeInput($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Valida si un string es un email válido.
     *
     * @param string $email Email a validar.
     * @return bool True si es válido, false en caso contrario.
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida si una contraseña cumple con los requisitos mínimos.
     *
     * @param string $password Contraseña a validar.
     * @return bool True si es válida, false en caso contrario.
     */
    public static function validatePassword(string $password): bool
    {
        return strlen($password) >= 8;
    }
}