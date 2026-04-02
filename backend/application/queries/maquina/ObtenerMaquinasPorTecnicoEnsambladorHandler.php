<?php
/**
 * application/queries/maquina/ObtenerMaquinasPorTecnicoEnsambladorHandler.php
 */

namespace maquinas_recreativas\Application\Queries\Maquina;

use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

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

    public function handle(ObtenerMaquinasPorTecnicoEnsambladorQuery $query): array
    {
        try {
            $idTecnico = new Uuid($query->getIdTecnico());

            $tecnico = $this->usuarioRepository->findById($idTecnico);
            if (!$tecnico || !$tecnico->esTecnico()) {
                throw new DomainException('Técnico no encontrado o no válido.');
            }

            $maquinas = $this->maquinaRepository->findByTecnicoEnsamblador($idTecnico);

            $resultado = [];
            foreach ($maquinas as $maquina) {
                $data = $maquina->toArray();
                $resultado[] = [
                    'id' => $data['ID_Maquina'],
                    'nombre' => $data['Nombre_Maquina'],
                    'tipo' => $data['Tipo'],
                    'estado' => $data['Estado'],
                    'etapa' => $data['Etapa'],
                    'fecha_registro' => $data['Fecha_Registro'],
                    'id_comercio' => $data['ID_Comercio']
                ];
            }

            return ['success' => true, 'maquinas' => $resultado];
        } catch (\Exception $e) {
            error_log("Error en ObtenerMaquinasPorTecnicoEnsambladorHandler: " . $e->getMessage());
            return ['success' => false, 'maquinas' => [], 'error' => $e->getMessage()];
        }
    }
}