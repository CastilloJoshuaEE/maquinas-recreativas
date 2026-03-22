<?php

declare(strict_types=1);

namespace RecreaSys\Application\Query\Usuario;

use RecreaSys\Application\Query\Query;

/**
 * Query para buscar un usuario por su email.
 *
 * @package RecreaSys\Application\Query\Usuario
 * @version 1.0
 */
final class BuscarPorEmailQuery implements Query
{
    public string $email;

    /**
     * Constructor de la query.
     *
     * @param string $email
     */
    public function __construct(string $email)
    {
        $this->email = $email;
    }
}