<?php
// application/queries/usuario/ObtenerTecnicosPorEspecialidadHandler.php

namespace maquinas_recreativas\Application\Queries\Usuario;

use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class ObtenerTecnicosPorEspecialidadHandler
{
    private UsuarioRepository $usuarioRepository;
 
    public function __construct(UsuarioRepository $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }
 
    public function handle(ObtenerTecnicosPorEspecialidadQuery $query): array
    {
        $especialidadesValidas = ['Ensamblador', 'Comprobador', 'Mantenimiento'];
 
        if (!in_array($query->getEspecialidad(), $especialidadesValidas, true)) {
            throw new DomainException('Especialidad no válida.');
        }
 
        $tecnicos = $this->usuarioRepository->findTecnicosByEspecialidad($query->getEspecialidad());
 
        $resultado = [];
        foreach ($tecnicos as $tecnico) {
            // Sanitización profunda para evitar fallos en json_encode
            $item = [];
            foreach ($tecnico as $key => $value) {
                if ($value === null) {
                    $item[$key] = '';
                } elseif (is_string($value)) {
                    // Eliminar caracteres de control y convertir a UTF-8 limpio
                    $clean = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);
                    if (!mb_check_encoding($clean, 'UTF-8')) {
                        $clean = mb_convert_encoding($clean, 'UTF-8', 'UTF-8');
                    }
                    $item[$key] = $clean;
                } else {
                    $item[$key] = $value;
                }
            }
            $resultado[] = $item;
        }
 
        error_log("Handler especialidad={$query->getEspecialidad()}: " . count($resultado) . " técnicos sanitizados");
        return $resultado;
    }
}