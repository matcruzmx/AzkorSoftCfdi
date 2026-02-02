<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\XML;

use DOMDocument;
use DOMElement;
use AzkorSoft\Cfdi\Cfdi40\Cfdi;
use AzkorSoft\Cfdi\Cfdi40\Concepto;
use AzkorSoft\Cfdi\Security\Certificado;
use AzkorSoft\Cfdi\Security\LlavePrivada;
use AzkorSoft\Cfdi\Security\SelloDigital;
use AzkorSoft\Cfdi\SAT\CadenaOriginal;

final class XmlBuilder implements XmlBuilderInterface
{
    private const CFDI_NS = 'http://www.sat.gob.mx/cfd/4';
    private const XSI_NS  = 'http://www.w3.org/2001/XMLSchema-instance';

    public function build(Cfdi $cfdi): string
    {
        $cfdi->validar();

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $comprobante = $dom->createElementNS(
            self::CFDI_NS,
            'cfdi:Comprobante'
        );

        $comprobante->setAttributeNS(
            self::XSI_NS,
            'xsi:schemaLocation',
            'http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd'
        );

        $this->setAttributes($comprobante, $cfdi->comprobante()->toArray());

        // Emisor
        $emisor = $dom->createElement('cfdi:Emisor');
        $this->setAttributes($emisor, $cfdi->emisor()->toArray());
        $comprobante->appendChild($emisor);

        // Receptor
        $receptor = $dom->createElement('cfdi:Receptor');
        $this->setAttributes($receptor, $cfdi->receptor()->toArray());
        $comprobante->appendChild($receptor);

		// Conceptos
		if ($cfdi->conceptos()) {

			$conceptosNode = $dom->createElement('cfdi:Conceptos');

			foreach ($cfdi->conceptos()->all() as $concepto) {

				$conceptoNode = $dom->createElement('cfdi:Concepto');
				$this->setAttributes($conceptoNode, $concepto->toArray());
				$conceptosNode->appendChild($conceptoNode);



$impuestos = $concepto->getImpuestos();

if ($impuestos) {
    $impNode = $dom->createElement('cfdi:Impuestos');

    if ($impuestos->getTraslados()) {
        $trasladosNode = $dom->createElement('cfdi:Traslados');

        foreach ($impuestos->getTraslados() as $t) {
            $tNode = $dom->createElement('cfdi:Traslado');
            $this->setAttributes($tNode, $t->toArray());
            $trasladosNode->appendChild($tNode);
        }

        $impNode->appendChild($trasladosNode);
    }

    $conceptoNode->appendChild($impNode);
}




			}

			$comprobante->appendChild($conceptosNode);
		}		


$totalTraslados = $cfdi->totalImpuestosTrasladados();

if ($totalTraslados > 0) {
    $impGlobal = $dom->createElement('cfdi:Impuestos');
    $impGlobal->setAttribute(
        'TotalImpuestosTrasladados',
        number_format($totalTraslados, 2, '.', '')
    );

    $trasladosNode = $dom->createElement('cfdi:Traslados');

    foreach ($cfdi->conceptos()->all() as $concepto) {
        foreach ($concepto->getImpuestos()?->getTraslados() ?? [] as $t) {
            $tNode = $dom->createElement('cfdi:Traslado');
            $this->setAttributes($tNode, $t->toArray());
            $trasladosNode->appendChild($tNode);
        }
    }

    $impGlobal->appendChild($trasladosNode);
    $comprobante->appendChild($impGlobal);
}


        $dom->appendChild($comprobante);

        return $dom->saveXML();
    }

    private function setAttributes(DOMElement $node, array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            if ($value !== null && $value !== '') {
                $node->setAttribute($key, (string) $value);
            }
        }
    }

public function sellar(
    string $xml,
    Certificado $cert,
    LlavePrivada $key,
    string $xsltPath
): string {
    $cadena = CadenaOriginal::generar($xml, $xsltPath);
    $sello  = SelloDigital::generar($cadena, $key);

    $dom = new \DOMDocument();
    $dom->loadXML($xml);

    $comprobante = $dom->documentElement;

    $comprobante->setAttribute('Sello', $sello);
    $comprobante->setAttribute('NoCertificado', $cert->getNoCertificado());
    $comprobante->setAttribute('Certificado', $cert->getCertificado());

    return $dom->saveXML();
}









}
