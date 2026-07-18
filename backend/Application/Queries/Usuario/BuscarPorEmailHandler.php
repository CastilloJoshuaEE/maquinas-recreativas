<?php

declare(strict_types=1);

namespace maquinas_recreativas\Application\Queries\Usuario;

use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Application\Queries\QueryHandler;
use maquinas_recreativas\Application\Queries\Query;

use maquinas_recreativas\Infrastructure\Security\CifradoHelper;

/**
 * Manejador para la query de buscar un usuario por email.
 *
 * @package maquinas_recreativas\Application\Query\Usuario
 * @version 1.0
 */
final class BuscarPorEmailHandler implements QueryHandler
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
     * Maneja la query de buscar por email.
     *
     * @param BuscarPorEmailQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(Query $query): array
    {
        // Validar formato del email
        if (!filter_var($query->email, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException(
                "El formato del email '{$query->email}' es inválido.",
                'USER_INVALID_EMAIL'
            );
        }

        // Encriptar email para búsqueda
        $emailEncriptado = CifradoHelper::encriptar($query->email);

        // Buscar usuario
        $usuario = $this->usuarioRepository->findByEmail($emailEncriptado);

        if (!$usuario) {
            throw new DomainException(
                "Usuario con email '{$query->email}' no encontrado.",
                'USER_NOT_FOUND_BY_EMAIL'
            );
        }

        return $usuario->toArray();
    }
}