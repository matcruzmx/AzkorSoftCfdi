<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\Security;

use AzkorSoft\Cfdi\Exceptions\CfdiException;

final class Certificado
{
    private string $cerPath;
    private string $noCertificado;
    private string $certificadoBase64;

    public function __construct(string $cerPath)
    {
        if (!file_exists($cerPath)) {
            throw new CfdiException('Archivo .cer no encontrado');
        }

        $this->cerPath = $cerPath;
        $this->procesar();
    }

    private function procesar(): void
    {
        $cer = file_get_contents($this->cerPath);

        $this->certificadoBase64 = base64_encode($cer);

        $data = openssl_x509_parse($cer);
        $this->noCertificado = $data['serialNumberHex'];
    }

    public function getNoCertificado(): string
    {
        return $this->noCertificado;
    }

    public function getCertificado(): string
    {
        return $this->certificadoBase64;
    }
}
