<?php

declare(strict_types=1);

namespace maquinas_recreativas\Application\Command\Usuario;

use maquinas_recreativas\Domain\Usuario\Usuario;
use maquinas_recreativas\Domain\Usuario\Tecnico;
use maquinas_recreativas\Domain\Usuario\Logistica;
use maquinas_recreativas\Domain\Usuario\TipoUsuario;
use maquinas_recreativas\Domain\Usuario\EstadoUsuario;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\ValueObjects\Email;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Application\Command\CommandHandler;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;
use InvalidArgumentException;

/**
 * Manejador para el comando de registro de usuario por un administrador.
 *
 * @package maquinas_recreativas\Application\Command\Usuario
 * @version 1.0
 */
final class RegistrarUsuarioAdminHandler implements CommandHandler
{
    private UsuarioRepository $usuarioRepository;

    /**
     * Constructor del handler.
     *
     * @param UsuarioRepository $usuarioRepository
     */
    public function __construct(UsuarioRepository $usuarioRepository){
        $this->usuarioRepository = $usuarioRepository;

    }    
    /**
     * Maneja el comando de registro de usuario administrador.
     *
     * @param RegistrarUsuarioAdminCommand $command
     * @return Usuario
     * @throws DomainException|InvalidArgumentException
     */
    public function handle(RegistrarUsuarioAdminCommand $command): Usuario{
        // Validar si el email ya existe
        if($this->usuarioRepository->existsByEamil($command->email)){
            throw new DomainException("El email '{$command->email}' ya está registrado.",
            'USER_EMAIL_EXISTS');

        }
        // Validar si el nombre de usuario ya existe
        if($this->usuarioRepository->existsByUsuarioAsignado($command->usuarioAsignado)){
            throw new DomainException("El nombre de usuario '{$command->usuarioAsignado}' ya está en uso", 'USER_USERNAME_EXISTS');
        }
        // Validar si la cédula ya existe
        if($this->usuarioRepository->existsByCi($command->ci)){
            throw new DomainException("La cédula '{$command->ci}' ya está registrada", 'USER_CI_EXISTS');

        }
        // Crear el Value Object Email
        $emailVO = new Email ($command->email);
        $id = Uuid::generate();
        // Encriptar datos sensibles
        $ciEncriptada = CifradoHelper::encriptar($command->ci);
        $emailEncriptado = CifradoHelper::encriptar($command->email);
        $contrasenaHash = password_hash($command->contrasena, PASSWORD_BCRYPT);
        $estado = new EstadoUsuario($command->estado);
        // Crear la entidad según el tipo
        $usuario = $this->crearUsuarioPorTipo(
            $id,
            $command->nombre,
            $command->apellido,
            $ciEncriptada,
            $emailVO,
            $command->usuarioAsignado,
            $contrasenaHash,
            $estado,
            $command->tipo,
            $command->especialidad ?? null
        );
        // Guardar el usuario a través del repositorio
        $this->usuarioRepository->save($usuario);
        return $usuario;
    }    
    /**
     * Crea la instancia de usuario específica según el tipo.
     *
     * @param Uuid $id
     * @param string $nombre
     * @param string $apellido
     * @param string $ciEncriptada
     * @param Email $email
     * @param string $usuarioAsignado
     * @param string $contrasenaHash
     * @param EstadoUsuario $estado
     * @param string $tipo
     * @param string|null $especialidad
     * @return Usuario|Tecnico|Logistica
     * @throws InvalidArgumentException
     */
    private function crearUsuarioPorTipo(
        Uuid $id,
        string $nombre,
        string $apellido,
        string $ciEncriptada,
        Email $email,
        string $usuarioAsignado,
        string $contrasenaHash,
        EstadoUsuario $estado,
        string $tipo,
        ?string $especialidad
    ): Usuario {
        return match ($tipo) {
            TipoUsuario::TECNICO => new Tecnico(
                $id,
                $nombre,
                $apellido,
                $ciEncriptada,
                $email,
                $usuarioAsignado,
                $contrasenaHash,
                $estado,
                $especialidad ?? ''
            ),
            TipoUsuario::LOGISTICA => new Logistica(
                $id,
                $nombre,
                $apellido,
                $ciEncriptada,
                $email,
                $usuarioAsignado,
                $contrasenaHash,
                $estado
            ),
            default => new Usuario(
                $id,
                $nombre,
                $apellido,
                $ciEncriptada,
                $email,
                $usuarioAsignado,
                $contrasenaHash,
                new TipoUsuario($tipo),
                $estado
            ),
        };
    }        
}