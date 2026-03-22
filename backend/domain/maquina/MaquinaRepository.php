<?php
/**
 * domain/maquina/MaquinaRepository.php
 *
 * Interfaz para el repositorio de máquinas recreativas.
 *
 * @package Reconocimiento\Domain\Maquina
 */

namespace Reconocimiento\Domain\Maquina;

use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use Reconocimiento\Domain\Usuario\Usuario;
use Reconocimiento\Domain\Comercio\Comercio;
use Reconocimiento\Domain\Componente\Componente;
use RuntimeException;

/**
 * Interface MaquinaRepository
 */
interface MaquinaRepository
{
    /**
     * Guarda una máquina en el repositorio.
     *
     * @param MaquinaRecreativa $maquina
     * @return void
     */
    public function save(MaquinaRecreativa $maquina): void;

    /**
     * Busca una máquina por su ID.
     *
     * @param Uuid $id
     * @return MaquinaRecreativa|null
     */
    public function findById(Uuid $id): ?MaquinaRecreativa;

    /**
     * Busca máquinas por técnico ensamblador.
     *
     * @param Uuid $idTecnico
     * @return array<MaquinaRecreativa>
     */
    public function findByTecnicoEnsamblador(Uuid $idTecnico): array;

    /**
     * Busca máquinas por técnico comprobador.
     *
     * @param Uuid $idTecnico
     * @return array<MaquinaRecreativa>
     */
    public function findByTecnicoComprobador(Uuid $idTecnico): array;

    /**
     * Busca máquinas por técnico de mantenimiento.
     *
     * @param Uuid $idTecnico
     * @return array<MaquinaRecreativa>
     */
    public function findByTecnicoMantenimiento(Uuid $idTecnico): array;

    /**
     * Busca máquinas por estado.
     *
     * @param EstadoMaquina $estado
     * @return array<MaquinaRecreativa>
     */
    public function findByEstado(EstadoMaquina $estado): array;

    /**
     * Busca máquinas por etapa.
     *
     * @param EtapaMaquina $etapa
     * @return array<MaquinaRecreativa>
     */
    public function findByEtapa(EtapaMaquina $etapa): array;

    /**
     * Busca máquinas listas para distribución (etapa distribución, estado distribuyéndose).
     *
     * @return array<MaquinaRecreativa>
     */
    public function findParaDistribucion(): array;

    /**
     * Busca máquinas operativas por comercio.
     *
     * @param Comercio $comercio
     * @return array<MaquinaRecreativa>
     */
    public function findOperativasPorComercio(Comercio $comercio): array;

    /**
     * Obtiene los componentes montados en una máquina.
     *
     * @param MaquinaRecreativa $maquina
     * @return array<Componente>
     */
    public function getComponentesMontaje(MaquinaRecreativa $maquina): array;
}