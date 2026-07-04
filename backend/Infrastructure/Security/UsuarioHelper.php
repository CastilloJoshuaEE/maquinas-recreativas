<?php
/**
 * backend/infrastructure/security/UsuarioHelper.php
 *
 * Helper para generación y validación de datos de usuario.
 *
 * @package maquinas_recreativas\Infrastructure\Security
 * @author Tu Equipo
 * @version 1.0
 */

namespace maquinas_recreativas\Infrastructure\Security;

use PDO;
use maquinas_recreativas\Infrastructure\Database\Database;

class UsuarioHelper
{
    public static function generarUsuarioAsignado(string $nombre, string $apellido, ?string $tipo = null): string
    {
        $nombre = self::normalizarTexto($nombre);
        $apellido = self::normalizarTexto($apellido);

        $primeraLetraNombre = substr($nombre, 0, 1);
        $primerasLetrasApellido = substr($apellido, 0, 3);

        $prefijo = self::obtenerPrefijo($tipo);
        $baseUsuario = $prefijo . $primeraLetraNombre . $primerasLetrasApellido;

        return self::generarUnico($baseUsuario);
    }

    private static function normalizarTexto(string $texto): string
    {
        $texto = trim($texto);
        $texto = strtolower($texto);

        $buscar = ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ', 'ç', ' ', '-', "'", '.'];
        $reemplazar = ['a', 'e', 'i', 'o', 'u', 'u', 'n', 'c', '', '', '', ''];

        return str_replace($buscar, $reemplazar, $texto);
    }

    private static function obtenerPrefijo(?string $tipo): string
    {
        return match ($tipo) {
            'Tecnico' => 'tec',
            'Logistica' => 'log',
            'Contabilidad' => 'con',
            'Administrador' => 'adm',
            default => 'usr',
        };
    }

    private static function generarUnico(string $base): string
    {
        $db = new Database();
        $conn = $db->getConnection();

        if (!self::existeUsuario($conn, $base)) {
            return $base;
        }

        $contador = 1;
        $maxIntentos = 1000;

        while ($contador <= $maxIntentos) {
            $numero = str_pad($contador, 2, '0', STR_PAD_LEFT);
            $usuario = $base . $numero;

            if (!self::existeUsuario($conn, $usuario)) {
                return $usuario;
            }

            $contador++;
        }

        return $base . date('ymd');
    }

    private static function existeUsuario(PDO $conn, string $usuario): bool
    {
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE usuario_asignado = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$usuario]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'] > 0;
    }

    public static function generarDesdeEmail(string $email, ?string $tipo = null): string
    {
        $partes = explode('@', $email);
        $nombreBase = $partes[0];

        $nombreBase = preg_replace('/[^a-z0-9]/i', '', $nombreBase);
        $nombreBase = strtolower($nombreBase);

        $prefijo = self::obtenerPrefijo($tipo);

        return self::generarUnico($prefijo . $nombreBase);
    }

    public static function generarAleatorio(?string $tipo = null): string
    {
        $adjetivos = ['rojo', 'azul', 'verde', 'rapido', 'lento', 'fuerte', 'agil', 'brillante'];
        $sustantivos = ['tigre', 'leon', 'aguila', 'lobo', 'pez', 'gato', 'perro', 'caballo'];

        $adjetivo = $adjetivos[array_rand($adjetivos)];
        $sustantivo = $sustantivos[array_rand($sustantivos)];

        $base = $adjetivo . $sustantivo;
        $prefijo = self::obtenerPrefijo($tipo);

        return self::generarUnico($prefijo . $base);
    }
}