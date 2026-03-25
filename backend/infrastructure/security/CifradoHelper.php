<?php
/**
 * backend/infrastructure/security/CifradoHelper.php
 *
 * Helper para encriptación/desencriptación de datos sensibles.
 *
 * @package maquinas_recreativas\Infrastructure\Security
 * @author Tu Equipo
 * @version 1.0
 */

namespace maquinas_recreativas\Infrastructure\Security;

/**
 * Class CifradoHelper
 *
 * Proporciona métodos para encriptar y desencriptar datos sensibles
 * utilizando AES-256-CBC.
 */
class CifradoHelper
{
    /**
     * Encripta una cadena de texto.
     *
     * @param string $cadena Cadena a encriptar.
     * @return string Cadena encriptada en base64.
     */
    public static function encriptar(string $cadena): string
    {
        $key = hash('sha256', SECRET_KEY);
        $iv = substr(hash('sha256', SECRET_IV), 0, 16);
        return base64_encode(openssl_encrypt($cadena, ENCRYPT_METHOD, $key, 0, $iv));
    }

    /**
     * Desencripta una cadena de texto.
     *
     * @param string $cadenaCifrada Cadena encriptada en base64.
     * @return string Cadena desencriptada.
     */
    public static function desencriptar(string $cadenaCifrada): string
    {
        $key = hash('sha256', SECRET_KEY);
        $iv = substr(hash('sha256', SECRET_IV), 0, 16);
        return openssl_decrypt(base64_decode($cadenaCifrada), ENCRYPT_METHOD, $key, 0, $iv);
    }
}