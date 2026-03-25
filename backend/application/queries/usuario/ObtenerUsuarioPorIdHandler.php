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

/**
 * @package Application\Queries\Usuario
 * 
 * Manejador responsable de obtener un usuario por su ID
 */
class ObtenerUsuarioPorIdHandler {
    
    /**
     * @var UsuarioRepository Repositorio de usuarios
     */
    private $usuarioRepository;
    
    /**
     * Constructor del manejador
     * 
     * @param UsuarioRepository $usuarioRepository
     */
    public function __construct(UsuarioRepository $usuarioRepository) {
        $this->usuarioRepository = $usuarioRepository;
    }
    
    /**
     * Maneja el query de obtener usuario por ID
     * 
     * @param ObtenerUsuarioPorIdQuery $query Query con ID del usuario
     * @return array Datos del usuario encontrado
     * @throws DomainException Si el usuario no existe
     */
    public function handle(ObtenerUsuarioPorIdQuery $query): array {
        
        $usuarioId = $query->getUsuarioId()->getValue();
        
        // Buscar usuario
        $usuario = $this->usuarioRepository->findById($usuarioId);
        
        if (!$usuario) {
            throw new DomainException(
                'Usuario no encontrado',
                DomainException::HTTP_NOT_FOUND
            );
        }
        
        // Construir respuesta según permisos
        $data = [
            'id' => $usuario->getId(),
            'nombre' => $usuario->getNombre(),
            'apellido' => $usuario->getApellido(),
            'usuario_asignado' => $usuario->getUsuarioAsignado(),
            'tipo' => $usuario->getTipo(),
            'estado' => $usuario->getEstado(),
            'fecha_registro' => $usuario->getFechaRegistro()
        ];
        
        // Incluir email solo si está permitido
        if ($query->shouldIncludeSensitive()) {
            $data['email'] = $usuario->getEmail();
            $data['ci'] = $usuario->getCi();
        }
        
        // Incluir especialidad si es técnico
        if ($usuario->esTecnico()) {
            $data['especialidad'] = $usuario->getEspecialidad();
        }
        
        return $data;
    }
}