<?php
/**
 * application/queries/maquina/ObtenerMaquinasPorTecnicoEnsambladorHandler.php
 *
 * Manejador del query ObtenerMaquinasPorTecnicoEnsamblador.
 *
 * @package maquinas_recreativas\Application\Queries\Maquina
 */

namespace maquinas_recreativas\Application\Queries\Maquina;

use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class ObtenerMaquinasPorTecnicoEnsambladorHandler
 */
final class ObtenerMaquinasPorTecnicoEnsambladorHandler
{
    private MaquinaRepository $maquinaRepository;
    private UsuarioRepository $usuarioRepository;

    public function __construct(
        MaquinaRepository $maquinaRepository,
        UsuarioRepository $usuarioRepository
    ) {
        $this->maquinaRepository = $maquinaRepository;
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * Maneja el query de obtener máquinas por técnico ensamblador.
     *
     * @param ObtenerMaquinasPorTecnicoEnsambladorQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(ObtenerMaquinasPorTecnicoEnsambladorQuery $query): array
    {
        $idTecnico = new Uuid($query->getIdTecnico());

        $tecnico = $this->usuarioRepository->findById($idTecnico);
        if (!$tecnico || !$tecnico->esTecnico()) {
            throw new DomainException('Técnico no encontrado o no válido.');
        }

        $maquinas = $this->maquinaRepository->findByTecnicoEnsamblador($idTecnico);

        return array_map(function ($maquina) {
            return [
                'id' => $maquina->id()->value(),
                'nombre' => $maquina->nombre(),
                'tipo' => $maquina->tipo(),
                'estado' => $maquina->estado()->value(),
                'etapa' => $maquina->etapa()->value(),
                'fecha_registro' => $maquina->fechaRegistro()->format('Y-m-d H:i:s'),
                'id_comercio' => $maquina->idComercio()->value()
            ];
        }, $maquinas);
    }
}