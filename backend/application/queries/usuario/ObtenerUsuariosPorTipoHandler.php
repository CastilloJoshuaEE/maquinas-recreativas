<?php

declare(strict_types=1);

namespace maquinas_recreativas\Application\Queries\Usuario;

use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Usuario\TipoUsuario;
use maquinas_recreativas\Application\Queries\QueryHandler;
use InvalidArgumentException;
use maquinas_recreativas\Application\Queries\Query;


/**
 * Manejador para la query de obtener usuarios por tipo.
 *
 * @package maquinas_recreativas\Application\Query\Usuario
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
    public function handle(Query $query):array{
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