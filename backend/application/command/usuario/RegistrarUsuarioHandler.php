<?php
/**
 * RecreaSys - Application Command Handler
 *
 * Manejador del comando RegistrarUsuarioCommand.
 *
 * @package RecreaSys\Application\Command\Usuario
 * @author Tu Equipo
 * @version 1.0
 */

namespace RecreaSys\Application\Command\Usuario;

use RecreaSys\Domain\Shared\ValueObjects\Uuid;
use RecreaSys\Domain\Usuario\Usuario;
use RecreaSys\Domain\Usuario\UsuarioRepository;
use RecreaSys\Infrastructure\Security\PasswordHasher;
use InvalidArgumentException;

/**
 * Class RegistrarUsuarioHandler
 *
 * Contiene la lógica de aplicación para el caso de uso "Registrar Usuario".
 * Orquesta las entidades del dominio y los servicios de infraestructura.
 */
class RegistrarUsuarioHandler
{
    private UsuarioRepository $usuarioRepository;
    private PasswordHasher $passwordHasher;

    /**
     * RegistrarUsuarioHandler constructor.
     *
     * @param UsuarioRepository $usuarioRepository
     * @param PasswordHasher $passwordHasher
     */
    public function __construct(UsuarioRepository $usuarioRepository, PasswordHasher $passwordHasher)
    {
        $this->usuarioRepository = $usuarioRepository;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Maneja el comando de registro de usuario.
     *
     * @param RegistrarUsuarioCommand $command
     * @return Uuid El ID del nuevo usuario registrado.
     * @throws InvalidArgumentException Si los datos no son válidos o el usuario ya existe.
     */
    public function handle(RegistrarUsuarioCommand $command): Uuid
    {
        // 1. Validaciones de negocio que requieren acceso a datos (unicidad)
        $this->ensureEmailIsUnique($command->getEmail());
        $this->ensureUsuarioAsignadoIsUnique($this->generarUsuarioAsignado($command));

        // 2. Crear la entidad Usuario (aplicando reglas de negocio)
        $nuevoId = Uuid::random();
        $hashContrasena = $this->passwordHasher->hash($command->getContrasenaPlana());

        $usuario = new Usuario(
            $nuevoId,
            $command->getNombre(),
            $command->getApellido(),
            $command->getEmail(),
            $command->getCi(),
            $this->generarUsuarioAsignado($command),
            $hashContrasena,
            $command->getTipo(),
            'Activo', // Estado por defecto
            $command->getEspecialidad()
        );

        // 3. Persistir la entidad
        $this->usuarioRepository->save($usuario);

        return $nuevoId;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function ensureEmailIsUnique(string $email): void
    {
        if ($this->usuarioRepository->searchByEmail($email) !== null) {
            throw new InvalidArgumentException('El correo electrónico ya está registrado.');
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function ensureUsuarioAsignadoIsUnique(string $usuarioAsignado): void
    {
        if ($this->usuarioRepository->searchByUsuarioAsignado($usuarioAsignado) !== null) {
            throw new InvalidArgumentException('El nombre de usuario ya está en uso.');
        }
    }

    /**
     * Lógica simple para generar un nombre de usuario (puede ser más compleja).
     * @param RegistrarUsuarioCommand $command
     * @return string
     */
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