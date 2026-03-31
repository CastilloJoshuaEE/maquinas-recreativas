<?php
/**
 * RecreSys - Domain Usuario Entity
 * Define la entidad Usuario con sus reglas de negocio
 * @package maquinas_recreativas\Domain\Usuario
 * @author Tu Equipo
 * @version 1.0.0
 */
namespace maquinas_recreativas\Domain\Usuario;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\ValueObjects\Email;
use InvalidArgumentException;

class Usuario
{
    private Uuid $id;
    private string $nombre;
    private string $apellido;
    private Email $email;
    private string $ci;
    private string $usuarioAsignado;
    private string $contrasenaHash;
    private TipoUsuario $tipo;
    private EstadoUsuario $estado;
    private ?string $especialidad;

    public const TIPOS_PERMITIDOS = ['Tecnico', 'Logistica', 'Contabilidad', 'Administrador', 'Usuario'];
    public const ESTADOS_PERMITIDOS = ['Activo', 'Inactivo', 'Suspendido', 'Pendiente_asignacion'];
    public const ESPECIALIDADES_TECNICO = ['Ensamblador', 'Comprobador', 'Mantenimiento'];

    public function __construct(
        Uuid $id,
        string $nombre,
        string $apellido,
        string $ci,
        Email $email,
        string $usuarioAsignado,
        string $contrasenaHash,
        TipoUsuario $tipo,
        EstadoUsuario $estado,
        ?string $especialidad = null
    ) {
        $this->setId($id);
        $this->setNombre($nombre);
        $this->setApellido($apellido);
        $this->setCi($ci);
        $this->setEmail($email);
        $this->setUsuarioAsignado($usuarioAsignado);
        $this->setContrasenaHash($contrasenaHash);
        $this->setTipo($tipo);
        $this->setEstado($estado);
        $this->especialidad = $especialidad;
    }

    /* --- Getters --- */
    public function getId(): Uuid { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getApellido(): string { return $this->apellido; }
    public function getEmail(): Email { return $this->email; }
    public function getEmailValue(): string { return $this->email->value(); }
    public function getCi(): string { return $this->ci; }
    public function getUsuarioAsignado(): string { return $this->usuarioAsignado; }
    public function getContrasenaHash(): string { return $this->contrasenaHash; }
    public function getTipo(): TipoUsuario { return $this->tipo; }
    public function getEstado(): EstadoUsuario { return $this->estado; }
    public function getEspecialidad(): ?string { return $this->especialidad; }

    /* --- Setters privados con validación --- */
    private function setId(Uuid $id): void { $this->id = $id; }

    private function setNombre(string $nombre): void {
        $nombre = trim($nombre);
        if (empty($nombre)) {
            throw new InvalidArgumentException('El nombre no puede estar vacío');
        }
        $this->nombre = $nombre;
    }

    private function setApellido(string $apellido): void {
        $apellido = trim($apellido);
        if (empty($apellido)) {
            throw new InvalidArgumentException('El apellido no puede estar vacío');
        }
        $this->apellido = $apellido;
    }

    private function setEmail(Email $email): void {
        $this->email = $email;
    }

    private function setCi(string $ci): void {
        $ci = trim($ci);
        if (strlen($ci) < 6) {
            throw new InvalidArgumentException('La cédula debe tener al menos 6 caracteres');
        }
        $this->ci = $ci;
    }

    private function setUsuarioAsignado(string $usuarioAsignado): void {
        $usuarioAsignado = trim($usuarioAsignado);
        if (strlen($usuarioAsignado) < 3) {
            throw new InvalidArgumentException('El nombre de usuario debe tener al menos 3 caracteres');
        }
        $this->usuarioAsignado = $usuarioAsignado;
    }

    private function setContrasenaHash(string $contrasenaHash): void {
        if (empty($contrasenaHash)) {
            throw new InvalidArgumentException('El hash de la contraseña no puede estar vacío');
        }
        $this->contrasenaHash = $contrasenaHash;
    }

    private function setTipo(TipoUsuario $tipo): void {
        $this->tipo = $tipo;
    }

    private function setEstado(EstadoUsuario $estado): void {
        $this->estado = $estado;
    }

    /* --- Métodos de negocio --- */

    /**
     * Actualiza los datos del usuario (para administradores)
     */
    public function actualizar(
        string $nombre,
        string $apellido,
        Email $email,
        string $ci,
        TipoUsuario $tipo,
        EstadoUsuario $estado,
        string $usuarioAsignado,
        ?string $especialidad = null,
        ?string $nuevaContrasenaHash = null
    ): void {
        $this->setNombre($nombre);
        $this->setApellido($apellido);
        $this->setEmail($email);
        $this->setCi($ci);
        $this->setTipo($tipo);
        $this->setEstado($estado);
        $this->setUsuarioAsignado($usuarioAsignado);
        
        if ($tipo->isTecnico()) {
            $this->setEspecialidad($especialidad);
        } else {
            $this->especialidad = null;
        }
        
        if ($nuevaContrasenaHash !== null) {
            $this->setContrasenaHash($nuevaContrasenaHash);
        }
    }

    /**
     * Actualiza el perfil del usuario (para el propio usuario)
     */
    public function actualizarPerfil(
        string $nombre,
        string $apellido,
        Email $email,
        string $ci,
        ?string $nuevaContrasenaHash = null
    ): void {
        $this->setNombre($nombre);
        $this->setApellido($apellido);
        $this->setEmail($email);
        $this->setCi($ci);
        
        if ($nuevaContrasenaHash !== null) {
            $this->setContrasenaHash($nuevaContrasenaHash);
        }
    }

    /**
     * Cambia el estado del usuario
     */
    public function cambiarEstado(EstadoUsuario $nuevoEstado): void {
        $this->setEstado($nuevoEstado);
    }

    /**
     * Cambia el nombre de usuario asignado
     */
    public function actualizarUsuarioAsignado(string $nuevoUsuarioAsignado): void {
        $this->setUsuarioAsignado($nuevoUsuarioAsignado);
    }

    /**
     * Cambia la contraseña
     */
    public function cambiarContrasena(string $nuevaContrasenaHash): void {
        $this->setContrasenaHash($nuevaContrasenaHash);
    }

    /**
     * Activa al usuario
     */
    public function activar(): void {
        $this->estado = new EstadoUsuario('Activo');
    }

    /**
     * Desactiva al usuario
     */
    public function desactivar(): void {
        $this->estado = new EstadoUsuario('Inactivo');
    }

    /**
     * Verifica si el usuario es técnico
     */
    public function esTecnico(): bool {
        return $this->tipo->isTecnico();
    }

    /**
     * Verifica si el usuario está activo
     */
    public function estaActivo(): bool {
        return $this->estado->isActivo();
    }

    private function setEspecialidad(?string $especialidad): void {
        if ($especialidad !== null && !in_array($especialidad, self::ESPECIALIDADES_TECNICO, true)) {
            throw new InvalidArgumentException(sprintf('La especialidad "%s" no es válida', $especialidad));
        }
        $this->especialidad = $especialidad;
    }

    /**
     * Convierte la entidad a array para persistencia o respuesta
     */
    public function toArray(): array {
        return [
            'id' => $this->id->value(),
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'ci' => $this->ci,
            'email' => $this->email->value(),
            'usuario_asignado' => $this->usuarioAsignado,
            'tipo' => $this->tipo->value(),
            'estado' => $this->estado->value(),
            'especialidad' => $this->especialidad,
        ];
    }
}