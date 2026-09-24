<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\Cfdi40;

use AzkorSoft\Cfdi\Exceptions\CfdiException;

final class Comprobante
{
    private string $version = '4.0';
    private string $moneda = 'MXN';
    private string $tipoDeComprobante;
    private ?string $serie = null;
    private ?string $folio = null;
    private ?string $formaPago = null;
    private ?string $metodoPago = null;
	private ?Conceptos $conceptos = null;
	private float $subTotal = 0.0;
	private float $total = 0.0;
	private string $exportacion = '01'; // default: No aplica
	private string $lugarExpedicion;

    public function tipoDeComprobante(string $tipo): self
    {
        $this->tipoDeComprobante = $tipo;
        return $this;
    }

    public function serie(string $serie): self
    {
        $this->serie = $serie;
        return $this;
    }

    public function folio(string $folio): self
    {
        $this->folio = $folio;
        return $this;
    }

    public function formaPago(string $formaPago): self
    {
        $this->formaPago = $formaPago;
        return $this;
    }

    public function metodoPago(string $metodoPago): self
    {
        $this->metodoPago = $metodoPago;
        return $this;
    }

    public function moneda(string $moneda): self
    {
        $this->moneda = $moneda;
        return $this;
    }

	public function exportacion(string $clave): self
	{
		$this->exportacion = $clave;
		return $this;
	}
	public function lugarExpedicion(string $cp): self
	{
		if (!preg_match('/^\d{5}$/', $cp)) {
			throw new CfdiException('LugarExpedicion debe ser un código postal de 5 dígitos');
		}

		$this->lugarExpedicion = $cp;
		return $this;
	}


	public function conceptos(Conceptos $conceptos): self
	{
		$conceptos->validar();
		$this->conceptos = $conceptos;
		return $this;
	}



	public function calcularTotales(): void
	{
		if (!$this->conceptos) {
			throw new CfdiException('El comprobante no tiene conceptos');
		}

		$this->subTotal = round($this->conceptos->subtotal(), 2);

		$totalImpuestosTrasladados = round(
			$this->conceptos->totalImpuestosTrasladados(),
			2
		);

		$this->total = round(
			$this->subTotal + $totalImpuestosTrasladados,
			2
		);
	}
	public function validar(): void
	{
		if (empty($this->tipoDeComprobante)) {
			throw new CfdiException('Tipo de comprobante requerido');
		}

		if ($this->subTotal <= 0) {
			throw new CfdiException('SubTotal no calculado');
		}

		if ($this->total <= 0) {
			throw new CfdiException('Total no calculado');
		}
	}

		

	public function toArray(): array
	{
		return array_filter([
			'Version' => $this->version,
			'Serie' => $this->serie,
			'Folio' => $this->folio,
			'Moneda' => $this->moneda,
			'FormaPago' => $this->formaPago,
			'MetodoPago' => $this->metodoPago,
			'TipoDeComprobante' => $this->tipoDeComprobante,

			// 🔥 OBLIGATORIOS CFDI 4.0
			'Exportacion' => $this->exportacion,	
			'LugarExpedicion' => $this->lugarExpedicion,			
			'SubTotal' => number_format($this->subTotal, 2, '.', ''),
			'Total' => number_format($this->total, 2, '.', ''),
		], fn ($v) => $v !== null);
	}
}
