<?php
/**
 * maquinas_recreativas - Application Commands Handler
 *
 * Manejador del comando RegistrarUsuarioCommand.
 *
 * @package maquinas_recreativas\Application\Commands\Usuario
 * @author Tu Equipo
 * @version 1.0
 */

namespace maquinas_recreativas\Application\Commands\Usuario;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\ValueObjects\Email;
use maquinas_recreativas\Domain\Usuario\Usuario;
use maquinas_recreativas\Domain\Usuario\TipoUsuario;
use maquinas_recreativas\Domain\Usuario\EstadoUsuario;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Infrastructure\Security\PasswordHasher;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;
use InvalidArgumentException;

/**
 * Class RegistrarUsuarioHandler
 */
class RegistrarUsuarioHandler
{
    private UsuarioRepository $usuarioRepository;
    private PasswordHasher $passwordHasher;

    public function __construct(UsuarioRepository $usuarioRepository, PasswordHasher $passwordHasher)
    {
        $this->usuarioRepository = $usuarioRepository;
        $this->passwordHasher = $passwordHasher;
    }

    public function handle(RegistrarUsuarioCommand $command): Uuid
    {
        // 1. Validaciones de negocio
        $this->ensureEmailIsUnique($command->getEmail());
        $this->ensureUsuarioAsignadoIsUnique($this->generarUsuarioAsignado($command));

        // 2. Crear la entidad Usuario
        $nuevoId = Uuid::v4();  // Cambiar random() por v4()
        $hashContrasena = $this->passwordHasher->hash($command->getContrasenaPlana());
        
        // Encriptar datos sensibles
        $ciEncriptada = CifradoHelper::encriptar($command->getCi());
        $emailEncriptado = CifradoHelper::encriptar($command->getEmail());

        $usuario = new Usuario(
            $nuevoId,
            $command->getNombre(),
            $command->getApellido(),
            $ciEncriptada,
            new Email($command->getEmail()),  // Usar Email Value Object
            $this->generarUsuarioAsignado($command),
            $hashContrasena,
            new TipoUsuario($command->getTipo()),  // Convertir a TipoUsuario
            new EstadoUsuario('Activo'),  // Estado por defecto como Value Object
            $command->getEspecialidad()
        );

        // 3. Persistir la entidad
        $this->usuarioRepository->save($usuario);

        return $nuevoId;
    }

    private function ensureEmailIsUnique(string $email): void
    {
        $emailEncriptado = CifradoHelper::encriptar($email);
        if ($this->usuarioRepository->searchByEmail($emailEncriptado) !== null) {
            throw new InvalidArgumentException('El correo electrónico ya está registrado.');
        }
    }

    private function ensureUsuarioAsignadoIsUnique(string $usuarioAsignado): void
    {
        if ($this->usuarioRepository->searchByUsuarioAsignado($usuarioAsignado) !== null) {
            throw new InvalidArgumentException('El nombre de usuario ya está en uso.');
        }
    }

    private function generarUsuarioAsignado(RegistrarUsuarioCommand $command): string
    {
        $base = strtolower(substr($command->getNombre(), 0, 1) . substr($command->getApellido(), 0, 3));
        $usuario = $base;
        $contador = 1;
        while ($this->usuarioRepository->searchByUsuarioAsignado($usuario) !== null) {
            $usuario = $base . $contador;
            $contador++;
        }
        return $usuario;
    }
}