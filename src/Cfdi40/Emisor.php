<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\Cfdi40;

use AzkorSoft\Cfdi\Exceptions\CfdiException;

final class Emisor
{
    private string $rfc;
    private string $nombre;
    private string $regimenFiscal;

    public function rfc(string $rfc): self
    {
        if (strlen($rfc) < 12) {
            throw new CfdiException('RFC de emisor inválido');
        }

        $this->rfc = strtoupper($rfc);
        return $this;
    }

    public function nombre(string $nombre): self
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function regimenFiscal(string $regimen): self
    {
        $this->regimenFiscal = $regimen;
        return $this;
    }

    public function validar(): void
    {
        if (empty($this->rfc) || empty($this->regimenFiscal)) {
            throw new CfdiException('Emisor incompleto');
        }
    }

    public function toArray(): array
    {
        return [
            'Rfc' => $this->rfc,
            'Nombre' => $this->nombre,
            'RegimenFiscal' => $this->regimenFiscal
        ];
    }
}
