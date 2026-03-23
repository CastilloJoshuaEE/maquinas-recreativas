<?php
/**
 * application/commands/maquina/MandarAComprobacion.php
 *
 * Comando para enviar una máquina a comprobación.
 *
 * @package Reconocimiento\Application\Commands\Maquina
 */

namespace Reconocimiento\Application\Commands\Maquina;

/**
 * Class MandarAComprobacion
 */
final class MandarAComprobacion
{
    private string $idMaquina;
    private string $idRemitente;
    private string $mensaje;
    public function __construct(string $idMaquina, string $idRemitente, string $mensaje){
        $this->idMaquina = $idMaquina;
        $this->idRemitente = $idRemitente;
        $this->mensaje = $mensaje;
    }    
    public function idMaquina(): string { return $this->idMaquina; }
    public function idRemitente(): string { return $this->idRemitente; }
    public function mensaje(): string { return $this->mensaje; }    
}