<?php
/**
 * Archivo: RegistrarComercioCommand.php
 * 
 * Comando (DTO) para la acción de registrar un nuevo comercio.
 * Contiene los datos necesarios para ejecutar el caso de uso.
 */

namespace maquinas_recreativas\Application\Commands\Comercio;

use maquinas_recreativas\Application\Commands\Command;

/**
 * Class RegistrarComercioCommand
 * 
 * Objeto inmutable que transporta los datos para registrar un comercio.
 */
class RegistrarComercioCommand implements Command
{
    private string $nombre;
    private string $tipo;
    private string $direccion;
    private string $telefono;
    private string $usuarioResponsableId;

    /**
     * @param string $nombre
     * @param string $tipo
     * @param string $direccion
     * @param string $telefono
     * @param string $usuarioResponsableId
     */
    public function __construct(
        string $nombre,
        string $tipo,
        string $direccion,
        string $telefono,
        string $usuarioResponsableId
    ) {
        $this->nombre = trim($nombre);
        $this->tipo = $tipo;
        $this->direccion = trim($direccion);
        $this->telefono = trim($telefono);
        $this->usuarioResponsableId = $usuarioResponsableId;
    }

    public function getNombre(): string {
        return $this->nombre;
    }

    public function getTipo(): string {
        return $this->tipo;
    }

    public function getDireccion(): string {
        return $this->direccion;
    }

    public function getTelefono(): string {
        return $this->telefono;
    }

    public function getUsuarioResponsableId(): string {
        return $this->usuarioResponsableId;
    }
}