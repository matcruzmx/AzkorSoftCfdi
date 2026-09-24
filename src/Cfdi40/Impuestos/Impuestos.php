<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\Cfdi40\Impuestos;

final class Impuestos
{
    /** @var Traslado[] */
    private array $traslados = [];

    /** @var Retencion[] */
    private array $retenciones = [];

    public function addTraslado(Traslado $t): self
    {
        $this->traslados[] = $t;
        return $this;
    }

    public function addRetencion(Retencion $r): self
    {
        $this->retenciones[] = $r;
        return $this;
    }

    public function getTraslados(): array
    {
        return $this->traslados;
    }

    public function getRetenciones(): array
    {
        return $this->retenciones;
    }

    public function totalTraslados(): float
    {
        return array_reduce(
            $this->traslados,
            fn ($s, Traslado $t) => $s + $t->getImporte(),
            0.0
        );
    }

    public function totalRetenciones(): float
    {
        return array_reduce(
            $this->retenciones,
            fn ($s, Retencion $r) => $s + $r->getImporte(),
            0.0
        );
    }
}
