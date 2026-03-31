<?php

declare(strict_types=1);

namespace maquinas_recreativas\Application\Commands\Usuario;

use maquinas_recreativas\Domain\Usuario\Usuario;
use maquinas_recreativas\Domain\Usuario\Tecnico;
use maquinas_recreativas\Domain\Usuario\Logistica;
use maquinas_recreativas\Domain\Usuario\TipoUsuario;
use maquinas_recreativas\Domain\Usuario\EstadoUsuario;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\ValueObjects\Email;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;
use InvalidArgumentException;

final class RegistrarUsuarioAdminHandler implements CommandHandler
{
    private UsuarioRepository $usuarioRepository;

    public function __construct(UsuarioRepository $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    public function handle(Command $command): Usuario
    {
        if (!$command instanceof RegistrarUsuarioAdminCommand) {
            throw new InvalidArgumentException('Comando inválido para este handler');
        }

        // Validar si el email ya existe
        $emailEncriptado = CifradoHelper::encriptar($command->email);
        if ($this->usuarioRepository->existsByEmail($emailEncriptado)) {
            throw new DomainException("El email '{$command->email}' ya está registrado.");
        }

        // Validar si el nombre de usuario ya existe
        if ($this->usuarioRepository->existsByUsuarioAsignado($command->usuarioAsignado)) {
            throw new DomainException("El nombre de usuario '{$command->usuarioAsignado}' ya está en uso");
        }

        // Validar si la cédula ya existe
        $ciEncriptada = CifradoHelper::encriptar($command->ci);
        if ($this->usuarioRepository->existsByCi($ciEncriptada)) {
            throw new DomainException("La cédula '{$command->ci}' ya está registrada");
        }

        // Usar Uuid::v4() en lugar de random()
        $id = Uuid::v4();
        $contrasenaHash = password_hash($command->contrasena, PASSWORD_BCRYPT);
        $estado = new EstadoUsuario($command->estado);
        $emailVO = new Email($command->email);

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
            $command->especialidad
        );

        $this->usuarioRepository->save($usuario);
        return $usuario;
    }

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
            'Tecnico' => new Tecnico(
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
            'Logistica' => new Logistica(
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