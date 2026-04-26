<?php
/**
 * domain/maquina/MaquinaRepository.php
 *
 * Interfaz para el repositorio de máquinas recreativas.
 *
 * @package maquinas_recreativas\Domain\Maquina
 */

namespace maquinas_recreativas\Domain\Maquina;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Usuario\Usuario;
use maquinas_recreativas\Domain\Comercio\Comercio;
use maquinas_recreativas\Domain\Componente\Componente;
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
    public function findByTecnicoEnsambladorWithComercio(Uuid $idTecnico): array; // Nuevo método
    public function findByTecnicoComprobadorWithComercio(Uuid $idTecnico): array;
    public function findByEtapaWithComercio(EtapaMaquina $etapa): array;
    public function findByTecnicoMantenimientoWithComercio(Uuid $idTecnico): array;
    public function findAllWithComercio(): array;
public function getComponentesEnUsoPorMaquina(Uuid $idMaquina): array;

}