<?php
/**
 * Entidad de dominio Comercio
 * 
 * @package Domain\Comercio
 * @author Tu Nombre
 * @version 1.0.0
 */

namespace Domain\Comercio;

use Domain\Shared\ValueObjects\Uuid;
use Domain\Shared\Exceptions\DomainException;

/**
 * @package Domain\Comercio
 * 
 * Entidad que representa un comercio en el dominio del sistema.
 * Encapsula la lógica de negocio relacionada con comercios.
 */
class Comercio {
    
    /**
     * @var string ID único del comercio (UUID)
     */
    private $id;
    
    /**
     * @var string Nombre del comercio
     */
    private $nombre;
    
    /**
     * @var string Tipo de comercio (Minorista/Mayorista)
     */
    private $tipo;
    
    /**
     * @var string Dirección del comercio
     */
    private $direccion;
    
    /**
     * @var string Teléfono de contacto
     */
    private $telefono;
    
    /**
     * @var int Cantidad de máquinas asignadas
     */
    private $cantidadMaquinas = 0;
    
    /**
     * @var string|null Fecha de registro
     */
    private $fechaRegistro;
    
    /**
     * Constructor de la entidad Comercio
     * 
     * @param string $nombre Nombre del comercio
     * @param string $tipo Tipo de comercio
     * @param string $direccion Dirección
     * @param string $telefono Teléfono
     * @throws DomainException Si algún valor es inválido
     */
    public function __construct(
        string $nombre,
        string $tipo,
        string $direccion,
        string $telefono
    ) {
        $this->setNombre($nombre);
        $this->setTipo($tipo);
        $this->setDireccion($direccion);
        $this->setTelefono($telefono);
        $this->id = Uuid::generate();
        $this->fechaRegistro = date('Y-m-d H:i:s');
    }
    
    /**
     * Establece el nombre del comercio
     * 
     * @param string $nombre
     * @return self
     * @throws DomainException Si el nombre es inválido
     */
    private function setNombre(string $nombre): self {
        $nombre = trim($nombre);
        
        if (empty($nombre)) {
            throw new DomainException(
                'El nombre del comercio no puede estar vacío',
                DomainException::HTTP_BAD_REQUEST
            );
        }
        
        if (strlen($nombre) < 3) {
            throw new DomainException(
                'El nombre del comercio debe tener al menos 3 caracteres',
                DomainException::HTTP_BAD_REQUEST
            );
        }
        
        if (strlen($nombre) > 100) {
            throw new DomainException(
                'El nombre del comercio no puede exceder los 100 caracteres',
                DomainException::HTTP_BAD_REQUEST
            );
        }
        
        $this->nombre = $nombre;
        return $this;
    }
    
    /**
     * Establece el tipo de comercio
     * 
     * @param string $tipo
     * @return self
     * @throws DomainException Si el tipo es inválido
     */
    private function setTipo(string $tipo): self {
        $tiposPermitidos = ['Minorista', 'Mayorista'];
        
        if (!in_array($tipo, $tiposPermitidos)) {
            throw new DomainException(
                'Tipo de comercio inválido. Debe ser Minorista o Mayorista',
                DomainException::HTTP_BAD_REQUEST
            );
        }
        
        $this->tipo = $tipo;
        return $this;
    }
    
    /**
     * Establece la dirección
     * 
     * @param string $direccion
     * @return self
     * @throws DomainException Si la dirección es inválida
     */
    private function setDireccion(string $direccion): self {
        $direccion = trim($direccion);
        
        if (empty($direccion)) {
            throw new DomainException(
                'La dirección no puede estar vacía',
                DomainException::HTTP_BAD_REQUEST
            );
        }
        
        if (strlen($direccion) > 200) {
            throw new DomainException(
                'La dirección no puede exceder los 200 caracteres',
                DomainException::HTTP_BAD_REQUEST
            );
        }
        
        $this->direccion = $direccion;
        return $this;
    }
    
    /**
     * Establece el teléfono
     * 
     * @param string $telefono
     * @return self
     * @throws DomainException Si el teléfono es inválido
     */
    private function setTelefono(string $telefono): self {
        $telefono = trim($telefono);
        
        if (empty($telefono)) {
            throw new DomainException(
                'El teléfono no puede estar vacío',
                DomainException::HTTP_BAD_REQUEST
            );
        }
        
        // Validar formato básico (solo números, +, -, espacios)
        if (!preg_match('/^[0-9+\-\s]+$/', $telefono)) {
            throw new DomainException(
                'El teléfono contiene caracteres inválidos',
                DomainException::HTTP_BAD_REQUEST
            );
        }
        
        $this->telefono = $telefono;
        return $this;
    }
    
    /**
     * Obtiene el ID del comercio
     * 
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }
    
    /**
     * Obtiene el nombre
     * 
     * @return string
     */
    public function getNombre(): string {
        return $this->nombre;
    }
    
    /**
     * Obtiene el tipo
     * 
     * @return string
     */
    public function getTipo(): string {
        return $this->tipo;
    }
    
    /**
     * Obtiene la dirección
     * 
     * @return string
     */
    public function getDireccion(): string {
        return $this->direccion;
    }
    
    /**
     * Obtiene el teléfono
     * 
     * @return string
     */
    public function getTelefono(): string {
        return $this->telefono;
    }
    
    /**
     * Obtiene la cantidad de máquinas
     * 
     * @return int
     */
    public function getCantidadMaquinas(): int {
        return $this->cantidadMaquinas;
    }
    
    /**
     * Obtiene la fecha de registro
     * 
     * @return string|null
     */
    public function getFechaRegistro(): ?string {
        return $this->fechaRegistro;
    }
    
    /**
     * Incrementa el contador de máquinas
     * 
     * @return self
     */
    public function incrementarMaquinas(): self {
        $this->cantidadMaquinas++;
        return $this;
    }
    
    /**
     * Decrementa el contador de máquinas
     * 
     * @return self
     * @throws DomainException Si no hay máquinas para decrementar
     */
    public function decrementarMaquinas(): self {
        if ($this->cantidadMaquinas <= 0) {
            throw new DomainException(
                'No hay máquinas para decrementar',
                DomainException::HTTP_CONFLICT
            );
        }
        $this->cantidadMaquinas--;
        return $this;
    }
    
    /**
     * Actualiza los datos del comercio
     * 
     * @param string $nombre
     * @param string $tipo
     * @param string $direccion
     * @param string $telefono
     * @return self
     */
    public function actualizar(
        string $nombre,
        string $tipo,
        string $direccion,
        string $telefono
    ): self {
        $this->setNombre($nombre);
        $this->setTipo($tipo);
        $this->setDireccion($direccion);
        $this->setTelefono($telefono);
        return $this;
    }
    
    /**
     * Convierte la entidad a array
     * 
     * @return array
     */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'tipo' => $this->tipo,
            'direccion' => $this->direccion,
            'telefono' => $this->telefono,
            'cantidad_maquinas' => $this->cantidadMaquinas,
            'fecha_registro' => $this->fechaRegistro
        ];
    }
}