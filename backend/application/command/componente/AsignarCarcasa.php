<?php
/**
 * application/commands/componente/AsignarCarcasa.php
 *
 * Comando para asignar una carcasa a un técnico.
 *
 * @package Reconocimiento\Application\Commands\Componente
 */

namespace Reconocimiento\Application\Commands\Componente;

/**
 * Class AsignarCarcasa
 */
final class AsignarCarcasa
{
    private string $idComponente;
    private string $idUsuario;
    private function __construct(string $idComponente, string $idUsuario){
        $this->idComponente = $idComponente;
        $this->idUsuario = $idUsuario;
    }

    public function idComponente(): string { return $this->idComponente; }
    public function idUsuario(): string { return $this->idUsuario; }    
}