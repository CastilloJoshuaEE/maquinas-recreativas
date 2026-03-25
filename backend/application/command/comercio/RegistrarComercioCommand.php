<?php
/**
 * Archivo: RegistrarComercioCommand.php
 * 
 * Comando (DTO) para la acción de registrar un nuevo comercio.
 * Contiene los datos necesarios para ejecutar el caso de uso.
 */

namespace maquinas_recreativas\Command\Comercio;

/**
 * Class RegistrarComercioCommand
 * 
 * Objeto inmutable que transporta los datos para registrar un comercio.
 */
class RegistrarComercioCommand {
    private string $nombre;
    private string $tipo;
    private string $direccion;
    private string $telefono;
    private string $usuarioResponsableId; // Quién está realizando el registro (para historial)

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

    /**
     * @return string
     */
    public function getNombre(): string {
        return $this->nombre;
    }

    /**
     * @return string
     */
    public function getTipo(): string {
        return $this->tipo;
    }

    /**
     * @return string
     */
    public function getDireccion(): string {
        return $this->direccion;
    }

    /**
     * @return string
     */
    public function getTelefono(): string {
        return $this->telefono;
    }

    /**
     * @return string
     */
    public function getUsuarioResponsableId(): string {
        return $this->usuarioResponsableId;
    }
}