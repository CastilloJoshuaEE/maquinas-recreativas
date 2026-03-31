<?php
/**
 * Archivo: RegistrarComercioHandler.php
 * 
 * Manejador (Handler) del comando RegistrarComercioCommand.
 * Contiene la lógica de aplicación (caso de uso) para registrar un comercio.
 */

namespace maquinas_recreativas\Application\Commands\Comercio;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\Comercio\Comercio;
use maquinas_recreativas\Domain\Comercio\ComercioRepository;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Security\HistorialHelper;

/**
 * Class RegistrarComercioHandler
 */
class RegistrarComercioHandler implements CommandHandler
{
    private ComercioRepository $comercioRepository;
    private HistorialHelper $historialHelper;

    /**
     * @param ComercioRepository $comercioRepository
     * @param HistorialHelper $historialHelper
     */
    public function __construct(
        ComercioRepository $comercioRepository,
        HistorialHelper $historialHelper
    ) {
        $this->comercioRepository = $comercioRepository;
        $this->historialHelper = $historialHelper;
    }

    /**
     * Ejecuta el caso de uso de registro de comercio.
     *
     * @param Command $command
     * @return Comercio La entidad de comercio recién creada.
     * @throws DomainException Si hay un error de negocio.
     */
    public function handle(Command $command): Comercio
    {
        if (!$command instanceof RegistrarComercioCommand) {
            throw new DomainException('Comando inválido');
        }

        // 1. Validaciones de negocio
        if ($this->comercioRepository->existePorNombre($command->getNombre())) {
            throw new DomainException('Ya existe un comercio con ese nombre');
        }

        // 2. Crear la entidad
        $comercio = Comercio::crear(
            Uuid::v4(),
            $command->getNombre(),
            $command->getTipo(),
            $command->getDireccion(),
            $command->getTelefono()
        );

        // 3. Persistir la entidad
        $this->comercioRepository->guardar($comercio);

        // 4. Registrar en el historial
        $this->historialHelper->registrar(
            null, // idMaquina
            $command->getUsuarioResponsableId(),
            'Usuario',
            'Registro de comercio',
            "Se registró el comercio: {$comercio->getNombre()}",
            null, null, null, null,
            $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            [
                'comercio_id' => $comercio->getId(),
                'comercio_nombre' => $comercio->getNombre()
            ]
        );

        return $comercio;
    }
}