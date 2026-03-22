<?php
/**
 * Archivo: RegistrarComercioHandler.php
 * 
 * Manejador (Handler) del comando RegistrarComercioCommand.
 * Contiene la lógica de aplicación (caso de uso) para registrar un comercio.
 */

namespace Application\Command\Comercio;

use Domain\Comercio\Comercio;
use Domain\Comercio\ComercioRepository;
use Domain\Shared\Exceptions\DomainException;
use Domain\Shared\ValueObjects\Uuid;

use Infrastructure\Security\HistorialHelper;

/**
 * Class RegistrarComercioHandler
 */
class RegistrarComercioHandler {
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
     * @param RegistrarComercioCommand $command
     * @return Comercio La entidad de comercio recién creada.
     * @throws DomainException Si hay un error de negocio.
     */
    public function handle(RegistrarComercioCommand $command): Comercio {
        // 1. Validaciones de negocio
        if ($this->comercioRepository->existePorNombre($command->getNombre())) {
            throw new DomainException('Ya existe un comercio con ese nombre');
        }

        // 2. Crear la entidad (esto también valida los datos internamente)
        $comercio = Comercio::crear(
            Uuid::generar(),
            $command->getNombre(),
            $command->getTipo(),
            $command->getDireccion(),
            $command->getTelefono()
        );

        // 3. Persistir la entidad
        $this->comercioRepository->guardar($comercio);

        // 4. Registrar en el historial (evento de dominio simulado)
        $this->historialHelper->registrar(
            null, // No aplica a máquina
            $command->getUsuarioResponsableId(),
            'Usuario', // O obtener el tipo real del usuario
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