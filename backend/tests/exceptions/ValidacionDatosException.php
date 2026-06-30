<?php
/**
 * Excepción personalizada para errores de validación de datos
 * 
 * Esta excepción se lanza cuando falla la validación de datos en el sistema,
 * como contraseñas cortas, emails inválidos, etc.
 */

namespace maquinas_recreativas\Tests\Exceptions;

use Exception;
use Throwable;

class ValidacionDatosException extends Exception
{
    /**
     * @var array Detalles adicionales del error de validación
     */
    private array $detalles;

    /**
     * Constructor de la excepción
     * 
     * @param string $message Mensaje descriptivo del error
     * @param int $code Código de error
     * @param array $detalles Datos adicionales sobre el error
     * @param Throwable|null $previous Excepción previa
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        array $detalles = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->detalles = $detalles;
    }

    /**
     * Obtiene los detalles adicionales del error
     * 
     * @return array
     */
    public function getDetalles(): array
    {
        return $this->detalles;
    }

    /**
     * Factory method para crear una excepción de contraseña corta
     * 
     * @param int $minLength Longitud mínima requerida
     * @return self
     */
    public static function forPasswordTooShort(int $minLength): self
    {
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
     * @return self
     */
    public static function forInvalidEmail(string $email): self
    {
        return new self(
            "El email proporcionado no es válido",
            1201,
            ['email' => $email]
        );
    }
}