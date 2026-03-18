<?php
require_once __DIR__ . '/../config/database.php';

class UsuarioHelper {
    
    /**
     * Generar un nombre de usuario único basado en nombre y apellido
     */
    public static function generarUsuarioAsignado($nombre, $apellido, $tipo = null) {
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
     * Normalizar texto (quitar acentos, convertir a minúsculas)
     */
    private static function normalizarTexto($texto) {
        $texto = trim($texto);
        $texto = strtolower($texto);
        
        // Reemplazar caracteres especiales
        $buscar = ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ', 'ç', ' ', '-', "'", '.'];
        $reemplazar = ['a', 'e', 'i', 'o', 'u', 'u', 'n', 'c', '', '', '', ''];
        
        return str_replace($buscar, $reemplazar, $texto);
    }
    
    /**
     * Obtener prefijo según tipo de usuario
     */
    private static function obtenerPrefijo($tipo) {
        switch ($tipo) {
            case 'Tecnico':
                return 'tec';
            case 'Logistica':
                return 'log';
            case 'Contabilidad':
                return 'con';
            case 'Administrador':
                return 'adm';
            case 'Usuario':
            default:
                return 'usr';
        }
    }
    
    /**
     * Generar nombre de usuario único
     */
    private static function generarUnico($base) {
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
     * Verificar si un nombre de usuario ya existe
     */
    private static function existeUsuario($conn, $usuario) {
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE usuario_asignado = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['total'] > 0;
    }
    
    /**
     * Generar nombre de usuario a partir de email
     */
    public static function generarDesdeEmail($email, $tipo = null) {
        $partes = explode('@', $email);
        $nombreBase = $partes[0];
        
        // Limpiar el nombre base
        $nombreBase = preg_replace('/[^a-z0-9]/i', '', $nombreBase);
        $nombreBase = strtolower($nombreBase);
        
        $prefijo = self::obtenerPrefijo($tipo);
        
        return self::generarUnico($prefijo . $nombreBase);
    }
    
    /**
     * Generar nombre de usuario aleatorio
     */
    public static function generarAleatorio($tipo = null) {
        $adjetivos = ['rojo', 'azul', 'verde', 'rapido', 'lento', 'fuerte', 'agil', 'brillante'];
        $sustantivos = ['tigre', 'leon', 'aguila', 'lobo', 'pez', 'gato', 'perro', 'caballo'];
        
        $adjetivo = $adjetivos[array_rand($adjetivos)];
        $sustantivo = $sustantivos[array_rand($sustantivos)];
        
        $base = $adjetivo . $sustantivo;
        $prefijo = self::obtenerPrefijo($tipo);
        
        return self::generarUnico($prefijo . $base);
    }
}
?>