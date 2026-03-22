<?php

declare(strict_types=1);

namespace RecreaSys\Domain\Shared\Exceptions;

use Exception;

/**
 * Excepción base para errores del dominio.
 * Todas las excepciones específicas del negocio deben extender esta clase.
 *
 * @package RecreaSys\Domain\Shared\Exceptions
 * @version 1.0
 */
class DomainException extends Exception
{
    /**
     * Código de error específico del dominio (opcional).
     *
     * @var string|null
     */
    protected ?string $domainErrorCode = null;
    /**
     * Constructor de DomainException.
     *
     * @param string $message Mensaje de error.
     * @param string|null $domainErrorCode Código de error del dominio.
     * @param int $code Código HTTP/Exception estándar.
     * @param Exception|null $previous Excepción anterior.
     */    
    public function __construct(string $message = "", ?string $domainErrorCode=null, int $code = 0, ?Exception $previous = null){
        parent::__construct($message, $code, $previous);
        $this->domainErrorCode = $domainErrorCode;
    }
    /**
     * Obtiene el código de error del dominio.
     *
     * @return string|null
     */
    public function getDomainErrorCode():?string{
        return $this->domainErrorCode;
    }    


}