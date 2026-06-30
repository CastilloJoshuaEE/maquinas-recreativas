<?php
// Application/Queries/Maquina/ObtenerMaquinaPorIdQuery.php

namespace maquinas_recreativas\Application\Queries\Maquina;

class ObtenerMaquinaPorIdQuery
{
    private string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }
}