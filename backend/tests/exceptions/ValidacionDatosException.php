<?php
/**
 * Excepción personalizada para errores de validación de datos
 * 
 * Esta excepción se lanza cuando falla la validación de datos en el sistema,
 * como contraseñas cortas, emails inválidos, etc.
 */
class ValidacionDatosException extends Exception {
    /**
     * @var array Detalles adicionales del error de validación
     */
    private $detalles;

    /**
     * Constructor de la excepción
     * 
     * @param string $message Mensaje descriptivo del error
     * @param int $code Código de error (usar códigos > 1000 para errores de validación)
     * @param array $detalles Datos adicionales sobre el error (opcional)
     * @param Throwable|null $previous Excepción previa (para encadenamiento)
     */
    public function __construct(
        string $message = "", 
        int $code = 0, 
        array $detalles = [], 
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->detalles = $detalles;
    }

    /**
     * Obtiene los detalles adicionales del error
     * 
     * @return array Detalles del error
     */
    public function getDetalles(): array {
        return $this->detalles;
    }

    /**
     * Representación string de la excepción
     * 
     * @return string
     */
    public function __toString(): string {
        $base = parent::__toString();
        
        if (!empty($this->detalles)) {
            $base .= "\nDetalles: " . json_encode($this->detalles, JSON_PRETTY_PRINT);
        }
        
        return $base;
    }

    /**
     *    
     * CP-006
     * Prueba el registro fallido con contraseña corta.
     * Factory method para crear una excepción de validación de contraseña
     * 
     * @param int $minLength Longitud mínima requerida
     * @return ValidacionDatosException
     */
    public static function forPasswordTooShort(int $minLength): ValidacionDatosException {
        return new self(
            "La contraseña debe tener al menos {$minLength} caracteres",
            1200,
            ['min_length' => $minLength]
        );
    }

    /**
     * Factory method para crear una excepción de email inválido
     * 
     * @param string $email Email inválido
     * @return ValidacionDatosException
     */
    public static function forInvalidEmail(string $email): ValidacionDatosException {
        return new self(
            "El email proporcionado no es válido",
            1201,
            ['email' => $email]
        );
    }
}