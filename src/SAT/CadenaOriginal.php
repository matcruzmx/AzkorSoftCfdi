<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\SAT;

use DOMDocument;
use XSLTProcessor;
use AzkorSoft\Cfdi\Exceptions\CfdiException;

final class CadenaOriginal
{
    public static function generar(
        string $xml,
        string $xsltPath
    ): string {
        if (!file_exists($xsltPath)) {
            throw new CfdiException('XSLT de cadena original no encontrado');
        }

        $dom = new DOMDocument();
        $dom->loadXML($xml);

        $xsl = new DOMDocument();
        $xsl->load($xsltPath);

        $proc = new XSLTProcessor();
        $proc->importStylesheet($xsl);

        $cadena = $proc->transformToXML($dom);

        if (!$cadena) {
            throw new CfdiException('Error al generar cadena original');
        }

        return trim($cadena);
    }
}
