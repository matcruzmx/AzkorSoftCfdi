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

    // private function procesar(): void
    // {
    //     $cer = file_get_contents($this->cerPath);

    //     $this->certificadoBase64 = base64_encode($cer);

    //     $data = openssl_x509_parse($cer);
    //     $this->noCertificado = $data['serialNumberHex'];
    // }

private function procesar(): void
{
    $cer = file_get_contents($this->cerPath);

    if ($cer === false) {
        throw new CfdiException('No se pudo leer el archivo .cer');
    }

    // Certificado en Base64 (para el XML CFDI)
    $this->certificadoBase64 = base64_encode($cer);

    // Convertir DER → PEM (necesario para OpenSSL)
    $pem = "-----BEGIN CERTIFICATE-----\n";
    $pem .= chunk_split(base64_encode($cer), 64, "\n");
    $pem .= "-----END CERTIFICATE-----\n";

    $data = openssl_x509_parse($pem);

    if ($data === false || empty($data['serialNumberHex'])) {
        throw new CfdiException('No se pudo interpretar el certificado');
    }

    // 🔐 VALIDAR VIGENCIA DEL CERTIFICADO
    $now = time();

    if (
        $now < ($data['validFrom_time_t'] ?? 0) ||
        $now > ($data['validTo_time_t'] ?? 0)
    ) {
        throw new CfdiException('El certificado no está vigente');
    }

    // El SAT espera el número SIN ceros a la izquierda
    $this->noCertificado = ltrim($data['serialNumberHex'], '0');
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
