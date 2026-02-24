<?php


namespace AzkorSoft\Cfdi\Utils;

class QrSat
{
    public static function generar(array $data): string
    {
		return sprintf(
			'https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?id=%s&re=%s&rr=%s&tt=%s&fe=%s',
			$data['timbre']['uuid'],
			$data['emisor']['rfc'],
			$data['receptor']['rfc'],
			number_format((float) $data['comprobante']['total'], 6, '.', ''),
			substr($data['comprobante']['sello_cfd'], -8)
		);
    }
}
