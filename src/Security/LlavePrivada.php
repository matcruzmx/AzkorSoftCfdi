<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\Security;

use AzkorSoft\Cfdi\Exceptions\CfdiException;

final class LlavePrivada
{
    private $resource;

    public function __construct(
        string $keyPath,
        string $password
    ) {
        if (!file_exists($keyPath)) {
            throw new CfdiException('Archivo .key no encontrado');
        }

        $key = file_get_contents($keyPath);

        $this->resource = openssl_pkey_get_private($key, $password);

        if (!$this->resource) {
            throw new CfdiException('No se pudo abrir la llave privada');
        }
    }

    public function getResource()
    {
        return $this->resource;
    }
}
