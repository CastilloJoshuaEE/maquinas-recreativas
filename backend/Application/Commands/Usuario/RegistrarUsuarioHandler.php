<?php
namespace maquinas_recreativas\Application\Commands\Usuario;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\ValueObjects\Email;
use maquinas_recreativas\Domain\Usuario\Usuario;
use maquinas_recreativas\Domain\Usuario\Tecnico;
use maquinas_recreativas\Domain\Usuario\Logistica;
use maquinas_recreativas\Domain\Usuario\TipoUsuario;
use maquinas_recreativas\Domain\Usuario\EstadoUsuario;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Infrastructure\Security\PasswordHasher;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;
use InvalidArgumentException;

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
    // Validar email
    $emailEncriptado = CifradoHelper::encriptar($command->getEmail());
    if ($this->usuarioRepository->searchByEmail($emailEncriptado) !== null) {
        throw new InvalidArgumentException('El correo electrónico ya está registrado.');
    }

    $ciEncriptadaParaValidar = CifradoHelper::encriptar($command->getCi());
    if ($this->usuarioRepository->existsByCi($ciEncriptadaParaValidar)) {
        throw new InvalidArgumentException('La cédula ya está registrada.');
    }

    $usuarioAsignado = $this->generarUsuarioAsignado($command);
    $this->ensureUsuarioAsignadoIsUnique($usuarioAsignado);
    
    // Generar contraseña si no viene del frontend o es null
    $contrasenaPlana = $command->getContrasenaPlana();
    if (empty($contrasenaPlana)) {
        $contrasenaPlana = $this->generarContrasenaTemporal();
    }
    $hashContrasena = $this->passwordHasher->hash($contrasenaPlana);

    $nuevoId = Uuid::v4();
    $email = new Email($command->getEmail());
    $estado = new EstadoUsuario('Pendiente de asignacion');
    $tipo = $command->getTipo();
    
    // Si no viene tipo o es vacío, usar 'Usuario'
    if (empty($tipo)) {
        $tipo = 'Usuario';
    }

    error_log("Registrando usuario: Tipo=" . $tipo . ", Email=" . $command->getEmail());

    if ($tipo === TipoUsuario::TECNICO) {
        $usuario = new Tecnico(
            $nuevoId, $command->getNombre(), $command->getApellido(), $command->getCi(),
            $email, $usuarioAsignado, $hashContrasena, $estado,
            $command->getEspecialidad() ?? '', 0
        );
    } elseif ($tipo === TipoUsuario::LOGISTICA) {
        $usuario = new Logistica(
            $nuevoId, $command->getNombre(), $command->getApellido(), $command->getCi(),
            $email, $usuarioAsignado, $hashContrasena, $estado
        );
    } else {
        $usuario = new Usuario(
            $nuevoId, $command->getNombre(), $command->getApellido(), $command->getCi(),
            $email, $usuarioAsignado, $hashContrasena, new TipoUsuario($tipo), $estado,
            $command->getEspecialidad()
        );
    }

    $this->usuarioRepository->save($usuario);
    error_log("Usuario registrado exitosamente: ID=" . $nuevoId->value());
    
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
        $base     = strtolower(substr($command->getNombre(), 0, 1) . substr($command->getApellido(), 0, 3));
        $usuario  = $base;
        $contador = 1;
        while ($this->usuarioRepository->searchByUsuarioAsignado($usuario) !== null) {
            $usuario = $base . $contador;
            $contador++;
        }
        return $usuario;
    }
    
    private function generarContrasenaTemporal(): string
    {
        $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%';
        return substr(str_shuffle($caracteres), 0, 12);
    }
}