<?php
namespace App\Http\Controllers\Pdf\Ticket;

class App_PDF_Ticket_Arie
{

    public function generar($data)
    {
        $h = 0;

        $pdf = new TCPDF('P', 'mm');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(1, 1);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetFont('helvetica', '', 8);

        ##
        # Calculo de alto de pagina
        if (isset($data['ticket_titulo_1']) && !empty($data['ticket_titulo_1'])) {
            $h += $pdf->getStringHeight(70, $data['ticket_titulo_1']);
        }
        if (isset($data['ticket_titulo_2']) && !empty($data['ticket_titulo_2'])) {
            $h += $pdf->getStringHeight(70, $data['ticket_titulo_2']);
        }
        $h += $pdf->getStringHeight(70,
            $data['c_party_postal_address_street_name'] . ' ' . $data['c_party_postal_address_city_subdivision_name']);

        $h += $pdf->getStringHeight(70, $data['c_party_postal_address_district']);
        $h += $pdf->getStringHeight(70, 'RUC: ' . $data['emisor_ruc'] . ' TLF: ' . $data['c_telephone']);

        if (isset($data['serie_ticket']) && !empty($data['serie_ticket'])) {
            $h += $pdf->getStringHeight(70, $data['serie_ticket']);
        }

        $h += $pdf->getStringHeight(70, '=========================================');
        $h += $pdf->getStringHeight(70, 'FECHA');
        $h += $pdf->getStringHeight(70, 'TICKET');

        if (isset($data['s_caja']) && !empty($data['s_caja'])) {
            $h += $pdf->getStringHeight(70, 's_caja');
        }
        if (isset($data['cliente_razon_social']) && !empty($data['cliente_razon_social'])) {
            $h += $pdf->getStringHeight(52, $data['cliente_razon_social']);
        }

        $h += $pdf->getStringHeight(52, $data['c_customer_assigned_account_id']);

        if (isset($data['cliente_direccion']) && !empty($data['cliente_direccion'])) {
            $h += $pdf->getStringHeight(52, $data['cliente_direccion']);
        }
        if (isset($data['paciente']) && !empty($data['paciente'])) {
            $h += $pdf->getStringHeight(52, $data['paciente']);
        }
        if (isset($data['prf_nro']) && !empty($data['prf_nro'])) {
            $h += $pdf->getStringHeight(52, $data['prf_nro']);
        }
        if (isset($data['hc']) && !empty($data['hc'])) {
            $h += $pdf->getStringHeight(52, $data['hc']);
        }
        $h += $pdf->getStringHeight(70, '=========================================');
        $h += $pdf->getStringHeight(70, 'CANT');
        $h += $pdf->getStringHeight(70, '=========================================');
        foreach ($data['items'] as $value) {
            foreach ($value->DocInvoiceItemDescription as $k => $v) {
                $h += $pdf->getStringHeight(42, $v->c_description);
            }
        }
        $h += $pdf->getStringHeight(70, '=========================================');
        $h += $pdf->getStringHeight(70, '=========================================');
        $h += $pdf->getStringHeight(70, '=========================================');
        if (isset($data['pago_con']) && !empty($data['pago_con'])) {
            $h += $pdf->getStringHeight(42, $data['pago_con']);
        }
        if (isset($data['vuelto']) && !empty($data['vuelto'])) {
            $h += $pdf->getStringHeight(42, $data['vuelto']);
        }
        if (isset($data['usuario']) && !empty($data['usuario'])) {
            $h += $pdf->getStringHeight(42, $data['usuario']);
        }
        ##

        $pdf->AddPage('P', [72, $h + 5]);
        if (isset($data['ticket_titulo_1']) && !empty($data['ticket_titulo_1'])) {
            $pdf->MultiCell(70, 0, $data['ticket_titulo_1'], '', 'C');
        }
        if (isset($data['ticket_titulo_2']) && !empty($data['ticket_titulo_2'])) {
            $pdf->MultiCell(70, 0, $data['ticket_titulo_2'], '', 'C');
        }
        $pdf->MultiCell(70, 0,
            $data['c_party_postal_address_street_name'] . ' ' . $data['c_party_postal_address_city_subdivision_name'],
            '', 'C');
        $pdf->MultiCell(70, 0, $data['c_party_postal_address_district'], '', 'C');
        $pdf->MultiCell(70, 0, 'RUC: ' . $data['emisor_ruc'] . ' TLF: ' . $data['c_telephone'], '', 'L');
        if (isset($data['serie_ticket']) && !empty($data['serie_ticket'])) {
            $pdf->MultiCell(70, 0, 'SERIE: ' . $data['serie_ticket'], '', 'L');
        }
        $pdf->MultiCell(70, 0, '=========================================', '', 'L');
        $pdf->MultiCell(14, 0, 'FECHA', '', 'L', false, 0);
        $pdf->MultiCell(4, 0, ':', '', 'C', false, 0);
        if (isset($data['fecha_emision_hora']) && !empty($data['fecha_emision_hora'])) {
            $pdf->MultiCell(20, 0, $data['fecha_emision'], '', 'L', false, 0);
            $pdf->MultiCell(12, 0, 'HORA', '', 'R', false, 0);
            $pdf->MultiCell(4, 0, ':', '', 'C', false, 0);
            $pdf->MultiCell(16, 0, $data['fecha_emision_hora'], '', 'L', false, 1);
        } else {
            $pdf->MultiCell(20, 0, $data['fecha_emision'], '', 'L', false, 1);
        }
        $pdf->MultiCell(14, 0, 'TICKET', '', 'L', false, 0);
        $pdf->MultiCell(4, 0, ':', '', 'C', false, 0);
        $pdf->MultiCell(52, 0, $data['serie'] . ' - ' . $data['correlativo'], '', 'L', false, 1);
        if (isset($data['s_caja']) && !empty($data['s_caja'])) {
            $pdf->MultiCell(14, 0, 'S-CAJA', '', 'L', false, 0);
            $pdf->MultiCell(4, 0, ':', '', 'C', false, 0);
            $pdf->MultiCell(52, 0, $data['s_caja'], '', 'L', false, 1);
        }
        if (isset($data['cliente_razon_social']) && !empty($data['cliente_razon_social'])) {
            $pdf->MultiCell(14, 0, 'Titular', '', 'L', false, 0);
            $pdf->MultiCell(4, 0, ':', '', 'C', false, 0);
            $pdf->MultiCell(52, 0, $data['cliente_razon_social'], '', 'L', false, 1);
        }
        $pdf->MultiCell(14, 0, 'DNI', '', 'L', false, 0);
        $pdf->MultiCell(4, 0, ':', '', 'C', false, 0);
        $pdf->MultiCell(52, 0, $data['c_customer_assigned_account_id'], '', 'L', false, 1);
        if (isset($data['cliente_direccion']) && !empty($data['cliente_direccion'])) {
            $pdf->MultiCell(14, 0, 'Direcc.', '', 'L', false, 0);
            $pdf->MultiCell(4, 0, ':', '', 'C', false, 0);
            $pdf->MultiCell(52, 0, $data['cliente_direccion'], '', 'L', false, 1);
        }
        if (isset($data['paciente']) && !empty($data['paciente'])) {
            $pdf->MultiCell(14, 0, 'Paciente', '', 'L', false, 0);
            $pdf->MultiCell(4, 0, ':', '', 'C', false, 0);
            $pdf->MultiCell(52, 0, $data['paciente'], '', 'L', false, 1);
        }
        if (isset($data['prf_nro']) && !empty($data['prf_nro'])) {
            $pdf->MultiCell(14, 0, 'Prf. N.', '', 'L', false, 0);
            $pdf->MultiCell(4, 0, ':', '', 'C', false, 0);
            $pdf->MultiCell(52, 0, $data['prf_nro'], '', 'L', false, 1);
        }
        if (isset($data['hc']) && !empty($data['hc'])) {
            $pdf->MultiCell(14, 0, 'H.C.', '', 'L', false, 0);
            $pdf->MultiCell(4, 0, ':', '', 'C', false, 0);
            $pdf->MultiCell(52, 0, $data['hc'], '', 'L', false, 1);
        }
        $pdf->MultiCell(70, 0, '=========================================', '', 'L');
        $pdf->MultiCell(11, 0, 'CANT.', '', 'L', false, 0);
        $pdf->MultiCell(42, 0, 'DESCRIPCIÓN', '', 'L', false, 0);
        $pdf->MultiCell(17, 0, 'MONTO S/.', '', 'L', false, 1);
        $pdf->MultiCell(70, 0, '=========================================', '', 'L');
        foreach ($data['items'] as $value) {
            $description = '';
            $h = 0;
            foreach ($value->DocInvoiceItemDescription as $k => $v) {
                if ($k != 0) {
                    $description .= "\n";
                }
                $description .= $v->c_description;
                $h += $pdf->getStringHeight(42, $v->c_description);
            }

            $pdf->MultiCell(11, $h, $value->c_invoiced_quantity, '', 'L', false, 0, '', '', true, 0, false, false, $h);

            $pdf->MultiCell(42, $h, $description, '', 'L', false, 0, '', '', true, 0, false, false, $h);
            $pdf->MultiCell(17, $h, $value->c_line_extension_amount, '', 'R', false, 1, '', '', true, 0, false, false,
                $h);
        }
        $pdf->MultiCell(70, 0, '=========================================', '', 'L');
        $pdf->MultiCell(20, 0, 'TOTAL', '', 'L', false, 0);
        $pdf->MultiCell(8, 0, 'S/.:', '', 'R', false, 0);
        $pdf->MultiCell(42, 0, $data['total'], '', 'R', false, 1);
        $pdf->MultiCell(70, 0, '=========================================', '', 'L');
        if (isset($data['pago_con']) && !empty($data['pago_con'])) {
            $pdf->MultiCell(20, 0, 'PAGO CON', '', 'L', false, 0);
            $pdf->MultiCell(8, 0, 'S/.:', '', 'R', false, 0);
            $pdf->MultiCell(42, 0, $data['pago_con'], '', 'R', false, 1);
        }
        if (isset($data['vuelto']) && !empty($data['vuelto'])) {
            $pdf->MultiCell(20, 0, 'VUELTO', '', 'L', false, 0);
            $pdf->MultiCell(8, 0, 'S/.:', '', 'R', false, 0);
            $pdf->MultiCell(42, 0, $data['vuelto'], '', 'R', false, 1);
        }
        if (isset($data['usuario']) && !empty($data['usuario'])) {
            $pdf->MultiCell(20, 0, 'Usuario', '', 'L', false, 0);
            $pdf->MultiCell(8, 0, ':', '', 'R', false, 0);
            $pdf->MultiCell(42, 0, $data['usuario'], '', 'R', false, 1);
        }

        $pdf->Output($data['path'], 'F');
        chmod($data['path'], 0777);
    }

}
