<?php
/**
 * Application/Commands/CommandHandler.php
 *
 * Interfaz base para todos los handlers de comandos.
 *
 * @package maquinas_recreativas\Application\Commands
 */

namespace maquinas_recreativas\Application\Commands;

/**
 * Interface CommandHandler
 *
 * Define el contrato para los handlers de comandos.
 */
interface CommandHandler
{
    /**
     * Maneja un comando.
     *
     * @param Command $command
     * @return mixed
     */
    public function handle(Command $command);
}