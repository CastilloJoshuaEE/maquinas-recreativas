<?php
namespace maquinas_recreativas\Application\Queries\Usuario;

use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;

final class ObtenerTodosUsuariosHandler
{
    private UsuarioRepository $usuarioRepository;

    public function __construct(UsuarioRepository $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    public function handle(ObtenerTodosUsuariosQuery $query): array
    {
        $filters = [];

        if ($query->getTipo()) {
            $filters['tipo'] = $query->getTipo();
        }
        if ($query->getEstado()) {
            $filters['estado'] = $query->getEstado();
        }
        if ($query->getCi()) {
            $filters['ci'] = CifradoHelper::encriptar($query->getCi());
        }
        
        // Agregar limit y offset a los filtros
        $filters['limit'] = $query->getLimit();
        $filters['offset'] = $query->getOffset();

        // findAll() ahora recibe solo un array de filtros
        $usuarios = $this->usuarioRepository->findAll($filters);

        return array_map(fn($usuario) => $usuario->toArray(), $usuarios);
    }
}