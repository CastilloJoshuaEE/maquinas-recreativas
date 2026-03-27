<?php
/**
 * application/commands/notificacion/CrearNotificacionMaquinaCommand.php
 *
 * Comando para crear una notificación de máquina.
 *
 * @package maquinas_recreativas\Application\Commands\Notificacion
 */

namespace maquinas_recreativas\Application\Commands\Notificacion;

/**
 * Class CrearNotificacionMaquina
 */
final class CrearNotificacionMaquinaCommand
{
    private string $idRemitente;
    private string $idDestinatario;
    private string $idMaquina;
    private string $tipo;
    private string $mensaje;

    public function __construct(
        string $idRemitente,
        string $idDestinatario,
        string $idMaquina,
        string $tipo,
        string $mensaje
    ) {
        $this->idRemitente = $idRemitente;
        $this->idDestinatario = $idDestinatario;
        $this->idMaquina = $idMaquina;
        $this->tipo = $tipo;
        $this->mensaje = $mensaje;
    }

    public function idRemitente(): string { return $this->idRemitente; }
    public function idDestinatario(): string { return $this->idDestinatario; }
    public function idMaquina(): string { return $this->idMaquina; }
    public function tipo(): string { return $this->tipo; }
    public function mensaje(): string { return $this->mensaje; }
}