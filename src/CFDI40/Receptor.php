<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\Cfdi40;

use AzkorSoft\Cfdi\Exceptions\CfdiException;

final class Receptor
{
    private string $rfc;
    private string $nombre;
    private string $usoCfdi;
    private string $domicilioFiscalReceptor;
    private string $regimenFiscalReceptor;

    public function rfc(string $rfc): self
    {
        $this->rfc = strtoupper($rfc);
        return $this;
    }

    public function nombre(string $nombre): self
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function usoCfdi(string $uso): self
    {
        $this->usoCfdi = $uso;
        return $this;
    }

    public function domicilioFiscal(string $cp): self
    {
        $this->domicilioFiscalReceptor = $cp;
        return $this;
    }

    public function regimenFiscal(string $regimen): self
    {
        $this->regimenFiscalReceptor = $regimen;
        return $this;
    }

    public function validar(): void
    {
        if (empty($this->rfc) || empty($this->usoCfdi)) {
            throw new CfdiException('Receptor incompleto');
        }
    }

    public function toArray(): array
    {
        return [
            'Rfc' => $this->rfc,
            'Nombre' => $this->nombre,
            'UsoCFDI' => $this->usoCfdi,
            'DomicilioFiscalReceptor' => $this->domicilioFiscalReceptor,
            'RegimenFiscalReceptor' => $this->regimenFiscalReceptor
        ];
    }
}
