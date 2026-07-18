<?php
/**
 * application/queries/reporte/ObtenerUsuariosChatHandler.php
 *
 * Manejador del query ObtenerUsuariosChat.
 *
 * @package maquinas_recreativas\Application\Queries\Reporte
 */

namespace maquinas_recreativas\Application\Queries\Reporte;

use maquinas_recreativas\Domain\Reporte\ReporteRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class ObtenerUsuariosChatHandler
 */
final class ObtenerUsuariosChatHandler
{
    private ReporteRepository $reporteRepository;
    private UsuarioRepository $usuarioRepository;

    public function __construct(
        ReporteRepository $reporteRepository,
        UsuarioRepository $usuarioRepository
    ) {
        $this->reporteRepository = $reporteRepository;
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * Maneja el query de obtener usuarios con los que ha chateado.
     *
     * @param ObtenerUsuariosChatQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(ObtenerUsuariosChatQuery $query): array
    {
        $userId = new Uuid($query->getUserId());

        $usuario = $this->usuarioRepository->findById($userId);
        if (!$usuario) {
            throw new DomainException('Usuario no encontrado.');
        }

        $usuarios = $this->reporteRepository->findUsuariosChat($userId);

        return array_map(function ($usuario) {
            return [
                'id' => $usuario['ID_Usuario'],
                'nombre' => $usuario['nombre'],
                'apellido' => $usuario['apellido'],
                'email' => $usuario['email'],
                'usuario_asignado' => $usuario['usuario_asignado'],
                'tipo' => $usuario['tipo']
            ];
        }, $usuarios);
    }
}