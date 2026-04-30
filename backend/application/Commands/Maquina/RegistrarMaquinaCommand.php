<?php
// application/commands/maquina/RegistrarMaquinaCommand.php

namespace maquinas_recreativas\Application\Commands\Maquina;

use maquinas_recreativas\Application\Commands\Command;

final class RegistrarMaquinaCommand implements Command
{
    private string $nombre;
    private string $tipo;
    private string $idComercio;
    private string $idUsuarioLogistica;
    private string $idPlaca;
    private string $idCarcasa;
    private ?string $idEnsamblador;
    private ?string $idComprobador;

    public function __construct(
        string $nombre,
        string $tipo,
        string $idComercio,
        string $idUsuarioLogistica,
        string $idPlaca,
        string $idCarcasa,
        ?string $idEnsamblador = null,
        ?string $idComprobador = null
    ) {
        $this->nombre = $nombre;
        $this->tipo = $tipo;
        $this->idComercio = $idComercio;
        $this->idUsuarioLogistica = $idUsuarioLogistica;
        $this->idPlaca = $idPlaca;
        $this->idCarcasa = $idCarcasa;
        $this->idEnsamblador = $idEnsamblador;
        $this->idComprobador = $idComprobador;
    }

    // Getters existentes
    public function nombre(): string { return $this->nombre; }
    public function tipo(): string { return $this->tipo; }
    public function idComercio(): string { return $this->idComercio; }
    public function idUsuarioLogistica(): string { return $this->idUsuarioLogistica; }
    public function idPlaca(): string { return $this->idPlaca; }
    public function idCarcasa(): string { return $this->idCarcasa; }
    
    // Nuevos getters
    public function idEnsamblador(): ?string { return $this->idEnsamblador; }
    public function idComprobador(): ?string { return $this->idComprobador; }
}