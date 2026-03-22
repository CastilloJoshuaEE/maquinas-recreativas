<?php
/**
 * RecreaSys - Application Command
 *
 * Comando para registrar un nuevo usuario.
 *
 * @package RecreaSys\Application\Command\Usuario
 * @author Tu Equipo
 * @version 1.0
 */

namespace RecreaSys\Application\Command\Usuario;

/**
 * Class RegistrarUsuarioCommand
 *
 * DTO (Data Transfer Object) inmutable que transporta los datos necesarios
 * para ejecutar el caso de uso "RegistrarUsuario".
 */
class RegistrarUsuarioCommand
{
    private string $nombre;
    private string $apellido;
    private string $ci;
    private string $email;
    private string $contrasenaPlana;
    private string $tipo;
    private ?string $especialidad;

    /**
     * RegistrarUsuarioCommand constructor.
     *
     * @param string $nombre
     * @param string $apellido
     * @param string $ci
     * @param string $email
     * @param string $contrasenaPlana
     * @param string $tipo
     * @param string|null $especialidad
     */
    public function __construct(
        string $nombre,
        string $apellido,
        string $ci,
        string $email,
        string $contrasenaPlana,
        string $tipo,
        ?string $especialidad = null
    ) {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->ci = $ci;
        $this->email = $email;
        $this->contrasenaPlana = $contrasenaPlana;
        $this->tipo = $tipo;
        $this->especialidad = $especialidad;
    }

    public function getNombre(): string { return $this->nombre; }
    public function getApellido(): string { return $this->apellido; }
    public function getCi(): string { return $this->ci; }
    public function getEmail(): string { return $this->email; }
    public function getContrasenaPlana(): string { return $this->contrasenaPlana; }
    public function getTipo(): string { return $this->tipo; }
    public function getEspecialidad(): ?string { return $this->especialidad; }
}