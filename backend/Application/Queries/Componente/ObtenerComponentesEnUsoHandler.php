<?php
/**
 * application/queries/componente/ObtenerComponentesEnUsoHandler.php
 *
 * Manejador del query ObtenerComponentesEnUso.
 *
 * @package maquinas_recreativas\Application\Queries\Componente
 */

namespace maquinas_recreativas\Application\Queries\Componente;

use maquinas_recreativas\Domain\Componente\ComponenteRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class ObtenerComponentesEnUsoHandler
 */
final class ObtenerComponentesEnUsoHandler
{
    private ComponenteRepository $componenteRepository;
    private UsuarioRepository $usuarioRepository;

    public function __construct(
        ComponenteRepository $componenteRepository,
        UsuarioRepository $usuarioRepository
    ) {
        $this->componenteRepository = $componenteRepository;
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * Maneja el query de obtener componentes en uso por un usuario.
     *
     * @param ObtenerComponentesEnUsoQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(ObtenerComponentesEnUsoQuery $query): array
    {
        $idUsuario = new Uuid($query->getIdUsuario());

        $usuario = $this->usuarioRepository->findById($idUsuario);
        if (!$usuario) {
            throw new DomainException('Usuario no encontrado.');
        }

        $idMaquina = $query->getIdMaquina() !== null ? new Uuid($query->getIdMaquina()) : null;

        $componentes = $this->componenteRepository->findEnUsoPorUsuario($idUsuario, $idMaquina);

        return array_map(function ($componente) {
            return [
                'id' => $componente->id()->value(),
                'tipo' => $componente->tipo()->value(),
                'nombre' => $componente->nombre(),
                'precio' => $componente->precio(),
                'fecha_asignacion' => $componente->fechaAsignacion()?->format('Y-m-d H:i:s'),
                'id_maquina' => $componente->maquinaAsignada()?->value()
            ];
        }, $componentes);
    }
}