<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\Cfdi40;

use AzkorSoft\Cfdi\Exceptions\CfdiException;

final class Conceptos
{
    /** @var Concepto[] */
    private array $items = [];

    public function add(Concepto $concepto): self
    {
        $concepto->validar();
        $this->items[] = $concepto;
        return $this;
    }

    public function all(): array
    {
        return $this->items;
    }

    public function subtotal(): float
    {
        return array_reduce(
            $this->items,
            fn ($sum, Concepto $c) => $sum + $c->getImporte(),
            0.0
        );
    }

public function totalImpuestosTrasladados(): float
{
    $total = 0.0;

    foreach ($this->items as $concepto) {
        foreach ($concepto->getImpuestos()?->getTraslados() ?? [] as $t) {
            $total += $t->getImporte();
        }
    }

    return round($total, 2);
}


    public function validar(): void
    {
        if (count($this->items) === 0) {
            throw new CfdiException('El CFDI debe tener al menos un concepto');
        }
    }
}
