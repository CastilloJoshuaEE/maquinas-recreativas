<?php

declare(strict_types=1);

namespace RecreaSys\Application\Query\Usuario;

use RecreaSys\Domain\Usuario\UsuarioRepository;
use RecreaSys\Domain\Shared\ValueObjects\Uuid;
use RecreaSys\Domain\Shared\Exceptions\DomainException;
use RecreaSys\Application\Query\QueryHandler;

/**
 * Manejador para la query de obtener historial de actividades de un usuario.
 *
 * @package RecreaSys\Application\Query\Usuario
 * @version 1.0
 */
final class ObtenerHistorialActividadesHandler implements QueryHandler
{
    private UsuarioRepository $usuarioRepository;

    /**
     * Constructor del handler.
     *
     * @param UsuarioRepository $usuarioRepository
     */
    public function __construct(UsuarioRepository $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * Maneja la query de obtener historial de actividades.
     *
     * @param ObtenerHistorialActividadesQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(ObtenerHistorialActividadesQuery $query): array
    {
        // Verificar que el usuario existe
        $usuario = $this->usuarioRepository->findById($query->usuarioId);

        if (!$usuario) {
            throw new DomainException(
                "Usuario con ID '{$query->usuarioId->value()}' no encontrado.",
                'USER_NOT_FOUND'
            );
        }

        // Obtener historial de actividades del repositorio
        return $this->usuarioRepository->obtenerHistorialActividades($query->usuarioId);
    }
}