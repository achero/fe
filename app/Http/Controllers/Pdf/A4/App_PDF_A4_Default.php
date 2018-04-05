<?php
namespace App\Http\Controllers\Pdf\A4;

class App_PDF_A4_Default
{

    public function generar($data)
    {
        # Detalles generales del documento
        $pdf = new App_PDF_A4_Default_HF('P', 'mm', 'A4', true, 'UTF-8', false, false, $data);
        $pdf->setPrintFooter(false);
        $pdf->SetTopMargin(63.23889);
        $pdf->SetLeftMargin(5);
        $pdf->SetRightMargin(5);
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(false);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetCellPadding(0.5);

        # Items
        $yBegin = $pdf->GetY();
        $pdf->SetTextColor(0, 0, 0);

        foreach ($data['items'] as $key => $value) {
            $y = $pdf->GetY();
            $x = 5;
            $description = '';
            $h = 0;

            foreach ($value->DocInvoiceItemDescription as $k => $v) {
                if ($k != 0) {
                    $description .= "\n";
                }
                $description .= $v->c_description;
                $h += $pdf->getStringHeight(115, $v->c_description);
            }

            # Crear nueva página cuando te acercas a su límite
            if (($y + $h) > 250) {
                $h = 250 - $y;
                $pdf->MultiCell(18, $h, '', 'LRB', 'L', false, 1, '', '', true, 0, false, false, $h);
                $pdf->SetXY($x + 18, $y);
                $x = $pdf->GetX();
                $pdf->MultiCell(115, $h, '', 'RB', 'L', false, 1, '', '', true, 0, false, false, $h);
                $pdf->SetXY($x + 115, $y);
                $x = $pdf->GetX();
                $pdf->MultiCell(18, $h, '', 'RB', 'R', false, 1, '', '', true, 0, false, false, $h);
                $pdf->SetXY($x + 18, $y);
                $x = $pdf->GetX();
                $pdf->MultiCell(25, $h, '', 'RB', 'R', false, 1, '', '', true, 0, false, false, $h);
                $pdf->SetXY($x + 25, $y);
                $x = $pdf->GetX();
                $pdf->MultiCell(24, $h, '', 'RB', 'R', false, 1, '', '', true, 0, false, false, $h);
                $y = $yBegin;
                $x = 5;
                $pdf->AddPage();
            } else if (($y + $h) == 250) {
                $y = $yBegin;
                $pdf->AddPage();
            }
            $pdf->SetXY($x, $y);
            $pdf->MultiCell(18, $h, $value->c_item_sellers_item_identification_id, 'LR', 'L');
            $pdf->SetXY($x + 18, $y);
            $x = $pdf->GetX();
            $pdf->MultiCell(115, $h, $description, 'R', 'L');
            $pdf->SetXY($x + 115, $y);
            $x = $pdf->GetX();
            $pdf->MultiCell(18, $h, $value->c_invoiced_quantity, 'R', 'R');
            $pdf->SetXY($x + 18, $y);
            $x = $pdf->GetX();
            $pdf->MultiCell(25, $h, $value->c_price_price_amount, 'R', 'R');
            $pdf->SetXY($x + 25, $y);
            $x = $pdf->GetX();
            $pdf->MultiCell(24, $h, $value->c_line_extension_amount, 'R', 'R');
        }

        # Textos adicionales #
        $append = [];
        if (isset($data['emisor_detraccion']) && !empty($data['emisor_detraccion'])) {
            $append[] = $data['emisor_detraccion'];
        } else if (isset($data['leyenda'])) {
            $append[] = $data['leyenda'];
        }
        if (isset($data['leyenda'])) {
            $append[] = $data['observacion'];
        }
        if (isset($data['monto_en_letras']) && !empty($data['monto_en_letras'])) {
            $append[] = $data['monto_en_letras'];
        }
        if (isset($data['monto_en_letras']) && !empty($data['monto_en_letras'])) {
            $append[] = "S.E.U.O.";
        }
        $height = 0;
        foreach ($append as $value) {
            $height += $pdf->getStringHeight(115, $value);
        }
        $pdf->SetX(5);
        $y = $pdf->GetY();
        $diff = 250 - $height - $y;
        $x = 5;
        if ((250 - $y) < $height) {
            $h = 250 - $y;
            $pdf->MultiCell(18, $h, '', 'LRB', 'L', false, 1, '', '', true, 0, false, false, $h);
            $pdf->SetXY($x + 18, $y);
            $x = $pdf->GetX();
            $pdf->MultiCell(115, $h, '', 'RB', 'L', false, 1, '', '', true, 0, false, false, $h);
            $pdf->SetXY($x + 115, $y);
            $x = $pdf->GetX();
            $pdf->MultiCell(18, $h, '', 'RB', 'R', false, 1, '', '', true, 0, false, false, $h);
            $pdf->SetXY($x + 18, $y);
            $x = $pdf->GetX();
            $pdf->MultiCell(25, $h, '', 'RB', 'R', false, 1, '', '', true, 0, false, false, $h);
            $pdf->SetXY($x + 25, $y);
            $x = $pdf->GetX();
            $pdf->MultiCell(24, $h, '', 'RB', 'R', false, 1, '', '', true, 0, false, false, $h);
            $y = $yBegin;
            $x = 5;
            $pdf->AddPage();
            $diff = 250 - $height - $y;
        }
        $pdf->MultiCell(18, $diff, '', 'LR', 'L', false, 1, '', '', true, 0, false, false, $diff);
        $pdf->SetXY($x + 18, $y);
        $x = $pdf->GetX();
        $pdf->MultiCell(115, $diff, '', 'R', 'L', false, 1, '', '', true, 0, false, false, $diff);
        $pdf->SetXY($x + 115, $y);
        $x = $pdf->GetX();
        $pdf->MultiCell(18, $diff, '', 'R', 'R', false, 1, '', '', true, 0, false, false, $diff);
        $pdf->SetXY($x + 18, $y);
        $x = $pdf->GetX();
        $pdf->MultiCell(25, $diff, '', 'R', 'R', false, 1, '', '', true, 0, false, false, $diff);
        $pdf->SetXY($x + 25, $y);
        $x = $pdf->GetX();
        $pdf->MultiCell(24, $diff, '', 'R', 'R', false, 1, '', '', true, 0, false, false, $diff);

        foreach ($append as $key => $value) {
            $b = '';
            if ((count($append) - 1) == $key) {
                $b = 'B';
            }
            $y = $pdf->GetY();
            $x = $pdf->GetX();
            $h = $pdf->getStringHeight(115, $value);
            $pdf->MultiCell(18, $h, '', 'LR' . $b, 'L');
            $pdf->SetXY($x + 18, $y);
            $x = $pdf->GetX();
            $pdf->MultiCell(115, $h, $value, 'R' . $b, 'L');
            $pdf->SetXY($x + 115, $y);
            $x = $pdf->GetX();
            $pdf->MultiCell(18, $h, '', 'R' . $b, 'L');
            $pdf->SetXY($x + 18, $y);
            $x = $pdf->GetX();
            $pdf->MultiCell(25, $h, '', 'R' . $b, 'L');
            $pdf->SetXY($x + 25, $y);
            $x = $pdf->GetX();
            $pdf->MultiCell(24, $h, '', 'R' . $b, 'L');
        }

        # Pie de página
        $pdf->Ln(1.5);
        $pdf->SetTextColor(255, 255, 255);
        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->SetXY($x, $y);
        $pdf->MultiCell(30, 4, 'OP. EXONERADA', 1, 'C', true);
        $pdf->SetXY($x + 30, $y);
        $x = $pdf->GetX();
        $pdf->MultiCell(30, 4, 'OP. INAFECTA', 1, 'C', true);
        $pdf->SetXY($x + 30, $y);
        $x = $pdf->GetX();
        $pdf->MultiCell(30, 4, 'OP. GRAVADA', 1, 'C', true);
        $pdf->SetXY($x + 30, $y);
        $x = $pdf->GetX();
        $pdf->MultiCell(30, 4, 'TOT. DSCTO.', 1, 'C', true);
        $pdf->SetXY($x + 30, $y);
        $x = $pdf->GetX();
        $pdf->MultiCell(20, 4, 'I.S.C.', 1, 'C', true);
        $pdf->SetXY($x + 20, $y);
        $x = $pdf->GetX();
        $pdf->MultiCell(20, 4, 'I.G.V.', 1, 'C', true);
        $pdf->SetXY($x + 20, $y);
        $x = $pdf->GetX();
        $pdf->MultiCell(40, 4, 'IMPORTE TOTAL', 1, 'C', true);

        $pdf->Ln(0);
        $pdf->SetTextColor(0, 0, 0);
        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->SetXY($x, $y);
        $pdf->MultiCell(30, 4, $data['op_exoneradas'], 1, 'R');
        $pdf->SetXY($x + 30, $y);
        $x = $pdf->GetX();
        $pdf->MultiCell(30, 4, $data['op_inafectas'], 1, 'R');
        $pdf->SetXY($x + 30, $y);
        $x = $pdf->GetX();
        $pdf->MultiCell(30, 4, $data['op_gravadas'], 1, 'R');
        $pdf->SetXY($x + 30, $y);
        $x = $pdf->GetX();
        $pdf->MultiCell(30, 4, $data['total_descuentos'], 1, 'R');
        $pdf->SetXY($x + 30, $y);
        $x = $pdf->GetX();
        $pdf->MultiCell(20, 4, $data['isc'], 1, 'R');
        $pdf->SetXY($x + 20, $y);
        $x = $pdf->GetX();
        $pdf->MultiCell(20, 4, $data['igv'], 1, 'R');
        $pdf->SetXY($x + 20, $y);
        $x = $pdf->GetX();
        $pdf->MultiCell(40, 4, $data['total'], 1, 'R');

        $pdf->Ln(1.5);
        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->SetXY($x, $y);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->MultiCell(100, 0, 'Representación Impresa de la ' . $data['tipo_documento'], '', 'C');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->MultiCell(100, 0, 'Autorizado mediante Resolución de Intendencia Nº ' . $data['emisor_resolucion'], '', 'C');
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->MultiCell(100, 0, 'Consulta tu comprobante en nuestra web ' . env('URL_CONSULTA_WEB'), '', 'C');    
        //$pdf->write2DBarcode($data['codigoBarra'], 'PDF417', $x + 140, $y, 70);
        $pdf->write2DBarcode($data['codigoBarra'], 'QRCODE,L', $x + 180, $y, 20);        
        $pdf->Output($data['path'], 'F');
        chmod($data['path'], 0777);
    }

}
