<?php
/**
 * application/commands/maquina/RegistrarMaquina.php
 *
 * Comando para registrar una nueva máquina recreativa.
 *
 * @package Reconocimiento\Application\Commands\Maquina
 */

namespace Reconocimiento\Application\Commands\Maquina;

use Reconocimiento\Domain\Shared\ValueObjects\Uuid;

/**
 * Class RegistrarMaquina
 */
final class RegistrarMaquina
{
    private string $nombre;
    private string $tipo;
    private string $idComercio;
    private string $idUsuarioLogistica;
    private string $idPlaca;
    private string $idCarcasa;

    public function __construct(
        string $nombre,
        string $tipo,
        string $idComercio,
        string $idUsuarioLogistica,
        string $idPlaca,
        string $idCarcasa
    ) {
        $this->nombre = $nombre;
        $this->tipo = $tipo;
        $this->idComercio = $idComercio;
        $this->idUsuarioLogistica = $idUsuarioLogistica;
        $this->idPlaca = $idPlaca;
        $this->idCarcasa = $idCarcasa;
    }

    public function nombre(): string { return $this->nombre; }
    public function tipo(): string { return $this->tipo; }
    public function idComercio(): string { return $this->idComercio; }
    public function idUsuarioLogistica(): string { return $this->idUsuarioLogistica; }
    public function idPlaca(): string { return $this->idPlaca; }
    public function idCarcasa(): string { return $this->idCarcasa; }
}