<?php

namespace AzkorSoft\Cfdi\Pdf;

use TCPDF;
use AzkorSoft\Cfdi\Utils\CfdiDataExtractor;
use AzkorSoft\Cfdi\Utils\QrSat;

class CfdiPdf
{
    public static function generar(string $xml, string $output): void
    {
        $data = CfdiDataExtractor::extract($xml);
        $qr = QrSat::generar($data);

        $pdf = new TCPDF();
        $pdf->AddPage();

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'FACTURA CFDI 4.0', 0, 1);

        $pdf->SetFont('helvetica', '', 9);
        $pdf->MultiCell(0, 5,
            "Emisor: {$data['nombre_emisor']} ({$data['rfc_emisor']})\n" .
            "Receptor: {$data['nombre_receptor']} ({$data['rfc_receptor']})\n" .
            "UUID: {$data['uuid']}\n" .
            "Fecha: {$data['fecha']}\n" .
            "Subtotal: {$data['subtotal']}\n" .
            "Total: {$data['total']}"
        );

        $pdf->write2DBarcode($qr, 'QRCODE,H', 150, 20, 40, 40);

        $pdf->Ln(45);
        $pdf->MultiCell(0, 5, "Sello CFD:\n{$data['sello_cfd']}\n\nSello SAT:\n{$data['sello_sat']}");

        $pdf->Output($output, 'F');
    }
}
