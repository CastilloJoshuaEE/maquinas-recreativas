<?php
/**
 * maquinas_recreativas - Domain Shared Value Object
 * 
 * Value Object para UUIDs
 * @package maquinas_recreativas\Domain\Shared\ValueObjects
 * @author Usuario <email> Tu Equipo
 * @version 2.0
 */
namespace maquinas_recreativas\Domain\Shared\ValueObjects;
use InvalidArgumentException;  
/**
 * Class Uuid 
 * 
 * Representa un identificador único universal(UUID) como un Value Object inmutable.
 * Garantiza qe el ID tenga un formato válido en todo el dominio.
 * 
 * */ 
final class Uuid{
    private string $value;
    /**
     * Uuid constructor
     * @param string $value El valor del UUID.
     * @throws InvalidArgumentException Si el valor no es un UUID válido.
     */
    public function __construct(string $value){
        $this->ensureIsValidUuid($value);
        $this->value = $value;
    }
    /**
     * Valida que el string proporcionado sea un UUID válido
     * @param string $value El valor a validar.
     * @return void
     * @throws InvalidArgumentException
     * 
     */
    private function ensureIsValidUuid(string $value): void{
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value)) {
            throw new InvalidArgumentException(sprintf('El valor "%s" no es un UUID válido.', $value));
        }
    }
    /**
     * Obtiene el valor del UUID.
     * @return string
     * 
     */
    public function value(): string{
        return $this->value;
    }    
    /**
     * Compara si este Uuid es igual a otro
     * @param Uuid $other
     * @return bool
     */
    public function equals(Uuid $other): bool{
        return $this->value() === $other->value();
    }
    /**
     * Genera un nuevo UUID aleatorio.
     * @return self
     */
    public static function random():self{
        $data = random_bytes(16);
        $data[6]=chr (ord($data[6]) &0x0f| 0x40); // Versión 4(0100)
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Variante (10xx)
        return new self(vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4)));
    }
    /**
     * Devuelve el UUID como string
     * @return string
     */
    public function __toString(): string
    {
        return $this->value();
    }



}
