<?php
namespace App\Http\Controllers\Core;

use Illuminate\Support\Facades\Log;
use Validator;
use Exception;
use DOMDocument;

use App\SupSupplier;
use App\CusCustomer;
use App\DocInvoice;
use App\DocInvoiceFile;
use App\DocInvoiceCdrStatus;
use App\DocInvoiceSupplier;
use App\DocInvoiceItem;
use App\DocInvoiceTicket;
use App\DocInvoiceItemAllowancecharge;
use App\DocInvoiceItemBillingPayment;
use App\DocInvoiceItemTaxTotal;
use App\DocInvoiceItemTaxTotalTaxSubtotal;
use App\DocInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme;


use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Resumen diario
 */
class SummaryDocumentsCore
{

    /* Resumen Versión 1.1 */
    public static function getFile($file)
    {
        $output['status'] = false;
        $output['message'] = '';
        $output['document'] = '';
        $response = [];
        $count = [];
        try {
            $count['SummaryDocuments']['sac:SummaryDocumentsLine'] = 0;
            $billingPayment = 0;
            foreach ($file as $key => $value) {
                if (!mb_check_encoding($value, "UTF-8")) {
                    $value = utf8_encode($value);
                }
                $value = trim($value);
                $line = explode('|', $value);
                switch ($key) {
                    case 0:
                        foreach ($line as $k => $v) {
                            switch ($k) {
                                case 0:
                                    # Apellidos y nombres o denominacion o razon social
                                    $response['SummaryDocuments']['cac:AccountingSupplierParty']['cac:Party']['cac:PartyLegalEntity']['cbc:RegistrationName'] = $v;
                                    break;
                                case 1:
                                    # Numero de RUC
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            case 0:
                                                $response['SummaryDocuments']['cac:AccountingSupplierParty']['cbc:CustomerAssignedAccountID'] = $va;
                                                break;
                                            case 1:
                                                $response['SummaryDocuments']['cac:AccountingSupplierParty']['cbc:AdditionalAccountID'] = $va;
                                                break;
                                        }
                                    }
                                    break;
                                case 2:
                                    # Fecha de emision de los documentos
                                    $response['SummaryDocuments']['cbc:ReferenceDate'] = $v;
                                    break;
                                case 3:
                                    # Identificador del resumen
                                    $response['SummaryDocuments']['cbc:ID'] = $v;
                                    break;
                                case 4:
                                    # Fecha de generacion del resumen
                                    $response['SummaryDocuments']['cbc:IssueDate'] = $v;
                                    break;
                                case 5:
                                    #Firma Digital
                                    break;
                                case 6:
                                    # Version del UBL utilizado para establecer el formato XML
                                    $response['SummaryDocuments']['cbc:UBLVersionID'] = $v;
                                    break;
                                case 7:
                                    # Version de la estructura del documento
                                    $response['SummaryDocuments']['cbc:CustomizationID'] = $v;
                                    break;                              
                                case 8:
                                    # Nota para el resumen 1.1
                                    $response['SummaryDocuments']['cbc:Note'] = $v;
                                    break;                                     
                            }
                        }
                        break;
                    default:
                        $c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal'] = 0;
                        foreach ($line as $k => $v) {
                            switch ($k) {
                                case 0:
                                    # Tipo del document
                                    $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                        [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['cbc:DocumentTypeCode'] = $v;
                                    break;
                                case 1:
                                    # Numero de serie de los documentos
                                    $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                        [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['sac:DocumentSerialID'] = $v;
                                    break;
                                case 2:
                                    # Numero correlativo del documento de inicio dentro de la serie
                                    $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                        [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['sac:StartDocumentNumberID'] = $v;
                                    break;
                                case 3:
                                    # Numero de documento de identidad
                                    $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                        [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['cbc:CustomerAssignedAccountID'] = $v;
                                    break;
                                case 4:
                                    # Numero correlativo del documento de fin dentro de la serie
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            case 0:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                                    [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['sac:BillingPayment'][$billingPayment]['cbc:PaidAmount'] = $va;
                                                break;
                                            case 1:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                                    [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['sac:BillingPayment'][$billingPayment]['cbc:InstructionID'] = $va;
                                                break;
                                        }
                                    }
                                    $billingPayment++;
                                    break;
                                case 5:
                                    # Total valor de venta - operaciones gravadas
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            case 0:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                                    [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['sac:BillingPayment'][$billingPayment]['cbc:PaidAmount'] = $va;
                                                break;
                                            case 1:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                                    [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['sac:BillingPayment'][$billingPayment]['cbc:InstructionID'] = $va;
                                                break;
                                        }
                                    }
                                    $billingPayment++;
                                    break;
                                case 6:
                                    # Total valor de venta - operaciones exoneradas
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            case 0:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                                    [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['sac:BillingPayment'][$billingPayment]['cbc:PaidAmount'] = $va;
                                                break;
                                            case 1:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                                    [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['sac:BillingPayment'][$billingPayment]['cbc:InstructionID'] = $va;
                                                break;
                                        }
                                    }
                                    $billingPayment++;
                                    break;
                                case 7:
                                    # Importe total de sumatoria otros cargos del item
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            case 0:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                                    [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['cac:AllowanceCharge']['cbc:ChargeIndicator'] = $va;
                                                break;
                                            case 1:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                                    [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['cac:AllowanceCharge']['cbc:Amount'] = $va;
                                                break;
                                        }
                                    }
                                    break;
                                case 8:
                                    # Total ISC
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            /* Monto de IGV de la linea */
                                            case 0:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cbc:TaxAmount'] = $va;
                                                break;
                                            /* Monto de IGV de la linea */
                                            case 1:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cbc:TaxAmount'] = $va;
                                                break;
                                            /* Codigo de tributo - Catalogo No 05 */
                                            case 2:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:ID'] = $va;
                                                break;
                                            /* Nombre de tributo - Catalogo No 05 */
                                            case 3:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:Name'] = utf8_encode($va);
                                                break;
                                            /* Codigo internacional tributo - Catalogo No 05 */
                                            case 4:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:TaxTypeCode'] = $va;
                                                break;
                                        }
                                    }
                                    $c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal'] ++;
                                    break;
                                case 9:
                                    # Total IGV
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            /* Monto de IGV de la linea */
                                            case 0:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cbc:TaxAmount'] = $va;
                                                break;
                                            /* Monto de IGV de la linea */
                                            case 1:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cbc:TaxAmount'] = $va;
                                                break;
                                            /* Codigo de tributo - Catalogo No 05 */
                                            case 2:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:ID'] = $va;
                                                break;
                                            /* Nombre de tributo - Catalogo No 05 */
                                            case 3:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:Name'] = utf8_encode($va);
                                                break;
                                            /* Codigo internacional tributo - Catalogo No 05 */
                                            case 4:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:TaxTypeCode'] = $va;
                                                break;
                                        }
                                    }
                                    $c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal'] ++;
                                    break;
                                case 10:
                                    # Total otros tributos
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            /* Monto de IGV de la linea */
                                            case 0:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cbc:TaxAmount'] = $va;
                                                break;
                                            /* Monto de IGV de la linea */
                                            case 1:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cbc:TaxAmount'] = $va;
                                                break;
                                            /* Codigo de tributo - Catalogo No 05 */
                                            case 2:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:ID'] = $va;
                                                break;
                                            /* Nombre de tributo - Catalogo No 05 */
                                            case 3:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:Name'] = utf8_encode($va);
                                                break;
                                            /* Codigo internacional tributo - Catalogo No 05 */
                                            case 4:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:TaxTypeCode'] = $va;
                                                break;
                                        }
                                    }
                                    $c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal'] ++;
                                    break;
                                case 11:
                                    # Importe total de la venta, cesion en uso o del servicio prestado
                                    $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['sac:TotalAmount'] = $v;
                                    break;
                                case 12:
                                    # Numero de Fila
                                    $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['cbc:LineID'] = $v;
                                    break;
                            }
                        }
                        $count['SummaryDocuments']['sac:SummaryDocumentsLine'] ++;
                        break;
                }
            }
            $output['status'] = true;
            $output['document'] = $response;

            Log::info('Generación de array',
                [
                'lgph_id' => 1, 'c_id' => $response['SummaryDocuments']['cbc:ID'],
                'c_invoice_type_code' => 'RC',
                ]
            );
        } catch (Exception $exc) {
            Log::error($exc->getMessage(), ['lgph_id' => 1]);
            $output['message'] = $exc->getMessage();
        }
        return $output;
    }

    /* Resumen Versión 1.0 */
    public static function _getFile($file)
    {
        $output['status'] = false;
        $output['message'] = '';
        $output['document'] = '';
        $response = [];
        $count = [];
        try {
            $count['SummaryDocuments']['sac:SummaryDocumentsLine'] = 0;
            $billingPayment = 0;
            foreach ($file as $key => $value) {
                if (!mb_check_encoding($value, "UTF-8")) {
                    $value = utf8_encode($value);
                }
                $value = trim($value);
                $line = explode('|', $value);
                switch ($key) {
                    case 0:
                        foreach ($line as $k => $v) {
                            switch ($k) {
                                case 0:
                                    # Apellidos y nombres o denominacion o razon social
                                    $response['SummaryDocuments']['cac:AccountingSupplierParty']['cac:Party']['cac:PartyLegalEntity']['cbc:RegistrationName'] = $v;
                                    break;
                                case 1:
                                    # Numero de RUC
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            case 0:
                                                $response['SummaryDocuments']['cac:AccountingSupplierParty']['cbc:CustomerAssignedAccountID'] = $va;
                                                break;
                                            case 1:
                                                $response['SummaryDocuments']['cac:AccountingSupplierParty']['cbc:AdditionalAccountID'] = $va;
                                                break;
                                        }
                                    }
                                    break;
                                case 2:
                                    # Fecha de emision de los documentos
                                    $response['SummaryDocuments']['cbc:ReferenceDate'] = $v;
                                    break;
                                case 3:
                                    # Identificador del resumen
                                    $response['SummaryDocuments']['cbc:ID'] = $v;
                                    break;
                                case 4:
                                    # Fecha de generacion del resumen
                                    $response['SummaryDocuments']['cbc:IssueDate'] = $v;
                                    break;
                                case 5:
                                    #Firma Digital
                                    break;
                                case 6:
                                    # Version del UBL utilizado para establecer el formato XML
                                    $response['SummaryDocuments']['cbc:UBLVersionID'] = $v;
                                    break;
                                case 7:
                                    # Version de la estructura del documento
                                    $response['SummaryDocuments']['cbc:CustomizationID'] = $v;
                                    break;
                            }
                        }
                        break;
                    default:
                        $c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal'] = 0;
                        foreach ($line as $k => $v) {
                            switch ($k) {
                                case 0:
                                    # Tipo del document
                                    $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                        [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['cbc:DocumentTypeCode'] = $v;
                                    break;
                                case 1:
                                    # Numero de serie de los documentos
                                    $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                        [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['sac:DocumentSerialID'] = $v;
                                    break;
                                case 2:
                                    # Numero correlativo del documento de inicio dentro de la serie
                                    $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                        [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['sac:StartDocumentNumberID'] = $v;
                                    break;
                                case 3:
                                    # Numero correlativo del documento de inicio dentro de la serie
                                    $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                        [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['sac:EndDocumentNumberID'] = $v;
                                    break;
                                case 4:
                                    # Numero correlativo del documento de fin dentro de la serie
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            case 0:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                                    [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['sac:BillingPayment'][$billingPayment]['cbc:PaidAmount'] = $va;
                                                break;
                                            case 1:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                                    [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['sac:BillingPayment'][$billingPayment]['cbc:InstructionID'] = $va;
                                                break;
                                        }
                                    }
                                    $billingPayment++;
                                    break;
                                case 5:
                                    # Total valor de venta - operaciones gravadas
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            case 0:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                                    [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['sac:BillingPayment'][$billingPayment]['cbc:PaidAmount'] = $va;
                                                break;
                                            case 1:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                                    [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['sac:BillingPayment'][$billingPayment]['cbc:InstructionID'] = $va;
                                                break;
                                        }
                                    }
                                    $billingPayment++;
                                    break;
                                case 6:
                                    # Total valor de venta - operaciones exoneradas
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            case 0:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                                    [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['sac:BillingPayment'][$billingPayment]['cbc:PaidAmount'] = $va;
                                                break;
                                            case 1:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                                    [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['sac:BillingPayment'][$billingPayment]['cbc:InstructionID'] = $va;
                                                break;
                                        }
                                    }
                                    $billingPayment++;
                                    break;
                                case 7:
                                    # Importe total de sumatoria otros cargos del item
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            case 0:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                                    [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['cac:AllowanceCharge']['cbc:ChargeIndicator'] = $va;
                                                break;
                                            case 1:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine']
                                                    [$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['cac:AllowanceCharge']['cbc:Amount'] = $va;
                                                break;
                                        }
                                    }
                                    break;
                                case 8:
                                    # Total ISC
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            /* Monto de IGV de la linea */
                                            case 0:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cbc:TaxAmount'] = $va;
                                                break;
                                            /* Monto de IGV de la linea */
                                            case 1:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cbc:TaxAmount'] = $va;
                                                break;
                                            /* Codigo de tributo - Catalogo No 05 */
                                            case 2:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:ID'] = $va;
                                                break;
                                            /* Nombre de tributo - Catalogo No 05 */
                                            case 3:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:Name'] = utf8_encode($va);
                                                break;
                                            /* Codigo internacional tributo - Catalogo No 05 */
                                            case 4:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:TaxTypeCode'] = $va;
                                                break;
                                        }
                                    }
                                    $c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal'] ++;
                                    break;
                                case 9:
                                    # Total IGV
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            /* Monto de IGV de la linea */
                                            case 0:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cbc:TaxAmount'] = $va;
                                                break;
                                            /* Monto de IGV de la linea */
                                            case 1:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cbc:TaxAmount'] = $va;
                                                break;
                                            /* Codigo de tributo - Catalogo No 05 */
                                            case 2:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:ID'] = $va;
                                                break;
                                            /* Nombre de tributo - Catalogo No 05 */
                                            case 3:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:Name'] = utf8_encode($va);
                                                break;
                                            /* Codigo internacional tributo - Catalogo No 05 */
                                            case 4:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:TaxTypeCode'] = $va;
                                                break;
                                        }
                                    }
                                    $c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal'] ++;
                                    break;
                                case 10:
                                    # Total otros tributos
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            /* Monto de IGV de la linea */
                                            case 0:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cbc:TaxAmount'] = $va;
                                                break;
                                            /* Monto de IGV de la linea */
                                            case 1:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cbc:TaxAmount'] = $va;
                                                break;
                                            /* Codigo de tributo - Catalogo No 05 */
                                            case 2:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:ID'] = $va;
                                                break;
                                            /* Nombre de tributo - Catalogo No 05 */
                                            case 3:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:Name'] = utf8_encode($va);
                                                break;
                                            /* Codigo internacional tributo - Catalogo No 05 */
                                            case 4:
                                                $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]
                                                    ['cac:TaxTotal'][$c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:TaxTypeCode'] = $va;
                                                break;
                                        }
                                    }
                                    $c['SummaryDocuments']['sac:SummaryDocumentsLine']['cac:TaxTotal'] ++;
                                    break;
                                case 11:
                                    # Importe total de la venta, cesion en uso o del servicio prestado
                                    $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['sac:TotalAmount'] = $v;
                                    break;
                                case 12:
                                    # Numero de Fila
                                    $response['SummaryDocuments']['sac:SummaryDocumentsLine'][$count['SummaryDocuments']['sac:SummaryDocumentsLine']]['cbc:LineID'] = $v;
                                    break;
                            }
                        }
                        $count['SummaryDocuments']['sac:SummaryDocumentsLine'] ++;
                        break;
                }
            }
            $output['status'] = true;
            $output['document'] = $response;

            Log::info('Generación de array',
                [
                'lgph_id' => 1, 'c_id' => $response['SummaryDocuments']['cbc:ID'],
                'c_invoice_type_code' => 'RC',
                ]
            );
        } catch (Exception $exc) {
            Log::error($exc->getMessage(), ['lgph_id' => 1]);
            $output['message'] = $exc->getMessage();
        }
        return $output;
    }

    public static function validateFile($array)
    {
        if (isset($array['SummaryDocuments']['cac:AccountingSupplierParty']['cac:Party']['cac:PostalAddress'])) {
            $validation = array(
                'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:ID' => 'size:6',
                'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:StreetName' => 'required|max:100',
                'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:CitySubdivisionName' => 'max:25',
                'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:CityName' => 'max:30',
                'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:CountrySubentity' => 'max:30',
                'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:District' => 'max:30',
                'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cac:Country.cbc:IdentificationCode' => 'size:2',
            );
            $messages = array(
                'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:ID.size' => 'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:ID El código de UBIGEO no tiene 6 caracteres.',
                'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:StreetName.required' => 'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:StreetName La dirección completa y detallada es requerida.',
                'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:StreetName.max' => 'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:StreetName La dirección completa y detallada excedió los 100 caracteres.',
                'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:CitySubdivisionName.max' => 'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:CitySubdivisionName La ubicación o zona excedió los 25 caracteres.',
                'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:CityName.max' => 'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:CityName El departamento excedió los 30 caracteres.',
                'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:CountrySubentity.max' => 'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:CountrySubentity La provincia excedió los 30 caracteres.',
                'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:District.max' => 'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:District El distrito excedió los 30 caracteres.',
                'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cac:Country.cbc:IdentificationCode.size' => 'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cac:Country.cbc:IdentificationCode El código del pais no tiene 2 caracteres.',
            );
            $validator = Validator::make($array, $validation, $messages);
            if ($validator->fails()) {
                Log::error('Error en cabecera: ' . implode(',', $validator->messages()->all()),
                    ['lgph_id' => 2, 'c_id' => $array['SummaryDocuments']['cbc:ID'], 'c_invoice_type_code' => 'RC',]);
                throw new Exception(implode(',', $validator->messages()->all()));
            }
        }

        $validation = array(
            'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PartyLegalEntity.cbc:RegistrationName' => 'required|max:100',
            'SummaryDocuments.cac:AccountingSupplierParty.cbc:CustomerAssignedAccountID' => 'required|size:11',
            'SummaryDocuments.cac:AccountingSupplierParty.cbc:AdditionalAccountID' => 'required|size:1',
            'SummaryDocuments.cbc:ReferenceDate' => 'required|max:10',
            'SummaryDocuments.cbc:ID' => 'required|max:17',
            'SummaryDocuments.cbc:IssueDate' => 'required|max:10',
            'SummaryDocuments.cbc:UBLVersionID' => 'required|max:10',
            'SummaryDocuments.cbc:CustomizationID' => 'required|max:10',
        );

        $messages = array(
            'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PartyLegalEntity.cbc:RegistrationName.required' => 'Apellidos y nombres odenominación o razón social es requerido.',
            'SummaryDocuments.cac:AccountingSupplierParty.cac:Party.cac:PartyLegalEntity.cbc:RegistrationName.max' => 'Apellidos y nombres odenominación o razón social excedió los 100 caracteres.',
            'SummaryDocuments.cac:AccountingSupplierParty.cbc:CustomerAssignedAccountID.required' => 'Número de RUC es requerido.',
            'SummaryDocuments.cac:AccountingSupplierParty.cbc:CustomerAssignedAccountID.size' => 'Número de RUC no tiene 11 caractéres.',
            'SummaryDocuments.cac:AccountingSupplierParty.cbc:AdditionalAccountID.required' => 'Tipo de documento - Catálogo No. 06 es requerido.',
            'SummaryDocuments.cac:AccountingSupplierParty.cbc:AdditionalAccountID.size' => 'Tipo de documento - Catálogo No. 06 no tiene 1 caracter.',
            'SummaryDocuments.cbc:ReferenceDate.required' => 'Fecha de emisión de los documentos es requerido.',
            'SummaryDocuments.cbc:ReferenceDate.max' => 'Fecha de emisión de los documentos excedió los 10 caracteres.',
            'SummaryDocuments.cbc:ID.required' => 'Identificador del resumen es requerido.',
            'SummaryDocuments.cbc:ID.max' => 'Identificador del resumen excedió los 17 caracteres.',
            'SummaryDocuments.cbc:IssueDate.required' => 'Fecha de generación del resumen es requerido.',
            'SummaryDocuments.cbc:IssueDate.max' => 'Fecha de generación del resumen excedió los 10 caracteres.',
            'SummaryDocuments.cbc:UBLVersionID.required' => 'Versión del UBL utilizado para establecer el formato XML es requerido.',
            'SummaryDocuments.cbc:UBLVersionID.max' => 'Versión del UBL utilizado para establecer el formato XML excedió los 10 caracteres.',
            'SummaryDocuments.cbc:CustomizationID.required' => 'Versión de la estructura del documento es requerido.',
            'SummaryDocuments.cbc:CustomizationID.max' => 'Versión de la estructura del documento excedió los 10 caracteres.',
        );

        $validator = Validator::make($array, $validation, $messages);

        if ($validator->fails()) {
            Log::error('Error en cabecera: ' . implode(',', $validator->messages()->all()),
                ['lgph_id' => 2, 'c_id' => $array['SummaryDocuments']['cbc:ID'], 'c_invoice_type_code' => 'RC',]);
            throw new Exception(implode(',', $validator->messages()->all()));
        }

        $summaryDocumentsLine = 1;
        foreach ($array['SummaryDocuments']['sac:SummaryDocumentsLine'] as $key => $value) {
            $itemIndex = $key + 1;
            $validation = array(
                'cbc:DocumentTypeCode' => 'required|size:2',
                'sac:DocumentSerialID' => 'required|size:4',
                'sac:StartDocumentNumberID' => 'required|max:8',
                'cbc:CustomerAssignedAccountID' => 'required|max:8',
                'cac:AllowanceCharge.cbc:ChargeIndicator' => 'required|max:5',
                'cac:AllowanceCharge.cbc:Amount' => 'required|max:15',
                'sac:TotalAmount' => 'required|max:15',
                'cbc:LineID' => 'required|max:5',
            );

            $messages = array(
                'cbc:DocumentTypeCode.required' => sprintf('Ítem %s | Tipo de documento es requerido.',
                    $summaryDocumentsLine),
                'cbc:DocumentTypeCode.size' => sprintf('Ítem %s | Tipo de documento no tiene 2 caracteres.',
                    $summaryDocumentsLine),
                'sac:DocumentSerialID.required' => sprintf('Ítem %s | Número de serie de los documentos es requerido.',
                    $summaryDocumentsLine),
                'sac:DocumentSerialID.size' => sprintf('Ítem %s | Número de serie de los documentos no tiene 4 caracteres.',
                    $summaryDocumentsLine),
                'sac:StartDocumentNumberID.required' => sprintf('Ítem %s | Número correlativo del documento de inicio dentro de la serie es requerido.',
                    $summaryDocumentsLine),
                'sac:StartDocumentNumberID.max' => sprintf('Ítem %s | Número correlativo del documento de inicio dentro de la serie excedió los 8 caracteres.',
                    $summaryDocumentsLine),
                'cbc:CustomerAssignedAccountID.required' => sprintf('Ítem %s | Numero de documento de identidad es requerido.',
                    $summaryDocumentsLine),
                'cbc:CustomerAssignedAccountID.max' => sprintf('Ítem %s | Número del documento excedió los 8 caracteres.',
                    $summaryDocumentsLine),
                'cac:AllowanceCharge.cbc:ChargeIndicator.required' => sprintf('Ítem %s | Importe total de sumatoria otros cargos del item, indicador de cargo es requerido.',
                    $summaryDocumentsLine),
                'cac:AllowanceCharge.cbc:ChargeIndicator.max' => sprintf('Ítem %s | Importe total de sumatoria otros cargos del item, indicador de cargo excedió los 5 caracteres.',
                    $summaryDocumentsLine),
                'cac:AllowanceCharge.cbc:Amount.required' => sprintf('Ítem %s | Importe total de sumatoria otros cargos del item, monto de otros cargos es requerido.',
                    $summaryDocumentsLine),
                'cac:AllowanceCharge.cbc:Amount.max' => sprintf('Ítem %s | Importe total de sumatoria otros cargos del item, monto de otros cargos excedió los 15 caracteres.',
                    $summaryDocumentsLine),
                'sac:TotalAmount.required' => sprintf('Ítem %s | Importe total de la venta, cesión en uso o del servicio prestado es requerido.',
                    $summaryDocumentsLine),
                'sac:TotalAmount.max' => sprintf('Ítem %s | Importe total de la venta, cesión en uso o del servicio prestado excedió los 15 caracteres.',
                    $summaryDocumentsLine),
                'cbc:LineID.required' => sprintf('Ítem %s | Número de fila es requerido.', $summaryDocumentsLine),
                'cbc:LineID.max' => sprintf('Ítem %s | Número de fila excedió los 5 caracteres.', $summaryDocumentsLine),
            );
            $validator = Validator::make($value, $validation, $messages);
            if ($validator->fails()) {
                Log::error("Error en detalle, Ítem #{$itemIndex}: " . implode(',', $validator->messages()->all()),
                    ['lgph_id' => 2, 'c_id' => $array['SummaryDocuments']['cbc:ID'], 'c_invoice_type_code' => 'RC',]);
                throw new Exception(implode(',', $validator->messages()->all()));
            }

            foreach ($value['sac:BillingPayment'] as $k => $v) {
                $prepend = '- ';
                switch ($k) {
                    case 0:
                        $prepend .= 'operaciones gravadas';
                        break;
                    case 1:
                        $prepend .= 'operaciones exoneradas';
                        break;
                    case 2:
                        $prepend .= 'operaciones inafectas';
                        break;
                }
                $validation = array(
                    'cbc:PaidAmount' => 'required|max:15',
                    'cbc:InstructionID' => 'required|size:2',
                );
                $messages = array(
                    'cbc:PaidAmount.required' => sprintf('Ítem %s | Total valor de venta %s, Monto es requerido.',
                        $summaryDocumentsLine, $prepend),
                    'cbc:PaidAmount.max' => sprintf('Ítem %s | Total valor de venta %s, Monto no puede exceder los 15 caracteres.',
                        $summaryDocumentsLine, $prepend),
                    'cbc:InstructionID.required' => sprintf('Ítem %s | Total valor de venta %s, Código de tipo de valor de venta - Catálogo No 11 es requerido.',
                        $summaryDocumentsLine, $prepend),
                    'cbc:InstructionID.size' => sprintf('Ítem %s | Total valor de venta %s, Código de tipo de valor de venta - Catálogo No 11 no tiene 2 caracteres.',
                        $summaryDocumentsLine, $prepend),
                );
                $validator = Validator::make($v, $validation, $messages);
                if ($validator->fails()) {
                    Log::error("Error en detalle, Ítem #{$itemIndex}: " . implode(',', $validator->messages()->all()),
                        ['lgph_id' => 2, 'c_id' => $array['SummaryDocuments']['cbc:ID'], 'c_invoice_type_code' => 'RC',]);
                    throw new Exception(implode(',', $validator->messages()->all()));
                }
            }

            foreach ($value['cac:TaxTotal'] as $k => $v) {
                $required = '|required';
                $description = '';
                switch ($k) {
                    case 0:
                        $description = 'Total ISC';
                        $messages['cbc:TaxAmount.required'] = sprintf('Ítem %s | %s, Monto Total ISC del item es requerido.',
                            $summaryDocumentsLine, $description);
                        $messages['cac:TaxSubtotal.cbc:TaxAmount.required'] = sprintf('Ítem %s | %s, Monto Total ISC del item es requerido.',
                            $summaryDocumentsLine, $description);
                        $messages['cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:ID.required'] = sprintf('Ítem %s | %s, Código de tributo - Catálogo No. 05 es requerido.',
                            $summaryDocumentsLine, $description);
                        $messages['cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:Name.required'] = sprintf('Ítem %s | %s, Nombre de tributo - Catálogo No. 05 es requerido.',
                            $summaryDocumentsLine, $description);
                        $messages['cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:TaxTypeCode.required'] = sprintf('Ítem %s | %s, Código internacional tributo - es requerido.',
                            $summaryDocumentsLine, $description);
                        break;
                    case 1:
                        $description = 'Total IGV';
                        $messages['cbc:TaxAmount.required'] = sprintf('Ítem %s | %s, Monto Total ISC del item es requerido.',
                            $summaryDocumentsLine, $description);
                        $messages['cac:TaxSubtotal.cbc:TaxAmount.required'] = sprintf('Ítem %s | %s, Monto Total ISC del item es requerido.',
                            $summaryDocumentsLine, $description);
                        $messages['cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:ID.required'] = sprintf('Ítem %s | %s, Código de tributo - Catálogo No. 05 es requerido.',
                            $summaryDocumentsLine, $description);
                        $messages['cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:Name.required'] = sprintf('Ítem %s | %s, Nombre de tributo - Catálogo No. 05 es requerido.',
                            $summaryDocumentsLine, $description);
                        $messages['cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:TaxTypeCode.required'] = sprintf('Ítem %s | %s, Código internacional tributo - es requerido.',
                            $summaryDocumentsLine, $description);
                        break;
                    case 2:
                        $description = 'Total Otros tributos';
                        $required = '';
                        break;
                }
                $validation = array(
                    'cbc:TaxAmount' => sprintf('max:15%s', $required),
                    'cac:TaxSubtotal.cbc:TaxAmount' => sprintf('max:15%s', $required),
                    'cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:ID' => sprintf('size:4%s', $required),
                    'cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:Name' => sprintf('max:10%s', $required),
                    'cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:TaxTypeCode' => sprintf('size:3%s', $required),
                );

                $messages = array(
                    'cbc:TaxAmount.max' => sprintf('Ítem %s | %s, Monto Total ISC del item no puede exceder los 15 caracteres.',
                        $summaryDocumentsLine, $description),
                    'cac:TaxSubtotal.cbc:TaxAmount.max' => sprintf('Ítem %s | %s, Monto Total ISC del item no puede exceder los 15 caracteres.',
                        $summaryDocumentsLine, $description),
                    'cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:ID.size' => sprintf('Ítem %s | %s, Código de tributo - Catálogo No. 05 no tiene 4 caracteres.',
                        $summaryDocumentsLine, $description),
                    'cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:Name.max' => sprintf('Ítem %s | %s, Nombre de tributo - Catálogo No. 05 no puede exceder más de 10 caracteres.',
                        $summaryDocumentsLine, $description),
                    'cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:TaxTypeCode.size' => sprintf('Ítem %s | %s, Código internacional tributo - Catálogo No. 05 no tiene 3 caracteres.',
                        $summaryDocumentsLine, $description),
                );
                $validator = Validator::make($v, $validation, $messages);
                if ($validator->fails()) {
                    Log::error("Error en detalle, Ítem #{$itemIndex}: " . implode(',', $validator->messages()->all()),
                        ['lgph_id' => 2, 'c_id' => $array['SummaryDocuments']['cbc:ID'], 'c_invoice_type_code' => 'RC',]);
                    throw new Exception(implode(',', $validator->messages()->all()));
                }
            }
            $summaryDocumentsLine++;
        }

        Log::info('Validación de array',
            [
            'lgph_id' => 2, 'c_id' => $array['SummaryDocuments']['cbc:ID'],
            'c_invoice_type_code' => 'RC',
            ]
        );
    }

    public static function setDb($array)
    {
        $response['status'] = 0;
        $response['id'] = 0;
        $response['message'] = '';

        try {
            $inSupplier = $array['SummaryDocuments']['cac:AccountingSupplierParty'];
            $supplier = SupSupplier::where('c_customer_assigned_account_id',
                    $inSupplier['cbc:CustomerAssignedAccountID'])
                ->where('c_additional_account_id', $inSupplier['cbc:AdditionalAccountID'])
                ->where('c_status_supplier', 'visible');

            if ($supplier->count() == 0) {
                throw new Exception('Emisor no registrado');
            }

            $supplier = $supplier->first();

            $supplier->c_additional_account_id = $inSupplier['cbc:AdditionalAccountID'];
            $supplier->c_party_party_legal_entity_registration_name = $inSupplier['cac:Party']['cac:PartyLegalEntity']
                ['cbc:RegistrationName'];

            $supplier->c_party_name_name = (isset($inSupplier['cac:Party']['cac:PartyName']['cbc:Name'])) ?
                $inSupplier['cac:Party']['cac:PartyName']['cbc:Name'] : NULL;

            if (isset($inSupplier['cac:Party']['cac:PostalAddress']['cbc:ID']) && !empty($inSupplier['cac:Party']['cac:PostalAddress']['cbc:ID'])) {
                $supplier->c_party_postal_address_id = $inSupplier['cac:Party']['cac:PostalAddress']['cbc:ID'];
                $supplier->c_party_postal_address_street_name = $inSupplier['cac:Party']['cac:PostalAddress']
                    ['cbc:StreetName'];
                $supplier->c_party_postal_address_city_subdivision_name = $inSupplier['cac:Party']['cac:PostalAddress']
                    ['cbc:CitySubdivisionName'];
                $supplier->c_party_postal_address_city_name = $inSupplier['cac:Party']['cac:PostalAddress']
                    ['cbc:CityName'];
                $supplier->c_party_postal_address_country_subentity = $inSupplier['cac:Party']['cac:PostalAddress']
                    ['cbc:CountrySubentity'];
                $supplier->c_party_postal_address_district = $inSupplier['cac:Party']['cac:PostalAddress']
                    ['cbc:District'];
                $supplier->c_party_postal_address_country_identification_code = $inSupplier['cac:Party']
                    ['cac:PostalAddress']['cac:Country']['cbc:IdentificationCode'];
            }
            $supplier->save();
            $supplierId = $supplier->n_id_supplier;

            #RESUMEN DE BOLETAS
            $inSummaryDocuments = $array['SummaryDocuments'];
            $summaryDocuments = DocInvoice::where('c_id', $inSummaryDocuments['cbc:ID'])->where('n_id_supplier',
                        $supplierId)
                    ->where('c_invoice_type_code', 'RC')->whereIn('c_status_invoice', array('visible', 'hidden'));
            if ($summaryDocuments->count()) {
                $summaryDocuments->update(array('c_status_invoice' => 'deleted'));
            }

            $summaryDocuments = new DocInvoice();
            $summaryDocuments->n_id_supplier = $supplierId;
            $summaryDocuments->d_reference_date = $inSummaryDocuments['cbc:ReferenceDate'];
            $summaryDocuments->c_id = $inSummaryDocuments['cbc:ID'];
            $cIdExplode = explode('-', $inSummaryDocuments['cbc:ID']);
            $summaryDocuments->c_correlative = end($cIdExplode);
            $summaryDocuments->n_correlative = (int) end($cIdExplode);
            $summaryDocuments->d_issue_date = $inSummaryDocuments['cbc:IssueDate'];
            $summaryDocuments->c_ubl_version_id = $inSummaryDocuments['cbc:UBLVersionID'];
            $summaryDocuments->c_customization_id = $inSummaryDocuments['cbc:CustomizationID'];
            $summaryDocuments->c_note = $inSummaryDocuments['cbc:Note'];            
            $summaryDocuments->c_invoice_type_code = 'RC'; # Resumen diario
            $summaryDocuments->c_document_currency_code = 'PEN'; # Todos los resumenes son en SOLES.
            $summaryDocuments->c_status_invoice = 'visible';
            $summaryDocuments->save();
            $summaryDocumentsId = $summaryDocuments->n_id_invoice;
            $docInvoiceCdrStatus = new DocInvoiceCdrStatus();
            $docInvoiceCdrStatus->n_id_invoice = $summaryDocumentsId;
            $docInvoiceCdrStatus->n_id_cdr_status = 4;
            $docInvoiceCdrStatus->save();

            # DocInvoiceSupplier
            $summaryDocumentsSupplier = new DocInvoiceSupplier();
            $summaryDocumentsSupplier->n_id_invoice = $summaryDocumentsId;
            $summaryDocumentsSupplier->n_id_supplier = $supplierId;
            $summaryDocumentsSupplier->c_customer_assigned_account_id = $inSupplier['cbc:CustomerAssignedAccountID'];
            $summaryDocumentsSupplier->c_additional_account_id = $inSupplier['cbc:AdditionalAccountID'];
            $summaryDocumentsSupplier->c_party_party_legal_entity_registration_name = $inSupplier['cac:Party']['cac:PartyLegalEntity']
                ['cbc:RegistrationName'];

            $summaryDocumentsSupplier->c_party_name_name = (isset($inSupplier['cac:Party']['cac:PartyName']['cbc:Name'])) ?
                $inSupplier['cac:Party']['cac:PartyName']['cbc:Name'] : NULL;

            if (isset($inSupplier['cac:Party']['cac:PostalAddress']['cbc:ID']) && !empty($inSupplier['cac:Party']['cac:PostalAddress']['cbc:ID'])) {
                $summaryDocumentsSupplier->c_party_postal_address_id = $inSupplier['cac:Party']['cac:PostalAddress']['cbc:ID'];
                $summaryDocumentsSupplier->c_party_postal_address_street_name = $inSupplier['cac:Party']['cac:PostalAddress']
                    ['cbc:StreetName'];
                $summaryDocumentsSupplier->c_party_postal_address_city_subdivision_name = $inSupplier['cac:Party']['cac:PostalAddress']
                    ['cbc:CitySubdivisionName'];
                $summaryDocumentsSupplier->c_party_postal_address_city_name = $inSupplier['cac:Party']['cac:PostalAddress']
                    ['cbc:CityName'];
                $summaryDocumentsSupplier->c_party_postal_address_country_subentity = $inSupplier['cac:Party']['cac:PostalAddress']
                    ['cbc:CountrySubentity'];
                $summaryDocumentsSupplier->c_party_postal_address_district = $inSupplier['cac:Party']['cac:PostalAddress']
                    ['cbc:District'];
                $summaryDocumentsSupplier->c_party_postal_address_country_identification_code = $inSupplier['cac:Party']
                    ['cac:PostalAddress']['cac:Country']['cbc:IdentificationCode'];
            }
            $summaryDocumentsSupplier->save();

            # DocInvoiceItem
            $inSummaryDocumentsLine = $inSummaryDocuments['sac:SummaryDocumentsLine'];
            foreach ($inSummaryDocumentsLine as $value) {
                $summaryDocumentsItem = new DocInvoiceItem();
                $summaryDocumentsItem->n_id_invoice = $summaryDocumentsId;
                $summaryDocumentsItem->c_document_type_code = $value['cbc:DocumentTypeCode'];
                $summaryDocumentsItem->c_document_serial_id = $value['sac:DocumentSerialID'];
                $summaryDocumentsItem->c_start_document_number_id = $value['sac:StartDocumentNumberID'];

                //1.1
                $summaryDocumentsItem->c_customer_assigned_account_id = $value['cbc:CustomerAssignedAccountID'];                
                //$summaryDocumentsItem->c_end_document_number_id = $value['sac:EndDocumentNumberID'];
                
                $summaryDocumentsItem->c_total_amount = $value['sac:TotalAmount'];
                $summaryDocumentsItem->n_id = $value['cbc:LineID'];
                $summaryDocumentsItem->save();
                $summaryDocumentsItemId = $summaryDocumentsItem->n_id_invoice_item;

                $summaryDocumentsItemAllowancecharge = new DocInvoiceItemAllowancecharge();
                $summaryDocumentsItemAllowancecharge->n_id_invoice_item = $summaryDocumentsItemId;
                $summaryDocumentsItemAllowancecharge->c_charge_indicator = $value['cac:AllowanceCharge']['cbc:ChargeIndicator'];
                $summaryDocumentsItemAllowancecharge->c_amount = $value['cac:AllowanceCharge']['cbc:Amount'];
                $summaryDocumentsItemAllowancecharge->save();

                foreach ($value['sac:BillingPayment'] as $v) {
                    $summaryDocumentsItemBillingPayment = new DocInvoiceItemBillingPayment();
                    $summaryDocumentsItemBillingPayment->n_id_invoice_item = $summaryDocumentsItemId;
                    $summaryDocumentsItemBillingPayment->c_paid_amount = $v['cbc:PaidAmount'];
                    $summaryDocumentsItemBillingPayment->c_instruction_id = $v['cbc:InstructionID'];
                    $summaryDocumentsItemBillingPayment->save();
                }

                foreach ($value['cac:TaxTotal'] as $v) {
                    $docInvoiceItemTaxTotal = new DocInvoiceItemTaxTotal();
                    $docInvoiceItemTaxTotal->n_id_invoice_item = $summaryDocumentsItemId;
                    $docInvoiceItemTaxTotal->c_tax_amount = $v['cbc:TaxAmount'];
                    $docInvoiceItemTaxTotal->save();
                    $docInvoiceItemTaxTotalId = $docInvoiceItemTaxTotal->n_id_invoice_item_tax_total;

                    $docInvoiceItemTaxTotalTaxSubtotal = new DocInvoiceItemTaxTotalTaxSubtotal();
                    $docInvoiceItemTaxTotalTaxSubtotal->n_id_invoice_item_tax_total = $docInvoiceItemTaxTotalId;
                    $docInvoiceItemTaxTotalTaxSubtotal->c_tax_amount = $v['cac:TaxSubtotal']['cbc:TaxAmount'];
                    $docInvoiceItemTaxTotalTaxSubtotal->save();

                    $docInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme = new
                        DocInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme();
                    $docInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme->n_id_invoice_item_tax_total = $docInvoiceItemTaxTotalId;
                    $docInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme->c_id = $v['cac:TaxSubtotal']['cac:TaxCategory']
                        ['cac:TaxScheme']['cbc:ID'];
                    $docInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme->c_name = $v['cac:TaxSubtotal']
                        ['cac:TaxCategory']['cac:TaxScheme']['cbc:Name'];
                    $docInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme->c_tax_type_code = $v['cac:TaxSubtotal']
                        ['cac:TaxCategory']['cac:TaxScheme']['cbc:TaxTypeCode'];
                    $docInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme->save();
                }
            }

            # Ticket
            $docInvoiceTicket = new DocInvoiceTicket();
            $docInvoiceTicket->n_id_invoice = $summaryDocumentsId;
            $docInvoiceTicket->c_has_ticket = 'no';
            $docInvoiceTicket->save();

            $response['id'] = $summaryDocumentsId;
            $response['status'] = 1;

            Log::info('Grabado en BD',
                [
                'lgph_id' => 3, 'c_id' => $array['SummaryDocuments']['cbc:ID'], 'c_invoice_type_code' => 'RC',
                'n_id_invoice' => $summaryDocumentsId,
                ]
            );
        } catch (Exception $exc) {
            Log::error($exc->getMessage(),
                [
                'lgph_id' => 3, 'c_id' => $array['SummaryDocuments']['cbc:ID'], 'c_invoice_type_code' => 'RC',
                ]
            );
            $response['message'] = $exc->getMessage();
        }

        return $response;
    }

    public static function buildXml($summaryDocumentsId)
    {
        $response['status'] = 0;
        $response['message'] = '';
        $response['path'] = '';

        try {
            $summaryDocumentsData = DocInvoice::with('SupSupplier.SupSupplierConfiguration')->where('n_id_invoice',
                $summaryDocumentsId);

            if ($summaryDocumentsData->count() == 0) {
                throw new Exception('No se encuentra en BD');
            }

            $summaryDocumentsData = $summaryDocumentsData->first();
            $signatureId = 'S' . $summaryDocumentsData->c_id;
            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->xmlStandalone = false;
            $dom->formatOutput = true;

            $summaryDocuments = $dom->createElement('SummaryDocuments');
            $newNode = $dom->appendChild($summaryDocuments);
            $newNode->setAttribute('xmlns', 'urn:sunat:names:specification:ubl:peru:schema:xsd:SummaryDocuments-1');
            $newNode->setAttribute('xmlns:cac',
                'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $newNode->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $newNode->setAttribute('xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');
            $newNode->setAttribute('xmlns:ext',
                'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
            $newNode->setAttribute('xmlns:sac',
                'urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1');
            $newNode->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

            $ublExtensions = $dom->createElement('ext:UBLExtensions');
            $summaryDocuments->appendChild($ublExtensions);

            # Firma Digital
            $ublExtension = $dom->createElement('ext:UBLExtension');
            $ublExtensions->appendChild($ublExtension);
            $extensionContent = $dom->createElement('ext:ExtensionContent');
            $ublExtension->appendChild($extensionContent);

            if(env('SYSTEM')=='linux'){                        
                $signature = $dom->createElement('ds:Signature');
                $id = $dom->createAttribute('Id');
                $id->value = '#' . $signatureId;
                $signature->appendChild($id);
                $extensionContent->appendChild($signature);
                $signedInfo = $dom->createElement('ds:SignedInfo');
                $signature->appendChild($signedInfo);
                $canonicalizationMethod = $dom->createElement('ds:CanonicalizationMethod');
                $algorithm = $dom->createAttribute('Algorithm');
                $algorithm->value = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
                $canonicalizationMethod->appendChild($algorithm);
                $signedInfo->appendChild($canonicalizationMethod);
                $signatureMethod = $dom->createElement('ds:SignatureMethod');
                $algorithm = $dom->createAttribute('Algorithm');
                $algorithm->value = 'http://www.w3.org/2000/09/xmldsig#rsa-sha1';
                $signatureMethod->appendChild($algorithm);
                $signedInfo->appendChild($signatureMethod);
                $reference = $dom->createElement('ds:Reference');
                $uri = $dom->createAttribute('URI');
                $uri->value = '';
                $reference->appendChild($uri);
                $signedInfo->appendChild($reference);
                $transforms = $dom->createElement('ds:Transforms');
                $transform = $dom->createElement('ds:Transform');
                $algorithm = $dom->createAttribute('Algorithm');
                $algorithm->value = 'http://www.w3.org/2000/09/xmldsig#enveloped-signature';
                $transform->appendChild($algorithm);
                $transforms->appendChild($transform);
                $reference->appendChild($transforms);
                $digestMethod = $dom->createElement('ds:DigestMethod');
                $algorithm = $dom->createAttribute('Algorithm');
                $algorithm->value = 'http://www.w3.org/2000/09/xmldsig#sha1';
                $digestMethod->appendChild($algorithm);
                $reference->appendChild($digestMethod);
                $demo = (!env('XMLDSIG')) ? 'DEMO' : NULL;
                $digestValue = $dom->createElement('ds:DigestValue', $demo);
                $reference->appendChild($digestValue);
                $signatureValue = $dom->createElement('ds:SignatureValue', $demo);
                $signature->appendChild($signatureValue);
                $keyInfo = $dom->createElement('ds:KeyInfo');
                $signature->appendChild($keyInfo);
                $x509Data = $dom->createElement('ds:X509Data');
                $keyInfo->appendChild($x509Data);
                $x509Certificate = $dom->createElement('ds:X509Certificate', $demo);
                $x509Data->appendChild($x509Certificate);
            }

            $ublVersionID = $dom->createElement('cbc:UBLVersionID', $summaryDocumentsData->c_ubl_version_id);
            $summaryDocuments->appendChild($ublVersionID);
            $customizationID = $dom->createElement('cbc:CustomizationID', $summaryDocumentsData->c_customization_id);
            $summaryDocuments->appendChild($customizationID);

            $id = $dom->createElement('cbc:ID', $summaryDocumentsData->c_id);
            $summaryDocuments->appendChild($id);
            $referenceDate = $dom->createElement('cbc:ReferenceDate', $summaryDocumentsData->d_reference_date);
            $summaryDocuments->appendChild($referenceDate);
            $issueDate = $dom->createElement('cbc:IssueDate', $summaryDocumentsData->d_issue_date);
            $summaryDocuments->appendChild($issueDate);

            //1.1
            $note = $dom->createElement('cbc:Note', $summaryDocumentsData->c_note);
            $summaryDocuments->appendChild($note);            

            # Firma
            $signature = $dom->createElement('cac:Signature');
            $summaryDocuments->appendChild($signature);
            $id = $dom->createElement('cbc:ID', $signatureId);
            $signature->appendChild($id);
            $signatoryParty = $dom->createElement('cac:SignatoryParty');
            $signature->appendChild($signatoryParty);
            $partyIdentification = $dom->createElement('cac:PartyIdentification');
            $signatoryParty->appendChild($partyIdentification);
            $id = $dom->createElement('cbc:ID',
                $summaryDocumentsData->DocInvoiceSupplier->c_customer_assigned_account_id);
            $partyIdentification->appendChild($id);
            $partyName = $dom->createElement('cac:PartyName');
            $signatoryParty->appendChild($partyName);
            $name = $dom->createElement('cbc:Name');
            $name->appendChild($dom->createCDATASection('SUNAT'));
            $partyName->appendChild($name);
            $digitalSignatureAttachment = $dom->createElement('cac:DigitalSignatureAttachment');
            $signature->appendChild($digitalSignatureAttachment);
            $externalReference = $dom->createElement('cac:ExternalReference');
            $digitalSignatureAttachment->appendChild($externalReference);
            $uri = $dom->createElement('cbc:URI', '#' . $signatureId);
            $externalReference->appendChild($uri);

            # DocInvoiceSupplier
            $invoiceSupplierData = $summaryDocumentsData->SupSupplier;
            $accountingSupplierParty = $dom->createElement('cac:AccountingSupplierParty');
            $summaryDocuments->appendChild($accountingSupplierParty);
            $customerAssignedAccountId = $dom->createElement('cbc:CustomerAssignedAccountID',
                $invoiceSupplierData
                ->c_customer_assigned_account_id);
            $accountingSupplierParty->appendChild($customerAssignedAccountId);
            $additionalAccountId = $dom->createElement('cbc:AdditionalAccountID',
                $invoiceSupplierData
                ->c_additional_account_id);
            $accountingSupplierParty->appendChild($additionalAccountId);
            $party = $dom->createElement('cac:Party');
            $accountingSupplierParty->appendChild($party);

            if (isset($invoiceSupplierData->c_party_name_name) && !empty($invoiceSupplierData->c_party_name_name)) {
                $partyName = $dom->createElement('cac:PartyName');
                $party->appendChild($partyName);
                $name = $dom->createElement('cbc:Name');
                $name->appendChild($dom->createCDATASection($invoiceSupplierData->c_party_name_name));
                $partyName->appendChild($name);
            }

            //Remove for 1.1
            /*
            if (isset($invoiceSupplierData->c_party_postal_address_id) &&
                !empty($invoiceSupplierData->c_party_postal_address_id)) {
                $postalAddress = $dom->createElement('cac:PostalAddress');
                $party->appendChild($postalAddress);
                $id = $dom->createElement('cbc:ID');
                $id->nodeValue = $invoiceSupplierData->c_party_postal_address_id;
                $postalAddress->appendChild($id);
                $streetName = $dom->createElement('cbc:StreetName');
                $streetName->appendChild($dom->createCDATASection(
                        $invoiceSupplierData->c_party_postal_address_street_name));
                $postalAddress->appendChild($streetName);
                $citySubdivisionName = $dom->createElement('cbc:CitySubdivisionName');
                $citySubdivisionName->appendChild($dom->createCDATASection(
                        $invoiceSupplierData->c_party_postal_address_city_subdivision_name));
                $postalAddress->appendChild($citySubdivisionName);
                $cityName = $dom->createElement('cbc:CityName');
                $cityName->appendChild($dom->createCDATASection($invoiceSupplierData->c_party_postal_address_city_name));
                $postalAddress->appendChild($cityName);
                $countrySubentity = $dom->createElement('cbc:CountrySubentity');
                $countrySubentity->appendChild($dom->createCDATASection($invoiceSupplierData
                        ->c_party_postal_address_country_subentity));
                $postalAddress->appendChild($countrySubentity);
                $district = $dom->createElement('cbc:District');
                $district->appendChild($dom->createCDATASection($invoiceSupplierData->c_party_postal_address_district));
                $postalAddress->appendChild($district);
                $country = $dom->createElement('cac:Country');
                $postalAddress->appendChild($country);
                $identificationCode = $dom->createElement('cbc:IdentificationCode');
                $identificationCode->appendChild($dom->createCDATASection(
                        $invoiceSupplierData->c_party_postal_address_country_identification_code));
                $country->appendChild($identificationCode);
            }*/


            $partyLegalEntity = $dom->createElement('cac:PartyLegalEntity');
            $party->appendChild($partyLegalEntity);
            $registrationName = $dom->createElement('cbc:RegistrationName');
            $registrationName->appendChild($dom->createCDATASection(
                    $invoiceSupplierData->c_party_party_legal_entity_registration_name));
            $partyLegalEntity->appendChild($registrationName);

            # DocInvoiceItem
            $invoiceItemData = $summaryDocumentsData->DocInvoiceItem;
            foreach ($invoiceItemData as $value) {
                $summaryDocumentsLine = $dom->createElement('sac:SummaryDocumentsLine');
                $summaryDocuments->appendChild($summaryDocumentsLine);

                $lineID = $dom->createElement('cbc:LineID', $value->n_id);
                $summaryDocumentsLine->appendChild($lineID);
                $documentTypeCode = $dom->createElement('cbc:DocumentTypeCode', $value->c_document_type_code);
                $summaryDocumentsLine->appendChild($documentTypeCode);

                // For 1.1
                $id = $dom->createElement('cbc:ID', $value->c_document_serial_id . '-' .$value->c_start_document_number_id);
                $summaryDocumentsLine->appendChild($id);

                $accountingCustomerParty = $dom->createElement('cac:AccountingCustomerParty');            

                $customerAssignedAccountID = $dom->createElement('cbc:CustomerAssignedAccountID',$value->c_customer_assigned_account_id);            
                $accountingCustomerParty->appendChild($customerAssignedAccountID);

                $additionalAccountID = $dom->createElement('cbc:AdditionalAccountID','1');            
                $accountingCustomerParty->appendChild($additionalAccountID);

                $summaryDocumentsLine->appendChild($accountingCustomerParty);


                $status = $dom->createElement('cac:Status');                
                $conditionCode = $dom->createElement('cbc:ConditionCode','1');                
                $status->appendChild($conditionCode);
                $summaryDocumentsLine->appendChild($status);

                /* Remove 1.1
                $documentSerialID = $dom->createElement('sac:DocumentSerialID', $value->c_document_serial_id);
                $summaryDocumentsLine->appendChild($documentSerialID);

                $startDocumentNumberID = $dom->createElement('sac:StartDocumentNumberID',
                    $value->c_start_document_number_id);
                $summaryDocumentsLine->appendChild($startDocumentNumberID);

                $endDocumentNumberID = $dom->createElement('sac:EndDocumentNumberID', $value->c_end_document_number_id);
                $summaryDocumentsLine->appendChild($endDocumentNumberID);
                */

                $totalAmount = $dom->createElement('sac:TotalAmount', $value->c_total_amount);
                $currencyId = $dom->createAttribute('currencyID');
                $currencyId->nodeValue = $summaryDocumentsData->c_document_currency_code;
                $totalAmount->appendChild($currencyId);
                $summaryDocumentsLine->appendChild($totalAmount);

                foreach ($value->DocInvoiceItemBillingPayment as $v) {
                    $billingPayment = $dom->createElement('sac:BillingPayment');
                    $paidAmount = $dom->createElement('cbc:PaidAmount', $v->c_paid_amount);
                    $currencyId = $dom->createAttribute('currencyID');
                    $currencyId->nodeValue = $summaryDocumentsData->c_document_currency_code;
                    $paidAmount->appendChild($currencyId);
                    $billingPayment->appendChild($paidAmount);
                    $instructionID = $dom->createElement('cbc:InstructionID', $v->c_instruction_id);
                    $billingPayment->appendChild($instructionID);
                    $summaryDocumentsLine->appendChild($billingPayment);
                }

                $docInvoiceItemAllowancechargeData = $value->DocInvoiceItemAllowancecharge;
                $allowanceCharge = $dom->createElement('cac:AllowanceCharge');
                $chargeIndicator = $dom->createElement('cbc:ChargeIndicator',
                    $docInvoiceItemAllowancechargeData
                    ->c_charge_indicator);
                $amount = $dom->createElement('cbc:Amount',
                    $docInvoiceItemAllowancechargeData
                    ->c_amount);
                $currencyId = $dom->createAttribute('currencyID');
                $currencyId->value = $summaryDocumentsData->c_document_currency_code;
                $amount->appendChild($currencyId);
                $summaryDocumentsLine->appendChild($allowanceCharge);
                $allowanceCharge->appendChild($chargeIndicator);
                $allowanceCharge->appendChild($amount);

                # DocInvoiceItemTaxTotalData
                $docInvoiceItemTaxTotalData = $value->DocInvoiceItemTaxTotal;
                foreach ($docInvoiceItemTaxTotalData as $k => $v) {
                    $taxTotal = $dom->createElement('cac:TaxTotal');
                    $summaryDocumentsLine->appendChild($taxTotal);
                    $taxAmount = $dom->createElement('cbc:TaxAmount', $v->c_tax_amount);
                    $currencyId = $dom->createAttribute('currencyID');
                    $currencyId->nodeValue = $summaryDocumentsData->c_document_currency_code;
                    $taxAmount->appendChild($currencyId);
                    $taxTotal->appendChild($taxAmount);

                    $taxSubtotal = $dom->createElement('cac:TaxSubtotal');
                    $taxTotal->appendChild($taxSubtotal);
                    $docInvoiceItemTaxTotalTaxSubtotal = $v->DocInvoiceItemTaxTotalTaxSubtotal;
                    $taxAmount = $dom->createElement('cbc:TaxAmount', $docInvoiceItemTaxTotalTaxSubtotal->c_tax_amount);
                    $currencyId = $dom->createAttribute('currencyID');
                    $currencyId->nodeValue = $summaryDocumentsData->c_document_currency_code;
                    $taxAmount->appendChild($currencyId);
                    $taxSubtotal->appendChild($taxAmount);

                    $taxCategory = $dom->createElement('cac:TaxCategory');
                    $taxSubtotal->appendChild($taxCategory);

                    $taxScheme = $dom->createElement('cac:TaxScheme');
                    $taxCategory->appendChild($taxScheme);

                    $docInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme = $v
                        ->DocInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme;

                    $id = $dom->createElement('cbc:ID', $docInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme->c_id);
                    $taxScheme->appendChild($id);
                    $name = $dom->createElement('cbc:Name',
                        $docInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme->c_name);
                    $taxScheme->appendChild($name);

                    if (isset($docInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme->c_tax_type_code) &&
                        !empty($docInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme->c_tax_type_code)) {
                        $taxTypeCode = $dom->createElement('cbc:TaxTypeCode',
                            $docInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme->c_tax_type_code);
                        $taxScheme->appendChild($taxTypeCode);
                    }
                }
            }

            $xmlName = sprintf('%s-%s', $invoiceSupplierData->c_customer_assigned_account_id,
                $summaryDocumentsData->c_id);
            $xmlPath = 'sunat/tmp/' . $xmlName . '.XML';
            $xmlFullPath = storage_path($xmlPath);
            file_exists($xmlFullPath) ? unlink($xmlFullPath) : '';
            \File::put($xmlFullPath, $dom->saveXML());
            chmod($xmlFullPath, 0777);

            # Proceso de Firmado
            if (env('XMLDSIG')) {


                # Proceso de Firmado (XMLSEC)
                if(env('SYSTEM')=='linux'){

                    $privateKey = storage_path('sunat/keys/LLAVE_PRIVADA.pem');
                    $publicKey = storage_path('sunat/keys/LLAVE_PUBLICA.pem');
                    if (!file_exists($privateKey))
                        throw new Exception('No se encuentra la LLAVE PRIVADA');
                    if (!file_exists($publicKey))
                        throw new Exception('No se encuentra la LLAVE PUBLICA');                    

                    $cmdString = sprintf('xmlsec1 --sign --privkey-pem %s,%s --output %s %s', $privateKey, $publicKey,
                        $xmlFullPath, $xmlFullPath);
                    exec($cmdString);

                    while (!file_exists($xmlFullPath)) {
                        sleep(1);
                        if (file_exists($xmlFullPath)) {
                            break;
                        }
                    }
                    chmod($xmlFullPath, 0777);


                }elseif(env('SYSTEM')=='windows'){


                    $privateKey = storage_path('sunat/keys/PrivateKey.key');
                    $publicKey = storage_path('sunat/keys/ServerCertificate.cer');
                    if (!file_exists($privateKey))
                        throw new Exception('No se encuentra la LLAVE PRIVADA');
                    if (!file_exists($publicKey))
                        throw new Exception('No se encuentra la LLAVE PUBLICA');

                    $ReferenceNodeName = 'ExtensionContent';

                    // Load the XML to be signed
                    $doc = new DOMDocument();
                    $doc->load($xmlFullPath);

                    // Create a new Security object 
                    $objDSig = new XMLSecurityDSig();
                    // Use the c14n exclusive canonicalization
                    $objDSig->setCanonicalMethod(XMLSecurityDSig::C14N);
                    // Sign using SHA-256
                    $objDSig->addReference(
                        $doc, 
                        XMLSecurityDSig::SHA1, 
                        array('http://www.w3.org/2000/09/xmldsig#enveloped-signature'),
                        $options = array('force_uri' => true)
                    );

                    // Create a new (private) Security key
                    $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type'=>'private'));
                    /*
                    If key has a passphrase, set it using
                    $objKey->passphrase = '<passphrase>';
                    */

                    // Load the private key
                    $objKey->loadKey($privateKey, TRUE);
                    //$objKey->loadKey('certificates/PrivateKey.key', TRUE);

                    // Sign the XML file
                    $objDSig->sign($objKey,$doc->getElementsByTagName($ReferenceNodeName)->item(0));

                    // Add the associated public key to the signature
                    $objDSig->add509Cert(file_get_contents($publicKey));
                    //$objDSig->add509Cert(file_get_contents('certificates/ServerCertificate.cer'));
                    // Append the signature to the XML
                    //die(var_dump($doc->documentElement));
                    $objDSig->appendSignature($doc->getElementsByTagName($ReferenceNodeName)->item(0));
                    //$objDSig->appendSignature($ReferenceNodeName);
                    // Save the signed XML
                    $doc->save($xmlFullPath);


                }

            }

            $zipPath = $summaryDocumentsData->SupSupplier->SupSupplierConfiguration->c_public_path_document . DIRECTORY_SEPARATOR . $xmlName . '.ZIP';
            $zipFullPath = public_path($zipPath);

            # DocInvoiceFile
            $docInvoiceFile = new DocInvoiceFile();
            $docInvoiceFile->n_id_invoice = $summaryDocumentsId;
            $docInvoiceFile->c_has_document = 'yes';
            $docInvoiceFile->c_document_name = $xmlName . '.ZIP';
            $docInvoiceFile->d_date_document_created = date('Y-m-d H:i:s');
            $docInvoiceFile->c_has_cdr = 'no';
            $docInvoiceFile->c_has_pdf = 'no';
            $docInvoiceFile->c_is_sent = 'no';
            $docInvoiceFile->c_has_sunat_response = 'no';
            $docInvoiceFile->c_has_sunat_successfully_passed = 'no';
            $docInvoiceFile->c_is_cdr_processed_dispatched = 'no';
            $docInvoiceFile->save();

            # Comprimir contenido
            file_exists($zipFullPath) ? unlink($zipFullPath) : '';
            \Zipper::make($zipFullPath)->add($xmlFullPath)->close();
            unlink($xmlFullPath);

            $response['status'] = 1;
            $response['path'] = $zipFullPath;
            chmod($zipFullPath, 0777);

            Log::info('Generación de XML',
                [
                'lgph_id' => 4, 'n_id_invoice' => $summaryDocumentsId, 'c_invoice_type_code' => 'RC'
                ]
            );
        } catch (Exception $exc) {
            Log::error($exc->getMessage(),
                [
                'lgph_id' => 4, 'n_id_invoice' => $summaryDocumentsId, 'c_invoice_type_code' => 'RC'
                ]
            );
            $response['message'] = $exc->getMessage();
        }
        return $response;
    }

}
