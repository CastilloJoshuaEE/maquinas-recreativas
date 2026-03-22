<?php

declare(strict_types=1);

namespace RecreaSys\Application\Query\Usuario;

use RecreaSys\Domain\Usuario\UsuarioRepository;
use RecreaSys\Domain\Usuario\TipoUsuario;
use RecreaSys\Application\Query\QueryHandler;
use InvalidArgumentException;

/**
 * Manejador para la query de obtener usuarios por tipo.
 *
 * @package RecreaSys\Application\Query\Usuario
 * @version 1.0
 */
final class ObtenerUsuariosPorTipoHandler implements QueryHandler
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
     * Maneja la query de obtener usuarios por tipo.
     *
     * @param ObtenerUsuariosPorTipoQuery $query
     * @return array
     * @throws InvalidArgumentException
     */
    public function handle(ObtenerUsuariosPorTipoQuery $query):array{
        // Validar que el tipo sea válido
        $tiposValidos = TipoUsuario::validValues();
        if(!in_array($query->tipo, $tiposValidos, true)){
            throw new InvalidArgumentException("Tipo de usuario '{$query->tipo}' no válido. Valores permitidos:"
                .implode(',', $tiposValidos)
            );

        }
        // Obtener usuarios del repositorio
        $usuarios = $this->usuarioRepository->findByTipo($query->tipo, $query->excluirId);

        // Convertir entidades a arrays
        return array_map(fn($usuario) => $usuario->toArray(), $usuarios);
            
    }    

}