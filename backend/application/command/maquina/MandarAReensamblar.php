<?php
/**
 * application/commands/maquina/MandarAReensamblar.php
 *
 * Comando para enviar una máquina a reensamblar.
 *
 * @package maquinas_recreativas\Application\Commands\Maquina
 */

namespace maquinas_recreativas\Application\Commands\Maquina;

/**
 * Class MandarAReensamblar
 */
final class MandarAReensamblar
{
    private string $idMaquina;
    private string $idRemitente;
    private string $mensaje;

    public function __construct(string $idMaquina, string $idRemitente, string $mensaje)
    {
        $this->idMaquina = $idMaquina;
        $this->idRemitente = $idRemitente;
        $this->mensaje = $mensaje;
    }

    public function idMaquina(): string { return $this->idMaquina; }
    public function idRemitente(): string { return $this->idRemitente; }
    public function mensaje(): string { return $this->mensaje; }
}