<?php
namespace maquinas_recreativas\Application\Queries\Maquina;

use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Comercio\ComercioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class ObtenerMaquinasPorTecnicoEnsambladorHandler
{
    private MaquinaRepository $maquinaRepository;
    private UsuarioRepository $usuarioRepository;
    private ComercioRepository $comercioRepository;

    public function __construct(
        MaquinaRepository $maquinaRepository,
        UsuarioRepository $usuarioRepository,
        ComercioRepository $comercioRepository
    ) {
        $this->maquinaRepository = $maquinaRepository;
        $this->usuarioRepository = $usuarioRepository;
        $this->comercioRepository = $comercioRepository;
    }
public function handle(ObtenerMaquinasPorTecnicoEnsambladorQuery $query): array
{
    $idTecnico = new Uuid($query->getIdTecnico());
    
    $tecnico = $this->usuarioRepository->findById($idTecnico);
    if (!$tecnico) {
        throw new DomainException('Técnico no encontrado');
    }

    $maquinasData = $this->maquinaRepository->findByTecnicoEnsambladorWithComercio($idTecnico);
    
    return ['success' => true, 'maquinas' => $maquinasData];
}
}