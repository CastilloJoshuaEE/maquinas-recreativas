<?php
/**
 * Application/Queries/QueryHandler.php
 *
 * Interfaz base para todos los handlers de queries.
 *
 * @package maquinas_recreativas\Application\Queries
 */

namespace maquinas_recreativas\Application\Queries;

/**
 * Interface QueryHandler
 *
 * Define el contrato para los handlers de queries.
 */
interface QueryHandler
{
    /**
     * Maneja una query.
     *
     * @param Query $query
     * @return mixed
     */
    public function handle(Query $query);
}