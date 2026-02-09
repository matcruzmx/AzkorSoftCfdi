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
        // if (!file_exists($xsltPath)) {
        //     throw new CfdiException('XSLT de cadena original no encontrado');
        // }

        // $dom = new DOMDocument();
        // $dom->loadXML($xml);

        // $xsl = new DOMDocument();
        // $xsl->load($xsltPath);

        // $proc = new XSLTProcessor();
        // $proc->importStylesheet($xsl);

        // $cadena = $proc->transformToXML($dom);

        // if (!$cadena) {
        //     throw new CfdiException('Error al generar cadena original');
        // }

        // return trim($cadena);

  // Guardar estado previo
    $prev = libxml_use_internal_errors(true);

    try {
        $xmlDom = new \DOMDocument();
        $xmlDom->loadXML($xml);

        $xslDom = new \DOMDocument();
        $xslDom->load($xsltPath);

        $proc = new \XSLTProcessor();
        $proc->importStylesheet($xslDom);

        $cadena = $proc->transformToXML($xmlDom);

        if ($cadena === false) {
            throw new \RuntimeException('No se pudo generar la cadena original');
        }

        return trim($cadena);
    } finally {
        // 🔥 Restaurar estado anterior
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
    }


    }
}
