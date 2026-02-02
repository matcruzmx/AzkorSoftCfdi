<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\PAC\Providers;

use SoapClient;
use AzkorSoft\Cfdi\PAC\PacInterface;
use AzkorSoft\Cfdi\PAC\TimbradoResult;
use AzkorSoft\Cfdi\Exceptions\CfdiException;

final class Finkok implements PacInterface
{
    private SoapClient $client;

    public function __construct(
        private string $user,
        private string $password,
        bool $production = false
    ) {
        $wsdl = $production
            ? 'https://ws.finkok.com/servicios/soap/stamp.wsdl'
            : 'https://demo-facturacion.finkok.com/servicios/soap/stamp.wsdl';

        $this->client = new SoapClient($wsdl, [
            'trace' => 1,
            'exceptions' => true
        ]);
    }

    public function timbrar(string $xml): TimbradoResult
    {
        $params = [
            'xml' => $xml,
            'username' => $this->user,
            'password' => $this->password
        ];

        try {
            $response = $this->client->stamp($params);
        } catch (\Throwable $e) {
            throw new CfdiException('Error de conexión con Finkok: ' . $e->getMessage());
        }

        if (isset($response->stampResult->Incidencias)) {
            $inc = $response->stampResult->Incidencias->Incidencia;
            throw new CfdiException(
                'Error PAC: ' . $inc->MensajeIncidencia
            );
        }

        $xmlTimbrado = $response->stampResult->xml;

        // Extraer datos del TimbreFiscalDigital
        $dom = new \DOMDocument();
        $dom->loadXML($xmlTimbrado);

        $tfd = $dom->getElementsByTagName('TimbreFiscalDigital')->item(0);

        return new TimbradoResult(
            uuid: $tfd->getAttribute('UUID'),
            xmlTimbrado: $xmlTimbrado,
            fechaTimbrado: $tfd->getAttribute('FechaTimbrado'),
            selloSat: $tfd->getAttribute('SelloSAT'),
            noCertificadoSat: $tfd->getAttribute('NoCertificadoSAT')
        );
    }
}
