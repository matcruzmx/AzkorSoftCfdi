<?php

namespace AzkorSoft\Cfdi\Utils;

use DOMDocument;
use DOMXPath;

class CfdiDataExtractor
{
    public static function extract(string $xml): array
    {
        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $xp = new DOMXPath($doc);
        $xp->registerNamespace('cfdi', 'http://www.sat.gob.mx/cfd/4');
        $xp->registerNamespace('tfd', 'http://www.sat.gob.mx/TimbreFiscalDigital');

        return [
            'uuid' => $xp->evaluate('string(//tfd:TimbreFiscalDigital/@UUID)'),
            'fecha' => $xp->evaluate('string(//cfdi:Comprobante/@Fecha)'),
            'total' => $xp->evaluate('string(//cfdi:Comprobante/@Total)'),
            'subtotal' => $xp->evaluate('string(//cfdi:Comprobante/@SubTotal)'),
            'rfc_emisor' => $xp->evaluate('string(//cfdi:Emisor/@Rfc)'),
            'nombre_emisor' => $xp->evaluate('string(//cfdi:Emisor/@Nombre)'),
            'rfc_receptor' => $xp->evaluate('string(//cfdi:Receptor/@Rfc)'),
            'nombre_receptor' => $xp->evaluate('string(//cfdi:Receptor/@Nombre)'),
            'sello_sat' => $xp->evaluate('string(//tfd:TimbreFiscalDigital/@SelloSAT)'),
            'sello_cfd' => $xp->evaluate('string(//cfdi:Comprobante/@Sello)')
        ];
    }
}
