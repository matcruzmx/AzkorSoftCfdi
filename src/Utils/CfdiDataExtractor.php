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

        // =====================
        // Comprobante
        // =====================
        $data = [
            'comprobante' => [
                'version' => $xp->evaluate('string(/cfdi:Comprobante/@Version)'),
                'serie' => $xp->evaluate('string(/cfdi:Comprobante/@Serie)'),
                'folio' => $xp->evaluate('string(/cfdi:Comprobante/@Folio)'),
                'fecha' => $xp->evaluate('string(/cfdi:Comprobante/@Fecha)'),
                'forma_pago' => $xp->evaluate('string(/cfdi:Comprobante/@FormaPago)'),
                'moneda' => $xp->evaluate('string(/cfdi:Comprobante/@Moneda)'),
                'tipo_comprobante' => $xp->evaluate('string(/cfdi:Comprobante/@TipoDeComprobante)'),
                'metodo_pago' => $xp->evaluate('string(/cfdi:Comprobante/@MetodoPago)'),
                'lugar_expedicion' => $xp->evaluate('string(/cfdi:Comprobante/@LugarExpedicion)'),
                'subtotal' => $xp->evaluate('string(/cfdi:Comprobante/@SubTotal)'),
                'descuento' => $xp->evaluate('string(/cfdi:Comprobante/@Descuento)'),
                'total' => $xp->evaluate('string(/cfdi:Comprobante/@Total)'),
                'sello_cfd' => $xp->evaluate('string(/cfdi:Comprobante/@Sello)'),
                'certificado' => $xp->evaluate('string(/cfdi:Comprobante/@Certificado)'),
                'no_certificado' => $xp->evaluate('string(/cfdi:Comprobante/@NoCertificado)')
            ],

            // =====================
            // Emisor
            // =====================
            'emisor' => [
                'rfc' => $xp->evaluate('string(/cfdi:Comprobante/cfdi:Emisor/@Rfc)'),
                'nombre' => $xp->evaluate('string(/cfdi:Comprobante/cfdi:Emisor/@Nombre)'),
                'regimen_fiscal' => $xp->evaluate('string(/cfdi:Comprobante/cfdi:Emisor/@RegimenFiscal)')
            ],

            // =====================
            // Receptor
            // =====================
            'receptor' => [
                'rfc' => $xp->evaluate('string(/cfdi:Comprobante/cfdi:Receptor/@Rfc)'),
                'nombre' => $xp->evaluate('string(/cfdi:Comprobante/cfdi:Receptor/@Nombre)'),
                'uso_cfdi' => $xp->evaluate('string(/cfdi:Comprobante/cfdi:Receptor/@UsoCFDI)'),
                'domicilio_fiscal' => $xp->evaluate('string(/cfdi:Comprobante/cfdi:Receptor/@DomicilioFiscalReceptor)'),
                'regimen_fiscal' => $xp->evaluate('string(/cfdi:Comprobante/cfdi:Receptor/@RegimenFiscalReceptor)')
            ],

            'conceptos' => [],
            'impuestos' => [],
            'timbre' => []
        ];

        // =====================
        // Conceptos
        // =====================
$conceptos = $xp->query('//cfdi:Concepto');

if ($conceptos instanceof \DOMNodeList) {
    foreach ($conceptos as $conceptoNode) {
        if (!($conceptoNode instanceof \DOMElement)) {
            continue;
        }

        $concepto = $conceptoNode; // ahora sí, \DOMElement seguro

        $item = [
            'clave_prod_serv' => $concepto->getAttribute('ClaveProdServ'),
            'cantidad' => $concepto->getAttribute('Cantidad'),
            'clave_unidad' => $concepto->getAttribute('ClaveUnidad'),
            'unidad' => $concepto->getAttribute('Unidad'),
            'descripcion' => $concepto->getAttribute('Descripcion'),
            'valor_unitario' => $concepto->getAttribute('ValorUnitario'),
            'importe' => $concepto->getAttribute('Importe'),
            'descuento' => $concepto->getAttribute('Descuento'),
            'impuestos' => []
        ];

        // -------- Traslados --------
        $traslados = $xp->query('cfdi:Impuestos//cfdi:Traslado', $concepto);
        if ($traslados instanceof \DOMNodeList) {
            foreach ($traslados as $trasladoNode) {
                if (!($trasladoNode instanceof \DOMElement)) {
                    continue;
                }

                $item['impuestos'][] = [
                    'tipo' => 'traslado',
                    'impuesto' => $trasladoNode->getAttribute('Impuesto'),
                    'tipo_factor' => $trasladoNode->getAttribute('TipoFactor'),
                    'tasa_cuota' => $trasladoNode->getAttribute('TasaOCuota'),
                    'importe' => $trasladoNode->getAttribute('Importe')
                ];
            }
        }

        // -------- Retenciones --------
        $retenciones = $xp->query('cfdi:Impuestos//cfdi:Retencion', $concepto);
        if ($retenciones instanceof \DOMNodeList) {
            foreach ($retenciones as $retencionNode) {
                if (!($retencionNode instanceof \DOMElement)) {
                    continue;
                }

                $item['impuestos'][] = [
                    'tipo' => 'retencion',
                    'impuesto' => $retencionNode->getAttribute('Impuesto'),
                    'importe' => $retencionNode->getAttribute('Importe')
                ];
            }
        }

        $data['conceptos'][] = $item;
    }
}


        // =====================
        // Impuestos globales
        // =====================
        $data['impuestos'] = [
            'total_traslados' => $xp->evaluate('string(//cfdi:Impuestos/@TotalImpuestosTrasladados)'),
            'total_retenciones' => $xp->evaluate('string(//cfdi:Impuestos/@TotalImpuestosRetenidos)')
        ];

        // =====================
        // Timbre Fiscal Digital
        // =====================
        $data['timbre'] = [
            'uuid' => $xp->evaluate('string(//tfd:TimbreFiscalDigital/@UUID)'),
            'fecha_timbrado' => $xp->evaluate('string(//tfd:TimbreFiscalDigital/@FechaTimbrado)'),
            'rfc_prov_certif' => $xp->evaluate('string(//tfd:TimbreFiscalDigital/@RfcProvCertif)'),
            'sello_sat' => $xp->evaluate('string(//tfd:TimbreFiscalDigital/@SelloSAT)'),
            'no_certificado_sat' => $xp->evaluate('string(//tfd:TimbreFiscalDigital/@NoCertificadoSAT)')
        ];

        return $data;
    }
}



// class CfdiDataExtractor
// {
//     public static function extract(string $xml): array
//     {
//         $doc = new DOMDocument();
//         $doc->loadXML($xml);

//         $xp = new DOMXPath($doc);
//         $xp->registerNamespace('cfdi', 'http://www.sat.gob.mx/cfd/4');
//         $xp->registerNamespace('tfd', 'http://www.sat.gob.mx/TimbreFiscalDigital');

//         return [
//             'uuid' => $xp->evaluate('string(//tfd:TimbreFiscalDigital/@UUID)'),
//             'fecha' => $xp->evaluate('string(//cfdi:Comprobante/@Fecha)'),
//             'total' => $xp->evaluate('string(//cfdi:Comprobante/@Total)'),
//             'subtotal' => $xp->evaluate('string(//cfdi:Comprobante/@SubTotal)'),
//             'rfc_emisor' => $xp->evaluate('string(//cfdi:Emisor/@Rfc)'),
//             'nombre_emisor' => $xp->evaluate('string(//cfdi:Emisor/@Nombre)'),
//             'rfc_receptor' => $xp->evaluate('string(//cfdi:Receptor/@Rfc)'),
//             'nombre_receptor' => $xp->evaluate('string(//cfdi:Receptor/@Nombre)'),
//             'sello_sat' => $xp->evaluate('string(//tfd:TimbreFiscalDigital/@SelloSAT)'),
//             'sello_cfd' => $xp->evaluate('string(//cfdi:Comprobante/@Sello)')
//         ];
//     }
// }
