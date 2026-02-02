<?php


namespace AzkorSoft\Cfdi\Utils;

class QrSat
{
    public static function generar(array $data): string
    {
        return sprintf(
            'https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?id=%s&re=%s&rr=%s&tt=%s&fe=%s',
            $data['uuid'],
            $data['rfc_emisor'],
            $data['rfc_receptor'],
            number_format($data['total'], 6, '.', ''),
            substr($data['sello_cfd'], -8)
        );
    }
}
