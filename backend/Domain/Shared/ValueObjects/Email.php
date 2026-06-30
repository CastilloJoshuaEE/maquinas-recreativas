<?php

declare(strict_types=1);

namespace maquinas_recreativas\Domain\Shared\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object para representar un correo electrónico validado.
 * 
 * @package maquinas_recreativas\Domain\Shared\ValueObjects
 * @version 1.0
 */
final class Email
{
    private string $value;
    /**
     * Constructor del Email.
     *
     * @param string $email Dirección de correo electrónico.
     * @throws InvalidArgumentException Si el email no tiene un formato válido.
     */
    public function __construct(string $email){
        $this->validate($email);
        $this->value = $email;
    }
    /**
     * Valida el formato del email.
     *
     * @param string $email Email a validar.
     * @return void
     * @throws InvalidArgumentException
     */
private function validate(string $email): void
{
    // Validación estándar
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return;
    }

    // Permitir dominios internos tipo @admin
    if (preg_match('/^[a-zA-Z0-9._%+-]+@admin$/', $email)) {
        return;
    }

    throw new InvalidArgumentException("El email '{$email}' no tiene un formato válido.");
}
    /**
     * Obtiene el valor del email.
     *
     * @return string
     */
    public function value():string{
        return $this->value;
    }    
    /**
     * Compara si este Email es igual a otro Value Object.
     *
     * @param Email $other Otro objeto Email.
     * @return bool
     */
    public function equals(Email $other):bool{
        return $this->value() === $other->value();
    }
    /**
     * Representación en string del Value Object.
     *
     * @return string
     */    
    public function __toString(): string
    {
        return $this->value;
    }    

}