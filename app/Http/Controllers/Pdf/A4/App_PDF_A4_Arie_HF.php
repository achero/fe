<?php
namespace App\Http\Controllers\Pdf\A4;

class App_PDF_A4_Arie_HF extends \TCPDF
{

    protected $data;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8',
        $diskcache = false, $pdfa = false, $data)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        $this->data = $data;
    }

    public function Header()
    {
        # Logo
        $logo = public_path('/static/images/logo_pdf_a4_default.jpg');
        if (file_exists(public_path('/static/images/logo_pdf_a4.jpg'))) {
            $logo = public_path('/static/images/logo_pdf_a4.jpg');
        }
        $this->SetCellPadding(0.7);
        $this->Image($logo, 5, 5, 47.625, 22.49);

        $this->SetXY(58, 5);
        $x = $this->GetX();
        $this->SetFont('helvetica', '', 8);

        # Breve descripción de la empresa emisora
        $rhm = $this->getStringHeight(0, 'STRING');
        $this->MultiCell(74, 0, $this->data['emisor_direccion_calle'], 'L', 'L', false, 1, '', '', true, 0, false,
            false, $rhm);
        $this->SetX($x);
        $this->MultiCell(74, 0, $this->data['emisor_direccion_general'], 'L', 'L', false, 1, '', '', true, 0, false,
            false, $rhm);

        if (isset($this->data['c_telephone']) && !empty($this->data['c_telephone'])) {
            $this->SetX($x);
            $this->MultiCell(74, 4, 'Tel.: ' . $this->data['c_telephone'], 'L', 'L');
        }

        if (isset($this->data['correo_contacto']) && !empty($this->data['correo_contacto'])) {
            $this->SetX($x);
            $this->MultiCell(74, 4, 'E-mail: ' . $this->data['correo_contacto'], 'L', 'L');
        }

        # Descripción del documento
        $this->SetXY(140, 5);
        $this->SetFont('helvetica', '', 10);
        $this->MultiCell(65, 7, 'R.U.C. ' . $this->data['emisor_ruc'], 'LTR', 'C', false, 1, '', '', true, 0, false,
            true, 7, 'M');
        $this->Ln(0);
        $this->SetX(140);
        $this->SetFont('helvetica', '', 12);
        $this->MultiCell(65, 8, $this->data['tipo_documento'], 'LR', 'C', false, 1, '', '', true, 0, false, true, 8, 'M');
        $this->Ln(0);
        $this->SetX(140);
        $this->SetFont('helvetica', '', 10);
        $this->MultiCell(65, 7, $this->data['serie'] . ' Nº ' . $this->data['correlativo'], 'LRB', 'C', false, 1, '',
            '', true, 0, false, true, 7, 'M');

        # Detalle del cliente
        $this->SetFont('helvetica', '', 8);
        $this->SetXY(5, 29);
        $y = $this->GetY();
        $this->SetTextColor(255, 255, 255);
        $this->MultiCell(27, 4, 'SEÑOR (TITULAR)', 1, 'L', true, 1, '', '', true, 0, false, false, $rhm);
        $this->SetXY(32, $y);
        $this->SetTextColor(0, 0, 0);
        $this->MultiCell(100, 4, $this->data['cliente_razon_social'], 'TR', 'L', false, 1, '', '', true, 0, false,
            false, $rhm);
        $this->SetXY(5, 33);
        $y = $this->GetY();
        $this->SetTextColor(255, 255, 255);
        $this->MultiCell(27, 4, 'DIRECCIÓN', 1, 'L', true);
        $this->SetXY(32, $y);
        $this->SetTextColor(0, 0, 0);
        $this->MultiCell(100, 4, $this->data['cliente_direccion'], 'R', 'L', false, 1, '', '', true, 0, false, false,
            $rhm);
        $this->SetXY(5, 37);
        $y = $this->GetY();
        $this->SetTextColor(255, 255, 255);
        $documentoTipo = '';
        switch ($this->data['c_additional_account_id']) :
            case '1':
                $documentoTipo = 'DNI';
                break;
            case '6':
                $documentoTipo = 'RUC';
                break;
        endswitch;
        $this->MultiCell(27, 4, $documentoTipo, 1, 'L', true);
        $this->SetXY(32, $y);
        $this->SetTextColor(0, 0, 0);
        $this->MultiCell(100, 4, $this->data['c_customer_assigned_account_id'], 'RB', 'L');

        # Detalle adicional del documento
        $this->SetXY(140, 29);
        $y = $this->GetY();
        $this->MultiCell(32, 4, 'TIPO DE MONEDA', 'TL', 'L');
        $this->SetXY(172, $y);
        $this->MultiCell(10, 4, ':', 'T', 'C');
        $this->SetXY(182, $y);
        $this->MultiCell(23, 4, $this->data['tipo_moneda'], 'TR', 'L');
        $this->SetXY(140, 33);
        $y = $this->GetY();
        $this->MultiCell(32, 4, 'FECHA DE EMISIÓN', 'L', 'L');
        $this->SetXY(172, $y);
        $this->MultiCell(10, 4, ':', '', 'C');
        $this->SetXY(182, $y);
        $this->MultiCell(23, 4, $this->data['fecha_emision'], 'R', 'L');
        if (isset($this->data['fecha_vencimiento']) && !empty($this->data['fecha_vencimiento'])) {
            $this->SetXY(140, 37);
            $y = $this->GetY();
            $this->MultiCell(32, 4, 'FECHA VENCIMIENTO', 'BL', 'L');
            $this->SetXY(172, $y);
            $this->MultiCell(10, 4, ':', 'B', 'C');
            $this->SetXY(182, $y);
            $this->MultiCell(23, 4, $this->data['fecha_vencimiento'], 'BR', 'L');
        }

        # Fila 01 descriptiva del clientes
        $this->Ln(1.5);
        $this->SetX(5);
        $this->SetTextColor(255, 255, 255);
        $this->MultiCell(32, 4, 'CÓDIGO CLIENTE', 1, 'C', true, 0);
        $this->MultiCell(28, 4, 'NÚMERO PEDIDO', 1, 'C', true, 0);
        $this->MultiCell(35, 4, 'ORDEN DE COMPRA', 1, 'C', true, 0);
        $this->MultiCell(40, 4, 'NUMERO DE GUÍA', 1, 'C', true, 0);
        $this->MultiCell(40, 4, 'CONDICIONES DE PAGO', 1, 'C', true, 0);
        $this->MultiCell(25, 4, (!is_null($this->data['doc_invoice_related'])) ? 'DCTO. ORIGEN' : '', 1, 'C', true, 1);

        $this->Ln(0);
        $this->SetX(5);
        $this->SetTextColor(0, 0, 0);
        $this->MultiCell(32, 4, $this->data['cliente_codigo'], 1, 'C', false, 0);
        $this->MultiCell(28, 4, $this->data['pedido_numero'], 1, 'C', false, 0);
        $this->MultiCell(35, 4, $this->data['compra_orden'], 1, 'C', false, 0);
        $this->MultiCell(40, 4, $this->data['guia_numero'], 1, 'C', false, 0);
        $this->MultiCell(40, 4, $this->data['pago_condiciones'], 1, 'C', false, 0);
        $this->MultiCell(25, 4, $this->data['doc_invoice_related'], 1, 'C', false, 1);

        # Fila 02 descriptiva del clientes
        $this->Ln(1.5);
        $this->SetX(5);
        $y = $this->GetY();
        $this->SetTextColor(255, 255, 255);
        $this->MultiCell(85, $rhm, isset($this->data['paciente']) ? 'PACIENTE' : '', 1, 'C', true);
        $this->SetXY(90, $y);
        $this->MultiCell(20, $rhm, isset($this->data['prf_nro']) ? 'PRF. NRO.' : '', 1, 'C', true);
        $this->SetXY(110, $y);
        $this->MultiCell(57, $rhm, isset($this->data['plan']) ? 'PLAN' : '', 1, 'C', true);
        $this->SetXY(167, $y);
        $this->MultiCell(19, $rhm, '', 1, 'C', true);
        $this->SetXY(186, $y);
        $this->MultiCell(19, $rhm, '', 1, 'C', true);

        $this->Ln(0);
        $this->SetX(5);
        $y = $this->GetY();
        $this->SetTextColor(0, 0, 0);
        $this->MultiCell(85, $rhm, isset($this->data['paciente']) ? $this->data['paciente'] : '', 'LBR', 'L', false, 0,
            '', '', true, 0, false, false, $rhm);
        $this->MultiCell(20, $rhm, isset($this->data['prf_nro']) ? $this->data['prf_nro'] : '', 'BR', 'C', false, 0);
        $this->MultiCell(57, $rhm, isset($this->data['plan']) ? $this->data['plan'] : '', 'BR', 'C', false, 0);
        $this->MultiCell(19, $rhm, '', 'BR', 'C', false, 0);
        $this->MultiCell(19, $rhm, '', 'BR', 'C', false, 1);

        # Cabecera de detalles
        $this->Ln(1.5);
        $this->SetTextColor(255, 255, 255);
        $y = $this->GetY();
        $precVtaUnitario = 'PRECIO DE VTA. UNITARIO';
        $h = $this->getStringHeight(25, $precVtaUnitario);

        $this->MultiCell(18, $h, 'CÓDIGO', 1, 'C', true, 1, '', '', true, 0, false, true, 10, 'T');
        $this->SetXY(23, $y);
        $this->MultiCell(115, $h, 'DESCRIPCIÓN DEL ARTÍCULO', 'TRB', 'C', true, 1, '', '', true, 0, false, true, 10, 'T');
        $this->SetXY(138, $y);
        $this->MultiCell(18, $h, 'CANTIDAD', 'TRB', 'C', true, 1, '', '', true, 0, false, true, 10, 'T');
        $this->SetXY(156, $y);
        $this->MultiCell(25, 0, 'PRECIO DE VTA. UNITARIO', 'TRB', 'C', true, 1, '', '', true, 0, false, true, 10, 'Y');
        $this->SetXY(181, $y);
        $this->MultiCell(24, $h, 'TOTAL', 'TBR', 'C', true, 1, '', '', true, 0, false, true, 10, 'T');
    }

}
