<?php


namespace AzkorSoft\Cfdi\XML;

use DOMDocument;
use DOMXPath;

class TimbreFiscalInserter
{
    public static function insertar(
        string $xmlOriginal,
        string $xmlTimbrado
    ): string {
        $docOriginal = new DOMDocument('1.0', 'UTF-8');
        $docOriginal->loadXML($xmlOriginal);

        $docTimbrado = new DOMDocument('1.0', 'UTF-8');
        $docTimbrado->loadXML($xmlTimbrado);

        // XPath
        $xpTimbrado = new DOMXPath($docTimbrado);
        $xpTimbrado->registerNamespace('cfdi', 'http://www.sat.gob.mx/cfd/4');
        $xpTimbrado->registerNamespace('tfd', 'http://www.sat.gob.mx/TimbreFiscalDigital');

        // Obtener TimbreFiscalDigital
        $timbre = $xpTimbrado->query('//tfd:TimbreFiscalDigital')->item(0);

        if (!$timbre) {
            throw new \Exception('No se encontró TimbreFiscalDigital');
        }

        // Importar nodo al XML original
        $timbreImportado = $docOriginal->importNode($timbre, true);

        // Buscar o crear cfdi:Complemento
        $xpOriginal = new DOMXPath($docOriginal);
        $xpOriginal->registerNamespace('cfdi', 'http://www.sat.gob.mx/cfd/4');

        $complemento = $xpOriginal->query('//cfdi:Complemento')->item(0);

        if (!$complemento) {
            $comprobante = $xpOriginal->query('/cfdi:Comprobante')->item(0);
            $complemento = $docOriginal->createElement('cfdi:Complemento');
            $comprobante->appendChild($complemento);
        }

        $complemento->appendChild($timbreImportado);

        return $docOriginal->saveXML();
    }
}
