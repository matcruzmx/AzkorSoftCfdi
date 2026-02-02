<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\Security;

use AzkorSoft\Cfdi\Exceptions\CfdiException;

final class SelloDigital
{
    public static function generar(
        string $cadenaOriginal,
        LlavePrivada $llave
    ): string {
        $resultado = openssl_sign(
            $cadenaOriginal,
            $firma,
            $llave->getResource(),
            OPENSSL_ALGO_SHA256
        );

        if (!$resultado) {
            throw new CfdiException('Error al generar sello digital');
        }

        return base64_encode($firma);
    }
}
