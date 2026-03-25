<?php
/**
 * application/commands/componente/LiberarComponentesCancelacionHandler.php
 *
 * Manejador del comando LiberarComponentesCancelacion.
 *
 * @package Reconocimiento\Application\Commands\Componente
 */

namespace Reconocimiento\Application\Commands\Componente;

use Reconocimiento\Domain\Componente\ComponenteRepository;
use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use Reconocimiento\Domain\Shared\Exceptions\DomainException;

/**
 * Class LiberarComponentesCancelacionHandler
 */
final class LiberarComponentesCancelacionHandler
{
    private ComponenteRepository $componenteRepository;

    public function __construct(ComponenteRepository $componenteRepository)
    {
        $this->componenteRepository = $componenteRepository;
    }

    public function handle(LiberarComponentesCancelacion $command): array
    {
        $idUsuario = new Uuid($command->idUsuario());
        $componentesLiberados = 0;
        $mensajes = [];

        if ($command->idPlaca()) {
            $idPlaca = new Uuid($command->idPlaca());
            $placa = $this->componenteRepository->findById($idPlaca);

            if ($placa && $placa->estaAsignado() && $placa->usuarioAsignado()?->equals($idUsuario)) {
                $placa->liberar();
                $this->componenteRepository->save($placa);
                $componentesLiberados++;
                $mensajes[] = 'Placa liberada';
            }
        }

        if ($command->idCarcasa()) {
            $idCarcasa = new Uuid($command->idCarcasa());
            $carcasa = $this->componenteRepository->findById($idCarcasa);

            if ($carcasa && $carcasa->estaAsignado() && $carcasa->usuarioAsignado()?->equals($idUsuario)) {
                $carcasa->liberar();
                $this->componenteRepository->save($carcasa);
                $componentesLiberados++;
                $mensajes[] = 'Carcasa liberada';
            }
        }

        if ($componentesLiberados === 0) {
            throw new DomainException('No se encontraron componentes para liberar');
        }

        return [
            'success' => true,
            'message' => implode(', ', $mensajes) . ' correctamente'
        ];
    }
}