<?php
/**
 * application/queries/maquina/ObtenerMaquinasPorTecnicoComprobadorHandler.php
 *
 * Manejador del query ObtenerMaquinasPorTecnicoComprobador.
 *
 * @package maquinas_recreativas\Application\Queries\Maquina
 */

namespace maquinas_recreativas\Application\Queries\Maquina;

use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class ObtenerMaquinasPorTecnicoComprobadorHandler
 */
final class ObtenerMaquinasPorTecnicoComprobadorHandler
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
     * Maneja el query de obtener máquinas por técnico comprobador.
     *
     * @param ObtenerMaquinasPorTecnicoComprobadorQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(ObtenerMaquinasPorTecnicoComprobadorQuery $query): array
    {
        $idTecnico = new Uuid($query->getIdTecnico());

        $tecnico = $this->usuarioRepository->findById($idTecnico);
        if (!$tecnico || !$tecnico->esTecnico()) {
            throw new DomainException('Técnico no encontrado o no válido.');
        }

        $maquinas = $this->maquinaRepository->findByTecnicoComprobador($idTecnico);

    return array_map(function ($maquina) {
        $data = $maquina->toArray();
        return [
            'id' => $data['ID_Maquina'],
            'nombre' => $data['Nombre_Maquina'],
            'tipo' => $data['Tipo'],
            'estado' => $data['Estado'],
            'etapa' => $data['Etapa'],
            'fecha_registro' => $data['Fecha_Registro'],
            'id_comercio' => $data['ID_Comercio']
        ];
    }, $maquinas);
    }
}