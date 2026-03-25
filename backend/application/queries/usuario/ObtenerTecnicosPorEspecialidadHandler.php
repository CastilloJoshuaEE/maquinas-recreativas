<?php
/**
 * application/queries/usuario/ObtenerTecnicosPorEspecialidadHandler.php
 *
 * Manejador del query ObtenerTecnicosPorEspecialidad.
 *
 * @package maquinas_recreativas\Application\Queries\Usuario
 */

namespace maquinas_recreativas\Application\Queries\Usuario;

use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class ObtenerTecnicosPorEspecialidadHandler
 */
final class ObtenerTecnicosPorEspecialidadHandler
{
    private UsuarioRepository $usuarioRepository;

    public function __construct(UsuarioRepository $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * Maneja el query de obtener técnicos por especialidad.
     *
     * @param ObtenerTecnicosPorEspecialidadQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(ObtenerTecnicosPorEspecialidadQuery $query): array
    {
        $especialidadesValidas = ['Ensamblador', 'Comprobador', 'Mantenimiento'];

        if (!in_array($query->getEspecialidad(), $especialidadesValidas, true)) {
            throw new DomainException('Especialidad no válida.');
        }

        $tecnicos = $this->usuarioRepository->findTecnicosByEspecialidad($query->getEspecialidad());

        return array_map(function ($tecnico) {
            return [
                'id' => $tecnico->getId()->value(),
                'nombre' => $tecnico->getNombre(),
                'apellido' => $tecnico->getApellido(),
                'email' => $tecnico->getEmail(),
                'usuario_asignado' => $tecnico->getUsuarioAsignado(),
                'tipo' => $tecnico->getTipo()->value(),
                'estado' => $tecnico->getEstado()->value(),
                'especialidad' => $tecnico->getEspecialidad(),
                'cantidad_actividades' => $tecnico->getCantidadActividades()
            ];
        }, $tecnicos);
    }
}