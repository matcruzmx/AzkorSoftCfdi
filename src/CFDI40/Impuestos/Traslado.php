<?php
declare(strict_types=1);
namespace AzkorSoft\Cfdi\Cfdi40\Impuestos;
use AzkorSoft\Cfdi\Exceptions\CfdiException;

final class Traslado
{
    private string $impuesto;    // 002 IVA, 003 IEPS
    private string $tipoFactor;  // Tasa
    private float $tasaOCuota;
    private float $base;
    private float $importe;

    public function __construct(
        string $impuesto,
        float $base,
        float $tasaOCuota,
        string $tipoFactor = 'Tasa'
    ) {
        if ($base <= 0) {
            throw new CfdiException('Base de impuesto inválida');
        }

        $this->impuesto   = $impuesto;
        $this->base       = round($base, 2);
        $this->tasaOCuota = $tasaOCuota;
        $this->tipoFactor = $tipoFactor;
        $this->importe    = round($this->base * $this->tasaOCuota, 2);
    }

    /* =======================
       GETTERS
       ======================= */

    public function getImpuesto(): string
    {
        return $this->impuesto;
    }

    public function getTipoFactor(): string
    {
        return $this->tipoFactor;
    }

    public function getTasaOCuota(): float
    {
        return $this->tasaOCuota;
    }

    public function getBase(): float
    {
        return $this->base;
    }

    public function getImporte(): float
    {
        return $this->importe;
    }

    /* =======================
       XML
       ======================= */

    public function toArray(): array
    {
        return [
            'Base'       => number_format($this->base, 2, '.', ''),
            'Impuesto'   => $this->impuesto,
            'TipoFactor' => $this->tipoFactor,
            'TasaOCuota' => number_format($this->tasaOCuota, 6, '.', ''),
            'Importe'    => number_format($this->importe, 2, '.', '')
        ];
    }
}


// declare(strict_types=1);

// namespace AzkorSoft\Cfdi\Cfdi40\Impuestos;

// use AzkorSoft\Cfdi\Exceptions\CfdiException;

// final class Traslado
// {
//     private string $impuesto; // 002 IVA, 003 IEPS
//     private string $tipoFactor; // Tasa
//     private float $tasaOCuota;
//     private float $base;
//     private float $importe;

//     public function __construct(
//         string $impuesto,
//         float $base,
//         float $tasaOCuota,
//         string $tipoFactor = 'Tasa'
//     ) {
//         if ($base <= 0) {
//             throw new CfdiException('Base de impuesto inválida');
//         }

//         $this->impuesto = $impuesto;
//         $this->base = round($base, 2);
//         $this->tasaOCuota = $tasaOCuota;
//         $this->tipoFactor = $tipoFactor;
//         $this->importe = round($this->base * $this->tasaOCuota, 2);
//     }

//     public function getImporte(): float
//     {
//         return $this->importe;
//     }

//     public function toArray(): array
//     {
//         return [
//             'Base' => number_format($this->base, 2, '.', ''),
//             'Impuesto' => $this->impuesto,
//             'TipoFactor' => $this->tipoFactor,
//             'TasaOCuota' => number_format($this->tasaOCuota, 6, '.', ''),
//             'Importe' => number_format($this->importe, 2, '.', '')
//         ];
//     }
// }
