<?php
/**
 * Application/Commands/Email/EnviarEmailCommand.php
 */

namespace maquinas_recreativas\Application\Commands\Email;

use maquinas_recreativas\Application\Commands\Command;

final class EnviarEmailCommand implements Command
{
    public string $destinatarioEmail;
    public string $destinatarioNombre;
    public string $asunto;
    public string $mensaje;
    public string $remitenteNombre; // nombre del admin que envía

    public function __construct(
        string $destinatarioEmail,
        string $destinatarioNombre,
        string $asunto,
        string $mensaje,
        string $remitenteNombre = 'Administrador'
    ) {
        $this->destinatarioEmail  = trim($destinatarioEmail);
        $this->destinatarioNombre = trim($destinatarioNombre);
        $this->asunto             = trim($asunto);
        $this->mensaje            = trim($mensaje);
        $this->remitenteNombre    = trim($remitenteNombre);
    }
}