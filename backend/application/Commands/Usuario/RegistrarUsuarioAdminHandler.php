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

    // Validar CI pero NO encriptar aquí (solo verificar existencia)
    $ciEncriptadaParaValidar = CifradoHelper::encriptar($command->ci);
    if ($this->usuarioRepository->existsByCi($ciEncriptadaParaValidar)) {
        throw new DomainException("La cédula '{$command->ci}' ya está registrada");
    }

    // Generar usuario_asignado si no se proporcionó
    $usuarioAsignado = $command->usuarioAsignado;
    if (empty($usuarioAsignado)) {
        $usuarioAsignado = $this->generarUsuarioAsignado($command->nombre, $command->apellido, $command->tipo);
    } else {
        if ($this->usuarioRepository->existsByUsuarioAsignado($usuarioAsignado)) {
            throw new DomainException("El nombre de usuario '{$usuarioAsignado}' ya está en uso");
        }
    }

    $id = Uuid::v4();
    $contrasenaHash = password_hash($command->contrasena, PASSWORD_BCRYPT);
    $estado = new EstadoUsuario($command->estado);
    $emailVO = new Email($command->email);

    // Pasar CI en texto plano (NO encriptada)
    $usuario = $this->crearUsuarioPorTipo(
        $id,
        $command->nombre,
        $command->apellido,
        $command->ci,  // ← Texto plano
        $emailVO,
        $usuarioAsignado,
        $contrasenaHash,
        $estado,
        $command->tipo,
        $command->especialidad
    );

    $this->usuarioRepository->save($usuario);  // Aquí se encriptará
    return $usuario;
}

private function generarUsuarioAsignado(string $nombre, string $apellido, string $tipo): string
{
    // Normalizar texto
    $nombre = strtolower(trim($nombre));
    $apellido = strtolower(trim($apellido));
    
    // Eliminar acentos
    $buscar = ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ'];
    $reemplazar = ['a', 'e', 'i', 'o', 'u', 'u', 'n'];
    $nombre = str_replace($buscar, $reemplazar, $nombre);
    $apellido = str_replace($buscar, $reemplazar, $apellido);
    
    // Prefijo según tipo
    $prefijos = [
        'Administrador' => 'adm',
        'Tecnico' => 'tec',
        'Logistica' => 'log',
        'Contabilidad' => 'con',
        'Usuario' => 'usr'
    ];
    $prefijo = $prefijos[$tipo] ?? 'usr';
    
    // Base del nombre
    $base = $prefijo . substr($nombre, 0, 1) . substr($apellido, 0, 3);
    
    // Buscar un nombre único
    $contador = 1;
    $usuario = $base;
    while ($this->usuarioRepository->existsByUsuarioAsignado($usuario)) {
        $usuario = $base . $contador;
        $contador++;
    }
    
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