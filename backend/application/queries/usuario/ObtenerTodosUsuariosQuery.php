<?php
/**
 * application/queries/usuario/ObtenerTodosUsuarios.php
 *
 * Query para obtener todos los usuarios con filtros opcionales.
 *
 * @package maquinas_recreativas\Application\Queries\Usuario
 */

namespace maquinas_recreativas\Application\Queries\Usuario;

/**
 * Class ObtenerTodosUsuariosQuery
 */
final class ObtenerTodosUsuariosQuery
{
    private ?string $tipo;
    private ?string $estado;
    private ?string $ci;
    private int $limit;
    private int $offset;

    public function __construct(
        ?string $tipo = null,
        ?string $estado = null,
        ?string $ci = null,
        int $limit = 100,
        int $offset = 0
    ) {
        $this->tipo = $tipo;
        $this->estado = $estado;
        $this->ci = $ci;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function getTipo(): ?string { return $this->tipo; }
    public function getEstado(): ?string { return $this->estado; }
    public function getCi(): ?string { return $this->ci; }
    public function getLimit(): int { return $this->limit; }
    public function getOffset(): int { return $this->offset; }
}