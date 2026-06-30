<?php
/**
 * Application/Commands/Email/EnviarEmailHandler.php
 */

namespace maquinas_recreativas\Application\Commands\Email;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Infrastructure\Services\Email\BrevoEmailService;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class EnviarEmailHandler implements CommandHandler
{
    private BrevoEmailService $emailService;

    public function __construct(BrevoEmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * @return array{ success: bool, messageId?: string }
     */
    public function handle(Command $command): array
    {
        if (!$command instanceof EnviarEmailCommand) {
            throw new DomainException('Comando inválido para EnviarEmailHandler');
        }

        // Validar email
        if (!filter_var($command->destinatarioEmail, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException("El email '{$command->destinatarioEmail}' no tiene un formato válido");
        }

        if (empty($command->asunto)) {
            throw new DomainException('El asunto no puede estar vacío');
        }
        if (empty($command->mensaje)) {
            throw new DomainException('El mensaje no puede estar vacío');
        }

        // Construir texto con firma del remitente
        $mensajeConFirma = $command->mensaje . "\n\n---\nEnviado por: {$command->remitenteNombre} (RecreaSys Admin)";

        error_log("EnviarEmailHandler: enviando a {$command->destinatarioEmail}, asunto: {$command->asunto}");

        $resultado = $this->emailService->enviar(
            $command->destinatarioEmail,
            $command->destinatarioNombre,
            $command->asunto,
            $mensajeConFirma
        );

        if (!$resultado['success']) {
            throw new DomainException('Error al enviar el email: ' . ($resultado['error'] ?? 'Error desconocido'));
        }

        return $resultado;
    }
}