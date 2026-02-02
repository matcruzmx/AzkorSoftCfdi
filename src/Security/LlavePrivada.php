<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\Security;

use AzkorSoft\Cfdi\Exceptions\CfdiException;
use OpenSSLAsymmetricKey;

final class LlavePrivada
{
    private OpenSSLAsymmetricKey $resource;

    public function __construct(string $keyPath, string $password)
    {
        if (!file_exists($keyPath)) {
            throw new CfdiException('Archivo .key no encontrado');
        }

        $key = file_get_contents($keyPath);

        if ($key === false) {
            throw new CfdiException('No se pudo leer el archivo .key');
        }

        // 🔑 Convertir DER → PEM (PKCS#8 ENCRYPTED)
        $pem  = "-----BEGIN ENCRYPTED PRIVATE KEY-----\n";
        $pem .= chunk_split(base64_encode($key), 64, "\n");
        $pem .= "-----END ENCRYPTED PRIVATE KEY-----\n";

        $resource = openssl_pkey_get_private($pem, $password);

        if ($resource === false) {
            throw new CfdiException(
                'No se pudo abrir la llave privada. ' .
                'Verifica contraseña o archivo .key'
            );
        }

        $this->resource = $resource;
    }

    public function getResource(): OpenSSLAsymmetricKey
    {
        return $this->resource;
    }
}
