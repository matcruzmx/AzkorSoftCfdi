<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\Cfdi40;

use AzkorSoft\Cfdi\Exceptions\CfdiException;
use AzkorSoft\Cfdi\Cfdi40\Impuestos\Impuestos;

final class Concepto
{
    private string $claveProdServ;
    private string $descripcion;
    private string $claveUnidad;
    private float $cantidad;
    private float $valorUnitario;
    private float $importe;
    private ?string $noIdentificacion = null;
    private bool $objetoImp = true; // por default sí objeto de impuesto
	private ?Impuestos $impuestos = null;


    public function claveProdServ(string $clave): self
    {
        $this->claveProdServ = $clave;
        return $this;
    }

    public function descripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;
        return $this;
    }

    public function claveUnidad(string $clave): self
    {
        $this->claveUnidad = $clave;
        return $this;
    }

    public function cantidad(float $cantidad): self
    {
        if ($cantidad <= 0) {
            throw new CfdiException('Cantidad inválida');
        }

        $this->cantidad = $cantidad;
        return $this;
    }

    public function valorUnitario(float $valor): self
    {
        if ($valor < 0) {
            throw new CfdiException('Valor unitario inválido');
        }

        $this->valorUnitario = $valor;
        $this->calcularImporte();
        return $this;
    }

    public function noIdentificacion(string $noId): self
    {
        $this->noIdentificacion = $noId;
        return $this;
    }

    public function objetoImp(bool $objeto): self
    {
        $this->objetoImp = $objeto;
        return $this;
    }

    private function calcularImporte(): void
    {
        if (isset($this->cantidad, $this->valorUnitario)) {
            $this->importe = round(
                $this->cantidad * $this->valorUnitario,
                2
            );
        }
    }

    public function validar(): void
    {
        if (
            empty($this->claveProdServ) ||
            empty($this->descripcion) ||
            empty($this->claveUnidad)
        ) {
            throw new CfdiException('Concepto incompleto');
        }
    }

    public function getImporte(): float
    {
        return $this->importe;
    }



public function impuestos(): Impuestos
{
    return $this->impuestos ??= new Impuestos();
}

public function getImpuestos(): ?Impuestos
{
    return $this->impuestos;
}

    public function toArray(): array
    {
        return [
            'ClaveProdServ' => $this->claveProdServ,
            'NoIdentificacion' => $this->noIdentificacion,
            'Cantidad' => $this->cantidad,
            'ClaveUnidad' => $this->claveUnidad,
            'Descripcion' => $this->descripcion,
            'ValorUnitario' => number_format($this->valorUnitario, 2, '.', ''),
            'Importe' => number_format($this->importe, 2, '.', ''),
            'ObjetoImp' => $this->objetoImp ? '02' : '01'
        ];
    }
}
