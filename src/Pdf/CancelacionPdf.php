<?php

namespace AzkorSoft\Cfdi\Pdf;

use TCPDF;

class CancelacionPdf
{
    public static function generar(
        string $uuid,
        string $motivo,
        string $acuseXml,
        string $output
    ): void {
        $pdf = new TCPDF();
        $pdf->AddPage();

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'CFDI CANCELADO', 0, 1);

        $pdf->SetFont('helvetica', '', 9);
        $pdf->MultiCell(0, 5,
            "UUID: {$uuid}\n" .
            "Motivo SAT: {$motivo}\n" .
            "Fecha de cancelación: " . date('Y-m-d H:i:s')
        );

        $pdf->Ln(10);
        $pdf->MultiCell(0, 5, "Acuse SAT:\n\n{$acuseXml}");

        $pdf->Output($output, 'F');
    }
}
