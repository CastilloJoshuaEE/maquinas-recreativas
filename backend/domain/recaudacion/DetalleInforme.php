<?php
/**
 * domain/recaudacion/DetalleInforme.php
 *
 * Entidad que representa el detalle de componentes en un informe.
 *
 * @package Reconocimiento\Domain\Recaudacion
 */

namespace Reconocimiento\Domain\Recaudacion;

use Reconocimiento\Domain\Shared\ValueObjects\Uuid;

/**
 * Class DetalleInforme
 */
class DetalleInforme
{
    private Uuid $id;
    private Uuid $idInforme;
    private Uuid $idComponente;

    private function __construct(Uuid $id, Uuid $idInforme, Uuid $idComponente)
    {
        $this->id = $id;
        $this->idInforme = $idInforme;
        $this->idComponente = $idComponente;
    }

    public static function crear(Uuid $idInforme, Uuid $idComponente): self
    {
        return new self(
            Uuid::v4(),
            $idInforme,
            $idComponente
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            new Uuid($data['ID_Informe_Detalle']),
            new Uuid($data['ID_Informe']),
            new Uuid($data['ID_Componente'])
        );
    }

    public function toArray(): array
    {
        return [
            'ID_Informe_Detalle' => $this->id->value(),
            'ID_Informe' => $this->idInforme->value(),
            'ID_Componente' => $this->idComponente->value()
        ];
    }

    public function id(): Uuid { return $this->id; }
    public function idInforme(): Uuid { return $this->idInforme; }
    public function idComponente(): Uuid { return $this->idComponente; }
}