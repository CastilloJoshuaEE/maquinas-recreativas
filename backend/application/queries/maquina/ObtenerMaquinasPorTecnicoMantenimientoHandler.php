<?php
/**
 * application/queries/maquina/ObtenerMaquinasPorTecnicoMantenimientoHandler.php
 */

namespace maquinas_recreativas\Application\Queries\Maquina;

use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class ObtenerMaquinasPorTecnicoMantenimientoHandler
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

    public function handle(ObtenerMaquinasPorTecnicoMantenimientoQuery $query): array
    {
        $idTecnico = new Uuid($query->getIdTecnico());

        $tecnico = $this->usuarioRepository->findById($idTecnico);
        if (!$tecnico || !$tecnico->esTecnico()) {
            throw new DomainException('Técnico no encontrado o no válido.');
        }

        // Usar el método que incluye datos del comercio
        $maquinasData = $this->maquinaRepository->findByTecnicoMantenimientoWithComercio($idTecnico);
        
        // Asegurar que $maquinasData sea un array
        if (!is_array($maquinasData)) {
            $maquinasData = [];
        }
        
        return ['success' => true, 'maquinas' => $maquinasData];
    }
}