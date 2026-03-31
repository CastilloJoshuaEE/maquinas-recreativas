<?php
/**
 * Manejador del query para obtener usuario por ID
 * 
 * @package Application\Queries\Usuario
 * @author Tu Nombre
 * @version 1.0.0
 */

namespace maquinas_recreativas\Application\Queries\Usuario;

use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;

class ObtenerUsuarioPorIdHandler 
{    
    private UsuarioRepository $usuarioRepository;
    
    public function __construct(UsuarioRepository $usuarioRepository) 
    {
        $this->usuarioRepository = $usuarioRepository;
    }
    
    public function handle(ObtenerUsuarioPorIdQuery $query): array 
    {
        $usuarioId = $query->getUsuarioId();
        
        // Buscar usuario - usar findById directamente
        $usuario = $this->usuarioRepository->findById($usuarioId);
        
        if (!$usuario) {
            throw new DomainException('Usuario no encontrado');
        }
        
        // Construir respuesta
        $data = [
            'id' => $usuario->getId()->value(),
            'nombre' => $usuario->getNombre(),
            'apellido' => $usuario->getApellido(),
            'usuario_asignado' => $usuario->getUsuarioAsignado(),
            'tipo' => $usuario->getTipo()->value(),
            'estado' => $usuario->getEstado()->value(),
            'especialidad' => $usuario->getEspecialidad()
        ];
        
        // Incluir email solo si está permitido
        if ($query->shouldIncludeSensitive()) {
            $data['email'] = CifradoHelper::desencriptar($usuario->getEmailValue());
            $data['ci'] = CifradoHelper::desencriptar($usuario->getCi());
        }
        
        return $data;
    }
}