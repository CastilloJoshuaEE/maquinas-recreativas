<?php
/**
 * application/commands/usuario/ActualizarPerfil.php
 *
 * Comando para que un usuario actualice su propio perfil.
 *
 * @package maquinas_recreativas\Application\Commands\Usuario
 */

namespace maquinas_recreativas\Application\Commands\Usuario;

/**
 * Class ActualizarPerfil
 */
final class ActualizarPerfil
{
    private string $id;
    private string $nombre;
    private string $apellido;
    private string $email;
    private string $ci;
    private string $tipo;
    private string $estado;
    private ?string $especialidad;
    private ?string $contrasena;
    public function __construct(string $id, string $nombre, string $apellido, string $email, string $ci, string $tipo, string $estado, ?string $especialidad=null, ?string $contrasena=null){
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->email = $email;
        $this->ci = $ci;
        $this->tipo = $tipo;
        $this->estado = $estado;
            $this->especialidad = $especialidad;
        $this->contrasena=$contrasena;    
    }
    public function id(): string { return $this->id; }
    public function nombre(): string { return $this->nombre; }
    public function apellido(): string { return $this->apellido; }
    public function email(): string { return $this->email; }
    public function ci(): string { return $this->ci; }
    public function tipo(): string { return $this->tipo; }
    public function estado(): string { return $this->estado; }
    public function especialidad(): ?string { return $this->especialidad; }
    public function contrasena(): ?string { return $this->contrasena; }
}