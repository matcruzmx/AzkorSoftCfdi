<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\Cfdi40;

use AzkorSoft\Cfdi\Exceptions\CfdiException;

final class Cfdi
{
    private ?Comprobante $comprobante = null;
    private ?Emisor $emisor = null;
    private ?Receptor $receptor = null;
	private ?Conceptos $conceptos = null;

    public function comprobante(): Comprobante
    {
        return $this->comprobante ??= new Comprobante();
    }

    public function emisor(): Emisor
    {
        return $this->emisor ??= new Emisor();
    }

    public function receptor(): Receptor
    {
        return $this->receptor ??= new Receptor();
    }
    
	public function conceptos(): Conceptos
    {
        return $this->conceptos ??= new Conceptos();
    }

public function subtotal(): float
{
    return $this->conceptos()->subtotal();
}

public function totalImpuestosTrasladados(): float
{
    $total = 0.0;

    foreach ($this->conceptos()->all() as $concepto) {
        $total += $concepto->getImpuestos()?->totalTraslados() ?? 0;
    }

    return round($total, 2);
}

public function total(): float
{
    return round(
        $this->subtotal() + $this->totalImpuestosTrasladados(),
        2
    );
}

    public function validar(): void
    {
        if (!$this->comprobante || !$this->emisor || !$this->receptor) {
            throw new CfdiException('CFDI incompleto: faltan nodos obligatorios');
        }

		$this->conceptos?->validar();
    }
}
