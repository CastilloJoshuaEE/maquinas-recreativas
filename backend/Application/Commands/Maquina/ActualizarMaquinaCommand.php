<?php
namespace maquinas_recreativas\Application\Commands\Maquina;

class ActualizarMaquinaCommand
{
    private string $idMaquina;
    private string $nombre;
    private string $tipo;
    private string $idComercio;
    private ?string $estado;

    public function __construct(string $idMaquina, string $nombre, string $tipo, string $idComercio, ?string $estado = null)
    {
        $this->idMaquina = $idMaquina;
        $this->nombre = $nombre;
        $this->tipo = $tipo;
        $this->idComercio = $idComercio;
        $this->estado = $estado;
    }

    public function getIdMaquina(): string { return $this->idMaquina; }
    public function getNombre(): string { return $this->nombre; }
    public function getTipo(): string { return $this->tipo; }
    public function getIdComercio(): string { return $this->idComercio; }
    public function getEstado(): ?string { return $this->estado; }
}