<?php
/**
 * application/queries/componente/ObtenerComponentesEnUso.php
 *
 * Query para obtener componentes en uso por un usuario.
 *
 * @package maquinas_recreativas\Application\Queries\Componente
 */

namespace maquinas_recreativas\Application\Queries\Componente;

/**
 * Class ObtenerComponentesEnUsoQuery
 */
final class ObtenerComponentesEnUsoQuery
{
    private string $idUsuario;
    private ?string $idMaquina;

    public function __construct(string $idUsuario, ?string $idMaquina = null)
    {
        $this->idUsuario = $idUsuario;
        $this->idMaquina = $idMaquina;
    }

    public function getIdUsuario(): string { return $this->idUsuario; }
    public function getIdMaquina(): ?string { return $this->idMaquina; }
}