<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\Cfdi40\Impuestos;

final class Retencion
{
    private string $impuesto;
    private float $importe;

    public function __construct(string $impuesto, float $importe)
    {
        $this->impuesto = $impuesto;
        $this->importe = round($importe, 2);
    }

    public function getImporte(): float
    {
        return $this->importe;
    }

    public function toArray(): array
    {
        return [
            'Impuesto' => $this->impuesto,
            'Importe' => number_format($this->importe, 2, '.', '')
        ];
    }
}
