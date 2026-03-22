<?php
/**
 * Archivo: ComercioRepository.php
 * 
 * Puerto (Interfaz) para el repositorio de Comercio.
 * Define las operaciones que debe implementar cualquier
 * infraestructura de persistencia para la entidad Comercio.
 */

namespace Domain\Comercio;

use Domain\Shared\Exceptions\DomainException;

/**
 * Interface ComercioRepository
 * 
 * Contrato para el repositorio de comercios.
 */
interface ComercioRepository {
    /**
     * Guarda un comercio (crea o actualiza).
     *
     * @param Comercio $comercio
     * @return void
     * @throws DomainException Si hay un error de persistencia.
     */
    public function guardar(Comercio $comercio):void;
    /**
     * Busca un comercio por su ID.
     *
     * @param string $id
     * @return Comercio|null
     */
    public function buscarPorId(string $id):?Comercio;
    /**
     * Busca un comercio por su nombre exacto.
     *
     * @param string $nombre
     * @return Comercio|null
     */
    public function buscarPorNombre(string $nombre):?Comercio;
    /**
     * Obtiene todos los comercios.
     *
     * @param array $criterios Filtros opcionales (ej. ['tipo' => 'Minorista']).
     * @return Comercio[]
     */
    public function obtenerTodos(array $criterios=[]):array;
    /**
     * Elimina un comercio por su ID.
     *
     * @param string $id
     * @return void
     * @throws DomainException Si el comercio no existe o tiene dependencias.
     */
    public function eliminar(string $id):void;                    
    /**
     * Verifica si existe un comercio con el nombre dado (excluyendo un ID opcional).
     *
     * @param string $nombre
     * @param string|null $excluirId
     * @return bool
     */
    public function existePorNombre(string $nombre, ?string $excluirId = null):bool;
    /**
     * Verifica si un comercio tiene máquinas asociadas.
     *
     * @param string $id
     * @return bool
     */
    public function tieneMaquinas(string $id):bool;
    /**
     * Cuenta los comercios que cumplen ciertos criterios.
     *
     * @param array $criterios
     * @return int
     */
    public function contar(array $criterios = []): int;        


}