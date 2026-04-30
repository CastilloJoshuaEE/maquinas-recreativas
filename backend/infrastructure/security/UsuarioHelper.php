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

use maquinas_recreativas\Infrastructure\Database\Database;

/**
 * Class UsuarioHelper
 *
 * Proporciona métodos utilitarios para la gestión de usuarios,
 * como generación de nombres de usuario únicos.
 */
class UsuarioHelper
{
    /**
     * Generar un nombre de usuario único basado en nombre y apellido.
     *
     * @param string $nombre Nombre del usuario.
     * @param string $apellido Apellido del usuario.
     * @param string|null $tipo Tipo de usuario (Tecnico, Logistica, etc.)
     * @return string Nombre de usuario generado.
     */
    public static function generarUsuarioAsignado(string $nombre, string $apellido, ?string $tipo = null): string
    {
        // Limpiar y normalizar el texto
        $nombre = self::normalizarTexto($nombre);
        $apellido = self::normalizarTexto($apellido);

        // Obtener primeras letras
        $primeraLetraNombre = substr($nombre, 0, 1);
        $primerasLetrasApellido = substr($apellido, 0, 3);

        // Definir prefijo según el tipo de usuario
        $prefijo = self::obtenerPrefijo($tipo);

        // Base del usuario
        $baseUsuario = $prefijo . $primeraLetraNombre . $primerasLetrasApellido;

        // Buscar un nombre único
        return self::generarUnico($baseUsuario);
    }

    /**
     * Normalizar texto (quitar acentos, convertir a minúsculas).
     *
     * @param string $texto Texto a normalizar.
     * @return string Texto normalizado.
     */
    private static function normalizarTexto(string $texto): string
    {
        $texto = trim($texto);
        $texto = strtolower($texto);

        // Reemplazar caracteres especiales
        $buscar = ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ', 'ç', ' ', '-', "'", '.'];
        $reemplazar = ['a', 'e', 'i', 'o', 'u', 'u', 'n', 'c', '', '', '', ''];

        return str_replace($buscar, $reemplazar, $texto);
    }

    /**
     * Obtener prefijo según tipo de usuario.
     *
     * @param string|null $tipo Tipo de usuario.
     * @return string Prefijo.
     */
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

    /**
     * Generar nombre de usuario único.
     *
     * @param string $base Base del nombre.
     * @return string Nombre único.
     */
    private static function generarUnico(string $base): string
    {
        $conn = (new Database())->getConnection();

        // Intentar con el base primero
        if (!self::existeUsuario($conn, $base)) {
            return $base;
        }

        // Si existe, añadir números
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

        // Si llegamos aquí, generar con timestamp
        return $base . date('ymd');
    }

    /**
     * Verificar si un nombre de usuario ya existe.
     *
     * @param \mysqli $conn Conexión a la base de datos.
     * @param string $usuario Nombre de usuario a verificar.
     * @return bool True si existe, false en caso contrario.
     */
    private static function existeUsuario(\mysqli $conn, string $usuario): bool
    {
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE usuario_asignado = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'] > 0;
    }

    /**
     * Generar nombre de usuario a partir de email.
     *
     * @param string $email Correo electrónico.
     * @param string|null $tipo Tipo de usuario.
     * @return string Nombre de usuario generado.
     */
    public static function generarDesdeEmail(string $email, ?string $tipo = null): string
    {
        $partes = explode('@', $email);
        $nombreBase = $partes[0];

        // Limpiar el nombre base
        $nombreBase = preg_replace('/[^a-z0-9]/i', '', $nombreBase);
        $nombreBase = strtolower($nombreBase);

        $prefijo = self::obtenerPrefijo($tipo);

        return self::generarUnico($prefijo . $nombreBase);
    }

    /**
     * Generar nombre de usuario aleatorio.
     *
     * @param string|null $tipo Tipo de usuario.
     * @return string Nombre de usuario generado.
     */
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