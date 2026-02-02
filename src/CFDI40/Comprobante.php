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

    public function validar(): void
    {
        if (empty($this->tipoDeComprobante)) {
            throw new CfdiException('Tipo de comprobante requerido');
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

        ]);
    }
}
