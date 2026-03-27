<?php
/**
 * RecreSys - Domain Usuario Entity
 * Define la entidad Usuario con sus reglas de negocio
 * @package maquinas_recreativas\Domain\Usuario
 * @author Usuario <email> TU Equipo
 * @version 1.0.0
 * 
 */
namespace maquinas_recreativas\Domain\Usuario;
use PhpParser\Node\Expr\Cast\Void_;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use InvalidArgumentException;
/**
 * Class Usuario
 * Representa la entidad principal de un usuario en el sistema
 * Contiene las reglas de negocio que debe cumplir un usuario.
 * 
 */
class Usuario{
    private Uuid $id;
    private string $nombre;
    private string $apellido;
    private string $email;
    private string $ci;
    private string $usuarioAsignado;
    private string $contrasenaHash;
    private string $tipo;
    private string $estado;
    private ?string $especialidad;
    /**
     * Tipos de usuario permitidos en el sistema
     * 
     */
    public const TIPOS_PERMITIDOS =['Tecnico', 'Logistica', 'Contabilidad', 'Administrador', 'Usuario'];
    /**
     * Estados de usuario permitidos
     * 
     */
    public const ESTADOS_PERMITIDOS =['Activo', 'Inactivo'];
    /**
     * Especialidades permitidas para técnicos
     */
    public const ESPECIALIDADES_TECNICO=['Ensamblador', 'Comprobador', 'Mantenimiento'];
    /**
     * Usuario constructor.
     * @param Uuid $id
     * @param string $nombre
     * @param string $apellido
     * @param string $email
     * @param string $ci
     * @param string $usuarioAsignado
     * @param string $contrasenaHash
     * @param string $tipo
     * @param string $estado
     * @param string|null $especialidad
     * @throws InvalidArgumentException
     */
    public function __construct(Uuid $id, string $nombre, string $apellido, string $email, string $ci,  string $usuarioAsignado, string $contrasenaHash,    string $tipo, string $estado, ?string $especialidad=null){
        $this->setId ($id);
        $this->setNombre($nombre);
        $this->setApellido($apellido);
        $this->setEmail($email);
        $this->setCi($ci);
        $this->setUsuarioAsignado($usuarioAsignado);
        $this->setContrasenaHash($contrasenaHash);
        $this->setTipo($tipo);
        $this->setestado($estado);
    } 
    /*---Getters--- */
    public function getId(): Uuid { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getApellido(): string { return $this->apellido; }
    public function getEmail(): string { return $this->email; }
    public function getCi(): string { return $this->ci; }
    public function getUsuarioAsignado(): string { return $this->usuarioAsignado; }
    public function getContrasenaHash(): string { return $this->contrasenaHash; }
    public function getTipo(): string { return $this->tipo; }
    public function getEstado(): string { return $this->estado; }
    public function getEspecialidad(): ?string { return $this->especialidad; }
    /* --- Reglas de negocio (Setters) --- */
    /**
     * @throws InvalidArgumentException
     */
    private function setId(Uuid $id) { $this->id = $id; }
    /**
     * @throws InvalidArgumentException
     */
    private function setNombre(string $nombre):void {
        $nombre = trim($nombre);
        if(empty($nombre)){
            throw new InvalidArgumentException('El nombre no puede estar vacío');

        }
        $this->nombre = $nombre;

    }
    /**
     * @throws InvalidArgumentException
     */
    private function setApellido(string $apellido):void {
        $apellido = trim($apellido);
        if(empty($apellido)){
            throw new InvalidArgumentException('El apellido no puede estar vacío.');
        }
        $this->apellido = $apellido;
    }
    /**
    * @throws InvalidArgumentException
    */
    private function setEmail(string $email):void {
        $email = trim($email);
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            throw new InvalidArgumentException('El email no tiene un formato válido');
        }
        $this->email=   $email;
    }
    /**
    * @throws InvalidArgumentException
    */
    private function setCi(string $ci):void{
        $ci = trim($ci);
        if(strlen($ci) <6){
            throw new InvalidArgumentException('La cédula debe tener al menos 6 caracteres.');
        }
        $this->ci = $ci;    
    }    
    /**
    * @throws InvalidArgumentException
    */
    private function setUsuarioAsignado(string $usuarioAsignado):void{
        $usuarioAsignado = trim($usuarioAsignado);
        if(strlen($usuarioAsignado) < 3){
            throw new InvalidArgumentException('El nombre de usuario debe tener al menos 3 caracteres');
        }
        $this->usuarioAsignado = $usuarioAsignado;
    
    }    
    /**
    * @throws InvalidArgumentException
    */
    private function setContrasenaHash(string $contrasenaHash): void{
        if(empty($contrasenaHash)){
            throw new InvalidArgumentException('El hash de la contraseña no puede estar vacío');
        }
        $this ->contrasenaHash = $contrasenaHash;
    }    
    /**
     * Establece el tipo y la especialidad, aplicando reglas de negocio.
     *
     * @param string $tipo
     * @param string|null $especialidad
     * @return void
     * @throws InvalidArgumentException
     */
    private function setTipo(string $tipo, ?string $especialidad = null): void {
        if (!in_array($tipo, self::TIPOS_PERMITIDOS, true)) {
            throw new InvalidArgumentException(sprintf('El tipo "%s" no es válido.', $tipo));
        }

        if ($tipo === 'Tecnico') {
            if ($especialidad === null || !in_array($especialidad, self::ESPECIALIDADES_TECNICO, true)) {
                throw new InvalidArgumentException(sprintf('La especialidad "%s" no es válida para un técnico.', $especialidad));
            }
            $this->especialidad = $especialidad;
        } else {
            $this->especialidad = null;
        }

        $this->tipo = $tipo;
    } 
    /**
     * @throws InvalidArgumentException
     */
    private function setEstado(string $estado):void{
        if(!in_array($estado, self::ESTADOS_PERMITIDOS, true)){
            throw new InvalidArgumentException(sprintf('El estado "%s" no es válido.', $estado));
            
        }
        $this->estado = $estado;
    }
    /* --- Comportamiento de la entidad --- */

    /**
     * Cambia el nombre de usuario.
     *
     * @param string $nuevoUsuarioAsignado
     * @return void
     * @throws InvalidArgumentException
     */
    public function cambiarUsuarioAsignado(string $nuevoUsuarioAsignado):void{
        $this->setUsuarioAsignado($nuevoUsuarioAsignado);
    }    
    /**
     * Cambia la contraseña del usuario.
     *
     * @param string $nuevaContrasenaHash
     * @return void
     * @throws InvalidArgumentException
     */
    public function cambiarContrasena(string $nuevaContrasenaHash):void{
        $this->setContrasenaHash($nuevaContrasenaHash);
    }
    /**
     * Activa al usuario.
     *
     * @return void
     */
    public function activar():void{
        $this->estado='Activo';
    }    
    /**
     * Desactiva al usuario
     * @return void
     */
    public function desactivar():void{
        $this->estado= 'Inactivo';
    }
    /**
     * Verifica si el usuario es de un tipo específico.
     * @param string $tipo
     * @return bool
     */
    public function esTipo(string $tipo):bool{
        return $this->tipo===$tipo;
    }
    /**
     * Verifica si el usuario está activo.
     *
     * @return bool
     */    
    public function estaActivo(): bool {
        return $this->estado === 'Activo';
    }




}