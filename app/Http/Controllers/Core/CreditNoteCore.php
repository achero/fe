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
use App\DocInvoiceAdditionalInformationAdditionalProperty;
use App\DocInvoiceLegalMonetaryTotal;
use App\DocInvoiceAdditionalInformationAdditionalMonetaryTotal;
use App\DocInvoiceTaxTotal;
use App\DocInvoiceTaxTotalTaxSubtotal;
use App\DocInvoiceTaxTotalTaxSubtotalTaxCategoryTaxScheme;
use App\DocInvoiceCustomer;
use App\DocInvoiceSupplier;
use App\DocInvoiceItem;
use App\DocInvoiceItemDescription;
use App\DocInvoiceItemPricingReferenceAlternativeConditionPrice;
use App\DocInvoiceItemTaxTotal;
use App\DocInvoiceItemTaxTotalTaxSubtotal;
use App\DocInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme;
use App\DocInvoiceItemTaxTotalIgv;
use App\DocInvoicePdfData;
use App\DocInvoiceExtraData;
use App\DocInvoicePdfDataCustom;
use App\DocInvoiceSignature;
use App\DocInvoiceDiscrepancyResponse;
use App\DocInvoiceBillingReferenceInvoiceDocumentReference;

use App\Http\Controllers\Traits\UtilHelper;

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Nota de crédito
 */
class CreditNoteCore
{

    use UtilHelper;  

    /**
     * Lee el INPUT FILE y lo convierte en un ARRAY.
     * 
     * @param file $file
     * @return array
     */
    public static function getFile($file)
    {
        $output['status'] = false;
        $output['message'] = '';
        $output['document'] = '';
        $response = [];
        $count = [];
        try {
            $count['CreditNote']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']['sac:AdditionalInformation']
                ['sac:AdditionalMonetaryTotal'] = 0;
            $count['CreditNote']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']['sac:AdditionalInformation']
                ['sac:AdditionalProperty'] = 0;
            $count['CreditNote']['cac:TaxTotal'] = 0;
            $count['CreditNote']['cac:CreditNoteLine'] = 0;
            $a = 0;

            foreach ($file as $key => $value) {
                if (!mb_check_encoding($value, "UTF-8")) {
                    $value = utf8_encode($value);
                }
                $value = trim($value);
                $line = explode('|', $value);
                switch ($key) {
                    /* Cabecera */
                    case 0:
                        foreach ($line as $k => $v) {
                            switch ($k) {
                                /* Fecha de  Emisión */
                                case 0:
                                    $response['CreditNote']['cbc:IssueDate'] = $v;
                                    break;
                                /* Firma Digital */
                                case 1:
                                    break;
                                /* Apellidos y nombres, denominación o razón social */
                                case 2:
                                    $response['CreditNote']['cac:AccountingSupplierParty']['cac:Party']['cac:PartyLegalEntity']
                                        ['cbc:RegistrationName'] = $v;
                                    break;
                                /* Nombre Comercial */
                                case 3:
                                    if (isset($v) && !empty($v)) {
                                        $response['CreditNote']['cac:AccountingSupplierParty']['cac:Party']
                                            ['cac:PartyName']['cbc:Name'] = $v;
                                    }
                                    break;
                                /* Domicilio Fiscal */
                                case 4:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {

                                                /* Co digo de Ubigeo - Catalago No 13 */
                                                case 0:
                                                    $response['CreditNote']['cac:AccountingSupplierParty']['cac:Party']
                                                        ['cac:PostalAddress']['cbc:ID'] = $va;
                                                    break;
                                                /* Direccion completa y detallada */
                                                case 1:
                                                    $response['CreditNote']['cac:AccountingSupplierParty']['cac:Party']
                                                        ['cac:PostalAddress']['cbc:StreetName'] = $va;
                                                    break;
                                                /* Urbanizacion */
                                                case 2:
                                                    $response['CreditNote']['cac:AccountingSupplierParty']['cac:Party']
                                                        ['cac:PostalAddress']['cbc:CitySubdivisionName'] = $va;
                                                    break;
                                                /* Provincia */
                                                case 3:
                                                    $response['CreditNote']['cac:AccountingSupplierParty']['cac:Party']
                                                        ['cac:PostalAddress']['cbc:CityName'] = $va;
                                                    break;
                                                /* Departamento */
                                                case 4:
                                                    $response['CreditNote']['cac:AccountingSupplierParty']['cac:Party']
                                                        ['cac:PostalAddress']['cbc:CountrySubentity'] = $va;
                                                    break;
                                                /* Distrito */
                                                case 5:
                                                    $response['CreditNote']['cac:AccountingSupplierParty']['cac:Party']
                                                        ['cac:PostalAddress']['cbc:District'] = $va;
                                                    break;
                                                /* Codigo de pais - Catalogo No 04 */
                                                case 6:
                                                    $response['CreditNote']['cac:AccountingSupplierParty']['cac:Party']
                                                        ['cac:PostalAddress']['cbc:Country']['cbc:IdentificationCode'] = $va;
                                                    break;
                                            }
                                        }
                                    }
                                    break;
                                /* Numero de RUC */
                                case 5:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {

                                                /*  N umero de RUC */
                                                case 0:
                                                    $response['CreditNote']['cac:AccountingSupplierParty']
                                                        ['cbc:CustomerAssignedAccountID'] = $va;
                                                    break;
                                                /* Tipo de documento - Catalogo No 06 */
                                                case 1:
                                                    $response['CreditNote']['cac:AccountingSupplierParty']
                                                        ['cbc:AdditionalAccountID'] = $va;
                                                    break;
                                            }
                                        }
                                    }
                                    break;
                                /* Codigo del tipo de nota de credito electronica */
                                case 6:
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            /* Ser ie y nu mero de documento afectado */
                                            case 0:
                                                $response['CreditNote']['cac:DiscrepancyResponse']
                                                    ['cbc:ReferenceID'] = $va;
                                                break;
                                            /* Tipo de documento - Catalogo No 06 */
                                            case 1:
                                                $response['CreditNote']['cac:DiscrepancyResponse']
                                                    ['cbc:ResponseCode'] = $va;
                                                break;
                                        }
                                    }
                                    break;
                                /* Numeracion, conformada por serie y numero correlativo */
                                case 7:
                                    $response['CreditNote']['cbc:ID'] = $v;
                                    break;
                                /* Tipo y numero de documento de identidad del adquiriente o usuario */
                                case 8:
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            /* Num ero de  documento */
                                            case 0:
                                                $response['CreditNote']['cac:AccountingCustomerParty']
                                                    ['cbc:CustomerAssignedAccountID'] = $va;
                                                break;
                                            /* Tipo de documento - Catalogo No 06 */
                                            case 1:
                                                $response['CreditNote']['cac:AccountingCustomerParty']
                                                    ['cbc:AdditionalAccountID'] = $va;
                                                break;
                                        }
                                    }
                                    break;
                                /* Apelidos y Nombres o denominación o razon social del adquiriente o usuario */
                                case 9:
                                    $response['CreditNote']['cac:AccountingCustomerParty']['cac:Party']
                                        ['cac:PartyLegalEntity']['cbc:RegistrationName'] = $v;
                                    break;
                                /* Motivo o sustento */
                                case 10:
                                    $response['CreditNote']['cac:DiscrepancyResponse']['cbc:Description'] = $v;
                                    break;
                                /* Total valor de venta - operaciones gravadas */
                                case 11:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {
                                                /* Cod igo de  tipo de monto - Catalogo No 14 */
                                                case 0:
                                                    $response['CreditNote']['ext:UBLExtensions']['ext:UBLExtension']
                                                        ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:AdditionalMonetaryTotal'][$count['CreditNote']['ext:UBLExtensions']
                                                        ['ext:UBLExtension']['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:AdditionalMonetaryTotal']]['cbc:ID'] = $va;
                                                    break;
                                                /* Monto */
                                                case 1:
                                                    $response['CreditNote']['ext:UBLExtensions']['ext:UBLExtension']
                                                        ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:AdditionalMonetaryTotal'][$count['CreditNote']['ext:UBLExtensions']
                                                        ['ext:UBLExtension']['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:AdditionalMonetaryTotal']]['cbc:PayableAmount'] = $va;
                                                    break;
                                            }
                                        }
                                        $count['CreditNote']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']
                                            ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal'] ++;
                                    }
                                    break;
                                /* Total valor de venta - operaciones inafectas */
                                case 12:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {
                                                /* Cod igo de  tipo de monto - Catalogo No 14 */
                                                case 0:
                                                    $response['CreditNote']['ext:UBLExtensions']['ext:UBLExtension']
                                                        ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:AdditionalMonetaryTotal'][$count['CreditNote']['ext:UBLExtensions']
                                                        ['ext:UBLExtension']['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:AdditionalMonetaryTotal']]['cbc:ID'] = $va;
                                                    break;
                                                /* Monto */
                                                case 1:
                                                    $response['CreditNote']['ext:UBLExtensions']['ext:UBLExtension']
                                                        ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:AdditionalMonetaryTotal'][$count['CreditNote']['ext:UBLExtensions']
                                                        ['ext:UBLExtension']['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:AdditionalMonetaryTotal']]['cbc:PayableAmount'] = $va;
                                                    break;
                                            }
                                        }
                                        $count['CreditNote']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']
                                            ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal'] ++;
                                    }
                                    break;
                                /* Total valor de venta - operaciones exoneradas */
                                case 13:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {
                                                /* Cod igo de  tipo de monto - Catalogo No 14 */
                                                case 0:
                                                    $response['CreditNote']['ext:UBLExtensions']['ext:UBLExtension']
                                                        ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:AdditionalMonetaryTotal'][$count['CreditNote']['ext:UBLExtensions']
                                                        ['ext:UBLExtension']['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:AdditionalMonetaryTotal']]['cbc:ID'] = $va;
                                                    break;
                                                /* Monto */
                                                case 1:
                                                    $response['CreditNote']['ext:UBLExtensions']['ext:UBLExtension']
                                                        ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:AdditionalMonetaryTotal'][$count['CreditNote']['ext:UBLExtensions']
                                                        ['ext:UBLExtension']['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:AdditionalMonetaryTotal']]['cbc:PayableAmount'] = $va;
                                                    break;
                                            }
                                        }
                                        $count['CreditNote']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']
                                            ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal'] ++;
                                    }
                                    break;
                                /* Sumatoria IGV */
                                case 14:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {

                                                /* Su matoria de IGV */
                                                case 0:
                                                    $response['CreditNote']['cac:TaxTotal'][$count['CreditNote']
                                                        ['cac:TaxTotal']]['cbc:TaxAmount'] = $va;
                                                    break;
                                                /* Sumatorio de IGV (Subtotal) */
                                                case 1:
                                                    $response['CreditNote']['cac:TaxTotal'][$count['CreditNote']
                                                        ['cac:TaxTotal']]['cac:TaxSubtotal']['cbc:TaxAmount'] = $va;
                                                    break;
                                                /* Codigo de tributo - Catalogo No 05 */
                                                case 2:
                                                    $response['CreditNote']['cac:TaxTotal'][$count['CreditNote']
                                                        ['cac:TaxTotal']]['cac:TaxSubtotal']['cac:TaxCategory']
                                                        ['cac:TaxScheme']['cbc:ID'] = $va;
                                                    break;
                                                /* Nombre de tributo - Catalogo No 05 */
                                                case 3:
                                                    $response['CreditNote']['cac:TaxTotal'][$count['CreditNote']
                                                        ['cac:TaxTotal']]['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']
                                                        ['cbc:Name'] = $va;
                                                    break;
                                                /* Codigo internacional tributo - Catalogo No 05 */
                                                case 4:
                                                    $response['CreditNote']['cac:TaxTotal'][$count['CreditNote']
                                                        ['cac:TaxTotal']]['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']
                                                        ['cbc:TaxTypeCode'] = $va;
                                                    break;
                                            }
                                        }
                                        $count['CreditNote']['cac:TaxTotal'] ++;
                                    }
                                    break;
                                /* Sumatoria ISC */
                                case 15:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {
                                                /* Su matoria de ISC */
                                                case 0:
                                                    $response['CreditNote']['cac:TaxTotal'][$count['CreditNote']
                                                        ['cac:TaxTotal']]['cbc:TaxAmount'] = $va;
                                                    break;
                                                /* Sumatorio de ISC (Subtotal) */
                                                case 1:
                                                    $response['CreditNote']['cac:TaxTotal'][$count['CreditNote']
                                                        ['cac:TaxTotal']]['cac:TaxSubtotal']['cbc:TaxAmount'] = $va;
                                                    break;
                                                /* Codigo de tributo - Catalogo No 05 */
                                                case 2:
                                                    $response['CreditNote']['cac:TaxTotal'][$count['CreditNote']
                                                        ['cac:TaxTotal']]['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']
                                                        ['cbc:ID'] = $va;
                                                    break;
                                                /* Nombre de tributo - Catalogo No 05 */
                                                case 3:
                                                    $response['CreditNote']['cac:TaxTotal'][$count['CreditNote']
                                                        ['cac:TaxTotal']]['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']
                                                        ['cbc:Name'] = $va;
                                                    break;
                                                /* Codigo internacional tributo - Catalogo No 05 */
                                                case 4:
                                                    $response['CreditNote']['cac:TaxTotal'][$count['CreditNote']
                                                        ['cac:TaxTotal']]['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']
                                                        ['cbc:TaxTypeCode'] = $va;
                                                    break;
                                            }
                                        }
                                        $count['CreditNote']['cac:TaxTotal'] ++;
                                    }
                                    break;
                                /* Sumatoria otros tributos */
                                case 16:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {
                                                /* Su matoria de Otros Tributos */
                                                case 0:
                                                    $response['CreditNote']['cac:TaxTotal'][$count['CreditNote']
                                                        ['cac:TaxTotal']]['cbc:TaxAmount'] = $va;
                                                    break;
                                                /* Sumatorio de Otros Tributos (Subtotal) */
                                                case 1:
                                                    $response['CreditNote']['cac:TaxTotal'][$count['CreditNote']
                                                        ['cac:TaxTotal']]['cac:TaxSubtotal']['cbc:TaxAmount'] = $va;
                                                    break;
                                                /* Codigo de tributo - Catalogo No 05 */
                                                case 2:
                                                    $response['CreditNote']['cac:TaxTotal'][$count['CreditNote']
                                                        ['cac:TaxTotal']]['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']
                                                        ['cbc:ID'] = $va;
                                                    break;
                                                /* Nombre de tributo - Catalogo No 05 */
                                                case 3:
                                                    $response['CreditNote']['cac:TaxTotal'][$count['CreditNote']
                                                        ['cac:TaxTotal']]['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']
                                                        ['cbc:Name'] = $va;
                                                    break;
                                                /* Codigo internacional tributo - Catalogo No 05 */
                                                case 4:
                                                    $response['CreditNote']['cac:TaxTotal'][$count['CreditNote']
                                                        ['cac:TaxTotal']]['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']
                                                        ['cbc:TaxTypeCode'] = $va;
                                                    break;
                                            }
                                        }
                                        $count['CreditNote']['cac:TaxTotal'] ++;
                                    }
                                    break;
                                /* Sumatoria otros Cargos */
                                case 17:
                                    if (isset($v) && !empty($v)) {
                                        $response['CreditNote']['cac:LegalMonetaryTotal']['cbc:ChargeTotalAmount'] = $v;
                                    }
                                    break;
                                /* Total descuentos */
                                case 18:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {
                                                /* Codigo de Tipo de monto - Catalogo No 14 */
                                                case 0:
                                                    $response['CreditNote']['ext:UBLExtensions']['ext:UBLExtension']
                                                        ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:AdditionalMonetaryTotal'][$count['CreditNote']['ext:UBLExtensions']
                                                        ['ext:UBLExtension']['ext:ExtensionContent']
                                                        ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal']]['cbc:ID'] = $va;
                                                    break;
                                                /* Monto */
                                                case 1:
                                                    $response['CreditNote']['ext:UBLExtensions']['ext:UBLExtension']
                                                        ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:AdditionalMonetaryTotal'][$count['CreditNote']['ext:UBLExtensions']
                                                        ['ext:UBLExtension']['ext:ExtensionContent']
                                                        ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal']]
                                                        ['cbc:PayableAmount'] = $va;
                                                    break;
                                            }
                                        }
                                        $count['CreditNote']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']
                                            ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal'] ++;
                                    }
                                    break;
                                /* Importe total */
                                case 19:
                                    $response['CreditNote']['cac:LegalMonetaryTotal']['cbc:PayableAmount'] = $v;
                                    break;
                                /* Tipo de moneda en la cual se emite la nota de credito electronica */
                                case 20:
                                    $response['CreditNote']['cbc:DocumentCurrencyCode'] = $v;
                                    break;
                                /* Serie y numero del documento que modifica */
                                case 21:
                                    $response['CreditNote']['cac:BillingReference']['cac:InvoiceDocumentReference']
                                        ['cbc:ID'] = $v;
                                    break;
                                /* Tipo de documento del documento que modifica */
                                case 22:
                                    if (isset($v) && !empty($v)) {
                                        $response['CreditNote']['cac:BillingReference']['cac:InvoiceDocumentReference']
                                            ['cbc:DocumentTypeCode'] = $v;
                                    }
                                    break;
                                /* Documento de referencia - Guia de remision */
                                case 23:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            $expl = explode('¦', $va);
                                            if (isset($expl[0]) && !empty($expl[0])) {
                                                foreach ($expl as $k => $v) {
                                                    switch ($k) {
                                                        /* Numero de Guia */
                                                        case 0:
                                                            $response['CreditNote']['cac:DespatchDocumentReference'][$ke]
                                                                ['cbc:ID'] = $v;
                                                            break;
                                                        /* Tipo de documento - Catalogo No 01 */
                                                        case 1:
                                                            $response['CreditNote']['cac:DespatchDocumentReference'][$ke]
                                                                ['cbc:DocumentTypeCode'] = $v;
                                                            break;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    break;
                                /* Documento de referencia - otros documentos relacionados */
                                case 24:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            $expl = explode('¦', $va);
                                            if (isset($expl[0]) && !empty($expl[0])) {
                                                foreach ($expl as $k => $v) {
                                                    switch ($k) {
                                                        /* Numero de Guia */
                                                        case 0:
                                                            $response['CreditNote']['cac:AdditionalDocumentReference'][$ke]
                                                                ['cbc:ID'] = $v;
                                                            break;
                                                        /* Tipo de documento - Catalogo No 12 */
                                                        case 1:
                                                            $response['CreditNote']['cac:AdditionalDocumentReference'][$ke]
                                                                ['cbc:DocumentTypeCode'] = $v;
                                                            break;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    break;
                                /* Version del UBL */
                                case 25:
                                    $response['CreditNote']['cbc:UBLVersionID'] = $v;
                                    break;
                                /* Version de la estructura del documento */
                                case 26:
                                    $response['CreditNote']['cbc:CustomizationID'] = $v;
                                    break;
                                # Datos extra-oficiales
                                /* Orden de Compra */
                                case 27:
                                    if (isset($v) && !empty($v)) {
                                        $response['CreditNote']['ExtraOficial']['OrdenDeCompra'] = $v;
                                    }
                                    break;
                                /* Condiciones de Pago */
                                case 28:
                                    if (isset($v) && !empty($v)) {
                                        $response['CreditNote']['ExtraOficial']['CondicionesDePago'] = $v;
                                    }
                                    break;
                                /* Fecha de Vencimiento */
                                case 29:
                                    if (isset($v) && !empty($v)) {
                                        $response['CreditNote']['ExtraOficial']['FechaDeVencimiento'] = $v;
                                    }
                                    break;
                                /* Monto en Letras */
                                case 30:
                                    if (isset($v) && !empty($v)) {
                                        $explode = explode('!', $v);
                                        if (isset($explode[0]) && !empty($explode[0])) {
                                            foreach ($explode as $ke => $va) {
                                                $expl = explode('¦', $va);
                                                if (isset($expl[0]) && !empty($expl[0])) {
                                                    foreach ($expl as $k => $v) {
                                                        switch ($k) {
                                                            /* Codigo de la leyenda - Catalogo No 15 */
                                                            case 0:
                                                                $response['CreditNote']['ext:UBLExtensions']['ext:UBLExtension']
                                                                    ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                                    ['sac:AdditionalProperty'][$count['CreditNote']
                                                                    ['ext:UBLExtensions']['ext:UBLExtension']
                                                                    ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                                    ['sac:AdditionalProperty']]['cbc:ID'] = $v;
                                                                break;
                                                            /* Descripcion de la leyenda */
                                                            case 1:
                                                                $response['CreditNote']['ext:UBLExtensions']['ext:UBLExtension']
                                                                    ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                                    ['sac:AdditionalProperty'][$count['CreditNote']
                                                                    ['ext:UBLExtensions']['ext:UBLExtension']
                                                                    ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                                    ['sac:AdditionalProperty']]['cbc:Value'] = $v;
                                                                break;
                                                        }
                                                    }
                                                }
                                                $count['CreditNote']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']
                                                    ['sac:AdditionalInformation']['sac:AdditionalProperty'] ++;
                                            }
                                        }
                                    }
                                    break;
                                /* Observación */
                                case 31:
                                    if (isset($v) && !empty($v)) {
                                        $response['CreditNote']['ExtraOficial']['Observacion'] = $v;
                                    }
                                    break;
                                /* Dirección del Cliente */
                                case 32:
                                    if (isset($v) && !empty($v)) {
                                        $response['CreditNote']['cac:AccountingCustomerParty']['cac:Party']
                                            ['cac:PhysicalLocation']['cbc:Description'] = $v;
                                    }
                                    break;
                                /* Correo del cliente */
                                case 33:
                                    if (isset($v) && !empty($v)) {
                                        $response['CreditNote']['ExtraOficial']['ClienteCorreo'] = $v;
                                    }
                                    break;
                                /* Tipo de cambio */
                                case 34:
                                    $response['CreditNote']['ExtraOficial']['TipoDeCambio'] = $v;
                                    break;
                                ## LOLIMSA ##
                                case 35:
                                    if (isset($v) && !empty($v)) {
                                        $response['CreditNote']['ExtraOficial']['Custom'][$a]['c_value'] = $v;
                                        $response['CreditNote']['ExtraOficial']['Custom'][$a]['c_name'] = 'cf';
                                        $response['CreditNote']['ExtraOficial']['Custom'][$a]['n_index'] = $a;
                                        $a++;
                                    }
                                    break;
                                case 36:
                                    if (isset($v) && !empty($v)) {
                                        $response['CreditNote']['ExtraOficial']['Custom'][$a]['c_value'] = $v;
                                        $response['CreditNote']['ExtraOficial']['Custom'][$a]['c_name'] = 'sec';
                                        $response['CreditNote']['ExtraOficial']['Custom'][$a]['n_index'] = $a;
                                        $a++;
                                    }
                                    break;
                                case 37:
                                    if (isset($v) && !empty($v)) {
                                        $response['CreditNote']['ExtraOficial']['Custom'][$a]['c_value'] = $v;
                                        $response['CreditNote']['ExtraOficial']['Custom'][$a]['c_name'] = 'usuario';
                                        $response['CreditNote']['ExtraOficial']['Custom'][$a]['n_index'] = $a;
                                        $a++;
                                    }
                                    break;
                                case 38:
                                    if (isset($v) && !empty($v)) {
                                        $response['CreditNote']['ExtraOficial']['Custom'][$a]['c_value'] = $v;
                                        $response['CreditNote']['ExtraOficial']['Custom'][$a]['c_name'] = 'correo_contacto';
                                        $response['CreditNote']['ExtraOficial']['Custom'][$a]['n_index'] = $a;
                                        $a++;
                                    }
                                    break;
                            }
                        }
                        break;
                    /* Detalle */
                    default:
                        $c['CreditNote']['cac:CreditNoteLine']['cac:PricingReference']['cac:AlternativeConditionPrice'] = 0;
                        $c['CreditNote']['cac:CreditNoteLine']['cac:TaxTotal'] = 0;
                        foreach ($line as $k => $v) {
                            switch ($k) {
                                /* Unidad de medida por item */
                                case 0:
                                    $response['CreditNote']['cac:CreditNoteLine'][$count['CreditNote']['cac:CreditNoteLine']]
                                        ['cbc:CreditedQuantity']['@unitCode'] = $v;
                                    break;
                                /* Cantidad de unidades por item */
                                case 1:
                                    $response['CreditNote']['cac:CreditNoteLine'][$count['CreditNote']['cac:CreditNoteLine']]
                                        ['cbc:CreditedQuantity']['amount'] = $v;
                                    break;
                                /* Codigo del producto */
                                case 2:
                                    if (isset($v) && !empty($v)) {
                                        $response['CreditNote']['cac:CreditNoteLine'][$count['CreditNote']
                                            ['cac:CreditNoteLine']]['cac:Item']['cac:SellersItemIdentificacion']['cbc:ID'] = $v;
                                    }
                                    break;
                                /* Descripcion detallada del servicio prestado, bien vendido o cedido en uso indicando las 
                                 * caracteristicas */
                                case 3:
                                    if (isset($v) && !empty($v)) {
                                        $explode = explode('!', $v);
                                        foreach ($explode as $ke => $va) {
                                            $response['CreditNote']['cac:CreditNoteLine'][$count['CreditNote']['cac:CreditNoteLine']]
                                                ['cac:Item']['cbc:Description'][$ke] = $va;
                                        }
                                    }
                                    break;
                                /* Valor unitario por item */
                                case 4:
                                    $response['CreditNote']['cac:CreditNoteLine'][$count['CreditNote']['cac:CreditNoteLine']]
                                        ['cac:Price']['cbc:PriceAmount'] = $v;
                                    break;
                                /* Precio de venta unitario por item y codigo */
                                case 5:
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            /* Mon to de P recio de venta */
                                            case 0:
                                                $response['CreditNote']['cac:CreditNoteLine'][$count['CreditNote']
                                                    ['cac:CreditNoteLine']]['cac:PricingReference']
                                                    ['cac:AlternativeConditionPrice'][$c['CreditNote']['cac:CreditNoteLine']
                                                    ['cac:PricingReference']['cac:AlternativeConditionPrice']]
                                                    ['cbc:PriceAmount'] = $va;
                                                break;
                                            /* Codigo de tipo de precio - Catalogo No 16 */
                                            case 1:
                                                $response['CreditNote']['cac:CreditNoteLine'][$count['CreditNote']
                                                    ['cac:CreditNoteLine']]['cac:PricingReference']
                                                    ['cac:AlternativeConditionPrice'][$c['CreditNote']['cac:CreditNoteLine']
                                                    ['cac:PricingReference']['cac:AlternativeConditionPrice']]
                                                    ['cbc:PriceTypeCode'] = $va;
                                                break;
                                        }
                                    }
                                    break;
                                /* Afectacion al IGV por item */
                                case 6:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {
                                                /* Mon to de I GV de la linea */
                                                case 0:
                                                    $response['CreditNote']['cac:CreditNoteLine'][$count['CreditNote']
                                                        ['cac:CreditNoteLine']]['cac:TaxTotal'][$c['CreditNote']
                                                        ['cac:CreditNoteLine']['cac:TaxTotal']]['cbc:TaxAmount'] = $va;
                                                    break;
                                                /* Monto de IGV de la linea */
                                                case 1:
                                                    $response['CreditNote']['cac:CreditNoteLine'][$count['CreditNote']
                                                        ['cac:CreditNoteLine']]['cac:TaxTotal'][$c['CreditNote']
                                                        ['cac:CreditNoteLine']['cac:TaxTotal']]['cac:TaxSubtotal']['cbc:TaxAmount'] = $va;
                                                    break;
                                                /* Afectacion al IGV - Catalogo No 07 */
                                                case 2:
                                                    $response['CreditNote']['cac:CreditNoteLine'][$count['CreditNote']
                                                        ['cac:CreditNoteLine']]['cac:TaxTotal'][$c['CreditNote']
                                                        ['cac:CreditNoteLine']['cac:TaxTotal']]['cac:TaxSubtotal']
                                                        ['cac:TaxCategory']['cbc:TaxExemptionReasonCode'] = $va;
                                                    break;
                                                /* Codigo de tributo - Catalogo No 05 */
                                                case 3:
                                                    $response['CreditNote']['cac:CreditNoteLine'][$count['CreditNote']
                                                        ['cac:CreditNoteLine']]['cac:TaxTotal'][$c['CreditNote']
                                                        ['cac:CreditNoteLine']['cac:TaxTotal']]['cac:TaxSubtotal']
                                                        ['cac:TaxCategory']['cac:TaxScheme']['cbc:ID'] = $va;
                                                    break;
                                                /* Nombre de tributo - Catalogo No 05 */
                                                case 4:
                                                    $response['CreditNote']['cac:CreditNoteLine'][$count['CreditNote']
                                                        ['cac:CreditNoteLine']]['cac:TaxTotal'][$c['CreditNote']
                                                        ['cac:CreditNoteLine']['cac:TaxTotal']]['cac:TaxSubtotal']
                                                        ['cac:TaxCategory']['cac:TaxScheme']['cbc:Name'] = $va;
                                                    break;
                                                /* Codigo internacional tributo - Catalogo No 05 */
                                                case 5:
                                                    $response['CreditNote']['cac:CreditNoteLine'][$count['CreditNote']
                                                        ['cac:CreditNoteLine']]['cac:TaxTotal'][$c['CreditNote']
                                                        ['cac:CreditNoteLine']['cac:TaxTotal']]['cac:TaxSubtotal']
                                                        ['cac:TaxCategory']['cac:TaxScheme']['cbc:TaxTypeCode'] = $va;
                                                    break;
                                            }
                                        }
                                        $c['CreditNote']['cac:CreditNoteLine']['cac:TaxTotal'] ++;
                                    }
                                    break;
                                /* Sistema de ISC Por Item */
                                case 7:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {

                                                /* Mo nto de ISC de la linea */
                                                case 0:
                                                    $response['CreditNote']['cac:CreditNoteLine'][$count['CreditNote']
                                                        ['cac:CreditNoteLine']]['cac:TaxTotal'][$c['CreditNote']
                                                        ['cac:CreditNoteLine']['cac:TaxTotal']]['cbc:TaxAmount'] = $va;
                                                    break;
                                                /* Monto de ISC de la linea */
                                                case 1:
                                                    $response['CreditNote']['cac:CreditNoteLine'][$count['CreditNote']
                                                        ['cac:CreditNoteLine']]['cac:TaxTotal'][$c['CreditNote']
                                                        ['cac:CreditNoteLine']['cac:TaxTotal']]['cac:TaxSubtotal']
                                                        ['cbc:TaxAmount'] = $va;
                                                    break;
                                                /* Tipo de sistema de ISC - Catalogo No 08 */
                                                case 2:
                                                    $response['CreditNote']['cac:CreditNoteLine'][$count['CreditNote']
                                                        ['cac:CreditNoteLine']]['cac:TaxTotal'][$c['CreditNote']
                                                        ['cac:CreditNoteLine']['cac:TaxTotal']]['cac:TaxSubtotal']
                                                        ['cac:TaxCategory']['cbc:TierRange'] = $va;
                                                    break;
                                                /* Codigo de tributo - Catalogo No 05 */
                                                case 3:
                                                    $response['CreditNote']['cac:CreditNoteLine'][$count['CreditNote']
                                                        ['cac:CreditNoteLine']]['cac:TaxTotal'][$c['CreditNote']
                                                        ['cac:CreditNoteLine']['cac:TaxTotal']]['cac:TaxSubtotal']
                                                        ['cac:TaxCategory']['cac:TaxScheme']['cbc:ID'] = $va;
                                                    break;
                                                /* Nombre de tributo - Catalogo No 05 */
                                                case 4:
                                                    $response['CreditNote']['cac:CreditNoteLine'][$count['CreditNote']
                                                        ['cac:CreditNoteLine']]['cac:TaxTotal'][$c['CreditNote']
                                                        ['cac:CreditNoteLine']['cac:TaxTotal']]['cac:TaxSubtotal']
                                                        ['cac:TaxCategory']['cac:TaxScheme']['cbc:Name'] = $va;
                                                    break;
                                                /* Codigo internacional tributo - Catalogo No 05 */
                                                case 5:
                                                    $response['CreditNote']['cac:CreditNoteLine'][$count['CreditNote']
                                                        ['cac:CreditNoteLine']]['cac:TaxTotal'][$c['CreditNote']
                                                        ['cac:CreditNoteLine']['cac:TaxTotal']]['cac:TaxSubtotal']
                                                        ['cac:TaxCategory']['cac:TaxScheme']['cbc:TaxTypeCode'] = $va;
                                                    break;
                                            }
                                        }
                                    }
                                    $c['CreditNote']['cac:CreditNoteLine']['cac:TaxTotal'] ++;
                                    break;
                                /* Valor de Venta por Item */
                                case 8:
                                    $response['CreditNote']['cac:CreditNoteLine'][$count['CreditNote']
                                        ['cac:CreditNoteLine']]['cbc:LineExtensionAmount'] = $v;
                                    break;
                                /* Numero de orden del item */
                                case 9:
                                    $response['CreditNote']['cac:CreditNoteLine'][$count['CreditNote']
                                        ['cac:CreditNoteLine']]['cbc:ID'] = $v;
                                    break;
                                /* Valor referencial unitario por item en operaciones no onerosas y codigo */
                                case 10:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {

                                                /* Mo nto de valor referencial unitario */
                                                case 0:
                                                    $response['CreditNote']['cac:CreditNoteLine'][$count['CreditNote']
                                                        ['cac:CreditNoteLine']]['cac:PricingReference']
                                                        ['cac:AlternativeConditionPrice'][$count['CreditNote']
                                                        ['cac:CreditNoteLine']['cac:PricingReference']
                                                        ['cac:AlternativeConditionPrice']]['cbc:PriceAmount'] = $va;
                                                    break;
                                                /* Codigo de tipo de precio - Catalogo No 16 */
                                                case 1:
                                                    $response['CreditNote']['cac:CreditNoteLine'][$count['CreditNote']
                                                        ['cac:CreditNoteLine']]['cac:PricingReference']
                                                        ['cac:AlternativeConditionPrice'][$count['CreditNote']
                                                        ['cac:CreditNoteLine']['cac:PricingReference']
                                                        ['cac:AlternativeConditionPrice']]['cbc:PriceTypeCode'] = $va;
                                                    break;
                                            }
                                        }
                                    }
                                    break;
                            }
                        }
                        $count['CreditNote']['cac:CreditNoteLine'] ++;
                        break;
                }
            }
            $output['status'] = true;
            $output['document'] = $response;

            Log::info('Generación de array',
                [
                'lgph_id' => 1, 'c_id' => $response['CreditNote']['cbc:ID'],
                'c_invoice_type_code' => '07',
                ]
            );
        } catch (Exception $exc) {
            Log::error($exc->getMessage(), ['lgph_id' => 1]);
            $output['message'] = $exc->getMessage();
        }
        return $output;
    }

    /**
     * Recibe el ARRAY del INPUT y lo valida. Retorna un bool STATUS y array MESSAGE
     * 
     * @param array $array
     * @return array
     */
    public static function validateFile($array)
    {
        $validation = array(
            'CreditNote.cbc:UBLVersionID' => 'required|max:10',
            'CreditNote.cbc:CustomizationID' => 'required|max:10',
            'CreditNote.cbc:ID' => 'required|max:13',
            'CreditNote.cbc:IssueDate' => 'required|max:10',
            'CreditNote.cbc:DocumentCurrencyCode' => 'required|size:3',
        );
        $messages = array(
            'CreditNote.cbc:UBLVersionID.required' => 'La version del UBL es requerido.',
            'CreditNote.cbc:UBLVersionID.max' => 'La version del UBL excedió la cantidad de caracteres.',
            'CreditNote.cbc:CustomizationID.required' => 'La versión de la estructura del Documento es requerido.',
            'CreditNote.cbc:CustomizationID.max' => 'La versión de la estructura del Documento excedió la cantidad de caracteres.',
            'CreditNote.cbc:ID.required' => 'La serie y el numero correlativo es requerido.',
            'CreditNote.cbc:ID.max' => 'La serie y el numero correlativo excedió la cantidad de caracteres.',
            'CreditNote.cbc:IssueDate.required' => 'La Fecha de emisión es requerido.',
            'CreditNote.cbc:IssueDate.max' => 'La Fecha de emisión excedió la cantidad de caracteres.',
            'CreditNote.cbc:DocumentCurrencyCode.required' => 'El tipo de moneda es requerido.',
            'CreditNote.cbc:DocumentCurrencyCode.size' => 'El tipo de moneda no tiene 3 caracteres.',
        );
        $validator = Validator ::make($array, $validation, $messages);
        if ($validator->fails()) {
            Log::error('Error en cabecera: ' . implode(',', $validator->messages()->all()),
                ['lgph_id' => 2, 'c_id' => $array['CreditNote']['cbc:ID'], 'c_invoice_type_code' => '07',]);
            throw new Exception(implode(',', $validator->messages()->all()));
        }

        if (isset($array['CreditNote']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']
                ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal'])) {
            foreach ($array['CreditNote']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']
            ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal'] as $value) {
                $validation = array(
                    'cbc:ID' => 'required|size:4',
                    'cbc:PayableAmount' => 'required|max:15',
                    'sac:ReferenceAmount' => 'max:15',
                    'sac:TotalAmount' => 'max:15',
                );
                $messages = array(
                    'cbc:ID.required' => 'El código del concepto adicional es requerido.',
                    'cbc:ID.size' => 'El código del concepto adicional no tiene 4 caracteres.',
                    'cbc:PayableAmount.required' => 'El monto a pagar es requerido.',
                    'cbc:PayableAmount.max' => 'El monto a pagar excedió el máximo de caracteres.',
                    'sac:ReferenceAmount.max' => 'El monto de referencia excedio el máximo de caracteres.',
                    'sac:TotalAmount.max' => 'El monto Total excedió el máximo de caracteres.',
                );
                $validator = Validator::make($value, $validation, $messages);
                if ($validator->fails()) {
                    Log::error('Error en cabecera: ' . implode(',', $validator->messages()->all()),
                        ['lgph_id' => 2, 'c_id' => $array['CreditNote']['cbc:ID'], 'c_invoice_type_code' => '07',]);
                    throw new Exception(implode(',', $validator->messages()->all()));
                }
            }
        }

        if (isset($array['CreditNote']['cac:DespatchDocumentReference']) &&
            !empty($array['CreditNote']['cac:DespatchDocumentReference'])) {
            $i = 1;
            foreach ($array['CreditNote']['cac:DespatchDocumentReference'] as $key => $value) {
                $validation = array(
                    'cbc:ID' => 'required|max:30',
                    'cbc:DocumentTypeCode' => 'required|size:2',
                );
                $messages = array(
                    'cbc:ID.required' => sprintf('Ítem %s | El número de documento es requerido.', $i),
                    'cbc:ID.max' => sprintf('Ítem %s | El número de documento no puede exceder 30 caracteres.', $i),
                    'cbc:DocumentTypeCode.required' => sprintf('Ítem %s | El código de tipo de documento de referencia es requerido.',
                        $i),
                    'cbc:DocumentTypeCode.size' => sprintf('Ítem %s | El código de tipo de documento de referencia no tiene 2 caracteres.',
                        $i),
                );
                $validator = Validator::make($value, $validation, $messages);
                if ($validator->fails()) {
                    Log::error('Error en cabecera: ' . implode(',', $validator->messages()->all()),
                        ['lgph_id' => 2, 'c_id' => $array['CreditNote']['cbc:ID'], 'c_invoice_type_code' => '07',]);
                    throw new Exception(implode(',', $validator->messages()->all()));
                }
                $i++;
            }
        }

        if (isset($array['CreditNote']['cac:AdditionalDocumentReference']) &&
            !empty($array['CreditNote']['cac:AdditionalDocumentReference'])) {
            $i = 1;
            foreach ($array['CreditNote']['cac:AdditionalDocumentReference'] as $key => $value) {
                $validation = array(
                    'cbc:ID' => 'required|max:30',
                    'cbc:DocumentTypeCode' => 'required|size:2',
                );
                $messages = array(
                    'cbc:ID.required' => sprintf('Ítem %s | El número de documento es requerido.', $i),
                    'cbc:ID.max' => sprintf('Ítem %s | El número de documento no puede exceder 30 caracteres.', $i),
                    'cbc:DocumentTypeCode.required' => sprintf('Ítem %s | El código de tipo de documento de referencia es requerido.',
                        $i),
                    'cbc:DocumentTypeCode.size' => sprintf('Ítem %s | El código de tipo de documento de referencia no tiene 2 caracteres.',
                        $i),
                );
                $validator = Validator::make($value, $validation, $messages);
                if ($validator->fails()) {
                    Log::error('Error en cabecera: ' . implode(',', $validator->messages()->all()),
                        ['lgph_id' => 2, 'c_id' => $array['CreditNote']['cbc:ID'], 'c_invoice_type_code' => '07',]);
                    throw new Exception(implode(',', $validator->messages()->all()));
                }
                $i++;
            }
        }

        #AccountingSupplierParty
        $validation = array(
            'CreditNote.cac:AccountingSupplierParty.cbc:CustomerAssignedAccountID' => 'required|size:11',
            'CreditNote.cac:AccountingSupplierParty.cbc:AdditionalAccountID' => 'required|size:1',
        );
        $messages = array(
            'CreditNote.cac:AccountingSupplierParty.cbc:CustomerAssignedAccountID.required' => 'El número de documento de identidad (RUC) es requerido.',
            'CreditNote.cac:AccountingSupplierParty.cbc:CustomerAssignedAccountID.size' => 'El número de documento de identidad (RUC) no tiene 11 caracteres.',
            'CreditNote.cac:AccountingSupplierParty.cbc:AdditionalAccountID.required' => 'El tipo de documento de identificación es requerido.',
            'CreditNote.cac:AccountingSupplierParty.cbc:AdditionalAccountID.size' => 'El tipo de documento de identificación no tiene 2 caracter.',
        );
        $validator = Validator ::make($array, $validation, $messages);
        if ($validator->fails()) {
            Log::error('Error en cabecera: ' . implode(',', $validator->messages()->all()),
                ['lgph_id' => 2, 'c_id' => $array['CreditNote']['cbc:ID'], 'c_invoice_type_code' => '07',]);
            throw new Exception(implode(',', $validator->messages()->all()));
        }

        if (isset($array['CreditNote']['cac:AccountingSupplierParty']['cac:Party'])) {
            if (isset($array['CreditNote']['cac:AccountingSupplierParty']['cac:Party']['cac:PartyName'])) {
                $validation = array(
                    'CreditNote.cac:AccountingSupplierParty.cac:Party.cac:PartyName.cbc:Name' => 'required|max:100',
                    'CreditNote.cac:AccountingSupplierParty.cac:Party.cac:PartyLegalEntity.cbc:RegistrationName' => 'required|max:100',
                );
                $messages = array(
                    'CreditNote.cac:AccountingSupplierParty.cac:Party.cac:PartyName.cbc:Name.required' => 'Nombre comercial es requerido.',
                    'CreditNote.cac:AccountingSupplierParty.cac:Party.cac:PartyName.cbc:Name.max' => 'Nombre comercial no puede exceder 100 caracteres',
                    'CreditNote.cac:AccountingSupplierParty.cac:Party.cac:PartyLegalEntity.cbc:RegistrationName.required' => 'Apellidos y nombres o denominación o razón social es requerido.',
                    'CreditNote.cac:AccountingSupplierParty.cac:Party.cac:PartyLegalEntity.cbc:RegistrationName.max' => 'Apellidos y nombres o denominación o razón social no puede exceder 100 caracteres',
                );
                $validator = Validator::make($array, $validation, $messages);
                if ($validator->fails()) {
                    Log::error('Error en cabecera: ' . implode(',', $validator->messages()->all()),
                        ['lgph_id' => 2, 'c_id' => $array['CreditNote']['cbc:ID'], 'c_invoice_type_code' => '07',]);
                    throw new Exception(implode(',', $validator->messages()->all()));
                }
            }

            if (isset($array['CreditNote']['cac:AccountingSupplierParty']['cac:Party']['cac:PostalAddress'])) {
                $validation = array(
                    'CreditNote.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:ID' => 'size:6',
                    'CreditNote.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:StreetName' => 'required|max:100',
                    'CreditNote.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:CitySubdivisionName' => 'max:25',
                    'CreditNote.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:CityName' => 'max:30',
                    'CreditNote.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:CountrySubentity' => 'max:30',
                    'CreditNote.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:District' => 'max:30',
                    'CreditNote.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cac:Country.cbc:IdentificationCode' => 'size:2',
                );
                $messages = array(
                    'CreditNote.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:ID.size' => 'El código de UBIGEO no tiene 6 caracteres.',
                    'CreditNote.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:StreetName.required' => 'La dirección completa y detallada es requerida.',
                    'CreditNote.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:StreetName.max' => 'La dirección completa y detallada excedió los 100 caracteres.',
                    'CreditNote.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:CitySubdivisionName.max' => 'La ubicación o zona excedió los 25 caracteres.',
                    'CreditNote.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:CityName.max' => 'El departamento excedió los 30 caracteres.',
                    'CreditNote.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:CountrySubentity.max' => 'La provincia excedió los 30 caracteres.',
                    'CreditNote.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:District.max' => 'El distrito excedió los 30 caracteres.',
                    'CreditNote.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cac:Country.cbc:IdentificationCode.size' => 'El código del pais no tiene 2 caracteres.',
                );
                $validator = Validator::make($array, $validation, $messages);
                if ($validator->fails()) {
                    Log::error('Error en cabecera: ' . implode(',', $validator->messages()->all()),
                        ['lgph_id' => 2, 'c_id' => $array['CreditNote']['cbc:ID'], 'c_invoice_type_code' => '07',]);
                    throw new Exception(implode(',', $validator->messages()->all()));
                }
            }
        }

        #AccountingCustomerParty
        $validation = array(
            'CreditNote.cac:AccountingCustomerParty.cbc:CustomerAssignedAccountID' => 'required|max:15',
            'CreditNote.cac:AccountingCustomerParty.cbc:AdditionalAccountID' => 'required|size:1',
            'CreditNote.cac:AccountingCustomerParty.cac:Party.cac:PartyLegalEntity.cbc:RegistrationName' => 'required|max:100',
        );
        $messages = array(
            'CreditNote.cac:AccountingCustomerParty.cbc:CustomerAssignedAccountID.required' => 'El número de documento de identidad es requerido.',
            'CreditNote.cac:AccountingCustomerParty.cbc:CustomerAssignedAccountID.max' => 'El número de documento de identidad excedió los 15 caracteres.',
            'CreditNote.cac:AccountingCustomerParty.cbc:AdditionalAccountID.required' => 'Tipo de documento de identificación es requerido.',
            'CreditNote.cac:AccountingCustomerParty.cbc:AdditionalAccountID.size' => 'Tipo de documento de identificación no tiene 1 caracter.',
            'CreditNote.cac:AccountingCustomerParty.cac:Party.cac:PartyLegalEntity.cbc:RegistrationName.required' => 'Apellidos y nombres o denominación o razón social según RUC es requerido.',
            'CreditNote.cac:AccountingCustomerParty.cac:Party.cac:PartyLegalEntity.cbc:RegistrationName.max' => 'Apellidos y nombres o denominación o razón social según RUC excedió los 100 caracteres.',
        );
        $validator = Validator::make($array, $validation, $messages);
        if ($validator->fails()) {
            Log::error('Error en cabecera: ' . implode(',', $validator->messages()->all()),
                ['lgph_id' => 2, 'c_id' => $array['CreditNote']['cbc:ID'], 'c_invoice_type_code' => '07',]);
            throw new Exception(implode(',', $validator->messages()->all()));
        }

        if (isset($array['CreditNote']['cac:TaxTotal'])) {
            foreach ($array['CreditNote']['cac:TaxTotal'] as $value) {
                $validation = array(
                    'cbc:TaxAmount' => 'required|max:15',
                    'cac:TaxSubtotal.cbc:TaxAmount' => 'required|max:15',
                    'cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:ID' => 'required|size:4',
                    'cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:Name' => 'required|max:6',
                    'cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:TaxTypeCode' => 'required|size:3',
                );
                $messages = array(
                    'cbc:TaxAmount.required' => 'Importe total de un tributo para la factura es requerido.',
                    'cbc:TaxAmount.max' => 'Importe total de un tributo para la factura excedió los 15 caracteres.',
                    'cac:TaxSubtotal.cbc:TaxAmount.required' => 'Importe explícito a tributar es requerido.',
                    'cac:TaxSubtotal.cbc:TaxAmount.max' => 'Importe explícito a tributar excedió los 15 caracteres.',
                    'cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:ID.required' => 'Identificación del tributo según Catálogo No. 05 es requerido.',
                    'cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:ID.size' => 'Identificación del tributo según Catálogo No. 05 no tiene 4 caracteres.',
                    'cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:Name.required' => 'Nombre del Tributo (IGV, ISC) es requerido.',
                    'cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:Name.max' => 'Nombre del Tributo (IGV, ISC) excedió los 6 caracteres.',
                    'cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:TaxTypeCode.required' => 'Código del Tipo de Tributo (UN/ECE 5153) es requerido.',
                    'cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:TaxTypeCode.size' => 'Código del Tipo de Tributo (UN/ECE 5153) no tiene 3 caracteres.',
                );
                $validator = Validator::make($value, $validation, $messages);
                if ($validator->fails()) {
                    Log::error('Error en cabecera: ' . implode(',', $validator->messages()->all()),
                        ['lgph_id' => 2, 'c_id' => $array['CreditNote']['cbc:ID'], 'c_invoice_type_code' => '07',]);
                    throw new Exception(implode(',', $validator->messages()->all()));
                }
            }
        }

        $validation = array(
            'CreditNote.cac:LegalMonetaryTotal.cbc:ChargeTotalAmount' => 'max:15',
            'CreditNote.cac:LegalMonetaryTotal.cbc:PayableAmount' => 'required|max:15',
        );
        $messages = array(
            'CreditNote.cac:LegalMonetaryTotal.cbc:ChargeTotalAmount.max' => 'Importe total de cargos aplicados al total de la factura excedió los 15 caracteres.',
            'CreditNote.cac:LegalMonetaryTotal.cbc:PayableAmount.required' => 'Moneda e Importe total a pagar es requerido.',
            'CreditNote.cac:LegalMonetaryTotal.cbc:PayableAmount.max' => 'Moneda e Importe total a pagar excedió los 15 caracteres.',
        );
        $validator = Validator::make($array, $validation, $messages);
        if ($validator->fails()) {
            Log::error('Error en cabecera: ' . implode(',', $validator->messages()->all()),
                ['lgph_id' => 2, 'c_id' => $array['CreditNote']['cbc:ID']]);
            throw new Exception(implode(',', $validator->messages()->all()));
        }

        foreach ($array['CreditNote'] ['cac:CreditNoteLine'] as $key => $value) {
            $itemIndex = $key + 1;
            $validation = array(
                'cbc:ID' => 'required|max:3',
                'cbc:CreditedQuantity.amount' => 'required|max:16',
                'cbc:CreditedQuantity.@unitCode' => 'required|max:3',
                'cbc:LineExtensionAmount' => 'required|max:15',
                'cac:Item.cbc:Description' => 'max:250',
                'cac:Item.cac:SellersItemIdentification.cbc:ID' => 'max:30',
                'cac:Price.cbc:PriceAmount' => 'required|max:15',
            );
            $messages = array(
                'cbc:ID.required' => 'Número de orden del Ítem es requerido.',
                'cbc:ID.max' => 'Número de orden del Ítem excedió los 3 caracteres.',
                'cbc:CreditedQuantity.@unitCode.required' => 'Unidad de medida por Ítem (UN/ECE rec 20) es requerido.',
                'cbc:CreditedQuantity.@unitCode.max' => 'Unidad de medida por Ítem (UN/ECE rec 20) excedió los 3 caracteres.',
                'cbc:CreditedQuantity.amount.required' => 'Cantidad de unidades por Ítem es requerido.',
                'cbc:CreditedQuantity.amount.max' => 'Cantidad de unidades por Ítem excedió los 3 caracteres.',
                'cbc:LineExtensionAmount.required' => 'Moneda e Importe monetario que es el total de la línea de detalle, incluyendo variaciones de precio (subvenciones, cargos o descuentos) pero sin impuestos es requerido.',
                'cbc:LineExtensionAmount.max' => 'Moneda e Importe monetario que es el total de la línea de detalle, incluyendo variaciones de precio (subvenciones, cargos o descuentos) pero sin impuestos excedió los 15 caraceteres.',
                'cac:Item.cbc:Description.max' => 'Descripción detallada del bien vendido o cedido en uso, descripción o tipo de servicio prestado por ítem excedió los 250 caracteres.',
                'cac:Item.cac:SellersItemIdentification.cbc:ID.max' => 'Código del producto excedió los 30 caracteres.',
                'cac:Price.cbc:PriceAmount.required' => 'Valores de venta unitarios por ítem (VU) no incluye impuestos es requerido.',
                'cac:Price.cbc:PriceAmount.max' => 'Valores de venta unitarios por ítem (VU) no incluye impuestos excedió los 15 caracteres.',
            );
            $validator = Validator::make($value, $validation, $messages);
            if ($validator->fails()) {
                Log::error("Error en detalle, Ítem #{$itemIndex}: " . implode(',', $validator->messages()->all()),
                    ['lgph_id' => 2, 'c_id' => $array['CreditNote']['cbc:ID'], 'c_invoice_type_code' => '07', 'c_item_sellers_item_identification_id' => $value['cac:Item']['cac:SellersItemIdentification']['cbc:ID']]);
                throw new Exception(implode(',', $validator->messages()->all()));
            }
            foreach ($value['cac:PricingReference']['cac:AlternativeConditionPrice'] as $v) {
                $validation = array(
                    'cbc:PriceAmount' => 'required|max:15',
                    'cbc:PriceTypeCode' => 'required|size:2',
                );
                $messages = array(
                    'cbc:PriceAmount.required' => 'Monto del valor unitario es requerido.',
                    'cbc:PriceAmount.max' => 'Monto del valor unitario excedió los 15 caracteres.',
                    'cbc:PriceTypeCode.required' => 'Código del valor unitario es requerido.',
                    'cbc:PriceTypeCode.size' => 'Código del valor unitario no tiene 2 caracteres.',
                );
                $validator = Validator::make($v, $validation, $messages);
                if ($validator->fails()) {
                    Log::error("Error en detalle, Ítem #{$itemIndex}: " . implode(',', $validator->messages()->all()),
                        ['lgph_id' => 2, 'c_id' => $array['CreditNote']['cbc:ID'], 'c_invoice_type_code' => '07', 'c_item_sellers_item_identification_id' => $value['cac:Item']['cac:SellersItemIdentification']['cbc:ID']]);
                    throw new Exception(implode(',', $validator->messages()->all()));
                }
            }

            if (isset($value['cac:TaxTotal']) && !empty($value['cac:TaxTotal'])) {
                foreach ($value['cac:TaxTotal'] as $v) {
                    $validation = array(
                        'cbc:TaxAmount' => 'required|max:15',
                        'cac:TaxSubtotal.cbc:TaxAmount' => 'required|max:15',
                        'cac:TaxSubtotal.cac:TaxCategory.cbc:TaxExemptionReasonCode' => 'size:2',
                        'cac:TaxSubtotal.cac:TaxCategory.cbc:TierRange' => 'size:2',
                        'cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:ID' => 'required|size:4',
                        'cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:Name' => 'required|max:6',
                        'cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:TaxTypeCode' => 'size:3',
                    );
                    $messages = array(
                        'cbc:TaxAmount.required' => 'Importe total de un tributo para este ítem es requerido.',
                        'cbc:TaxAmount.max' => 'Importe total de un tributo para este ítem excedió los 15 caracteres.',
                        'cac:TaxSubtotal.cbc:TaxAmount.required' => 'Importe explícito a tributar ( = Tasa Porcentaje * Base Imponible) es requerido.',
                        'cac:TaxSubtotal.cbc:TaxAmount.max' => 'Importe explícito a tributar ( = Tasa Porcentaje * Base Imponible) excedió los 15 caracteres.',
                        'cac:TaxSubtotal.cac:TaxCategory.cbc:TaxExemptionReasonCode.size' => 'Afectación del IGV (Catálogo No. 07) no tiene 2 caracteres.',
                        'cac:TaxSubtotal.cac:TaxCategory.cbc:TierRange.size' => 'Sistema de ISC (Catálogo No. 08) no tiene 2 caracteres.',
                        'cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:ID.required' => 'Identificación del tributo según Catálogo No. 05 es requerido.',
                        'cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:ID.size' => 'Identificación del tributo según Catálogo No. 05 no tiene 4 caracteres.',
                        'cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:Name.required' => 'Nombre del Tributo (IGV, ISC) es requerido.',
                        'cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:Name.max' => 'Nombre del Tributo (IGV, ISC) excedió los 6 caracteres.',
                        'cac:TaxSubtotal.cac:TaxCategory.cac:TaxScheme.cbc:TaxTypeCode.size' => 'Código del Tipo de Tributo (UN/ECE 5153) no tiene 3 caracteres.',
                    );
                    $validator = Validator::make($v, $validation, $messages);
                    if ($validator->fails()) {
                        Log::error("Error en detalle, Ítem #{$itemIndex}: " . implode(',', $validator->messages()->all()),
                            ['lgph_id' => 2, 'c_id' => $array['CreditNote']['cbc:ID'], 'c_invoice_type_code' => '07', 'c_item_sellers_item_identification_id' => $value['cac:Item']['cac:SellersItemIdentification']['cbc:ID']]);
                        throw new Exception(implode(',', $validator->messages()->all()));
                    }
                }
            }
        }

        Log::info('Validación de array',
            [
            'lgph_id' => 2, 'c_id' => $array['CreditNote']['cbc:ID'],
            'c_invoice_type_code' => '07',
            ]
        );
    }

    /**
     * Recibe el array INPUT y lo almacena en la BASE DE DATOS. Retorna el Id del Documento
     * 
     * @param array $array
     * @return int
     */
    public static function setDb($array)
    {
        $response['status'] = 0;
        $response['id'] = 0;
        $response['message'] = '';

        try {
            $supSupplierArray = $array['CreditNote']['cac:AccountingSupplierParty'];
            $supSupplier = SupSupplier::where('c_customer_assigned_account_id',
                    $supSupplierArray['cbc:CustomerAssignedAccountID'])
                ->where('c_additional_account_id', $supSupplierArray['cbc:AdditionalAccountID'])
                ->where('c_status_supplier', 'visible');

            if ($supSupplier->count() == 0) {
                throw new Exception('Emisor no registrado');
            }

            $supSupplier = $supSupplier->first();

            $supSupplier->c_party_party_legal_entity_registration_name = $supSupplierArray['cac:Party']
                ['cac:PartyLegalEntity']['cbc:RegistrationName'];
            $supSupplier->c_party_name_name = (isset($supSupplierArray['cac:Party']['cac:PartyName']['cbc:Name'])) ?
                $supSupplierArray['cac:Party']['cac:PartyName']['cbc:Name'] : NULL;

            if (isset($supSupplierArray['cac:Party']['cac:PostalAddress']['cbc:ID'])) {
                $supSupplier->c_party_postal_address_id = $supSupplierArray['cac:Party']['cac:PostalAddress']['cbc:ID'];
                $supSupplier->c_party_postal_address_street_name = $supSupplierArray['cac:Party']['cac:PostalAddress']
                    ['cbc:StreetName'];
                $supSupplier->c_party_postal_address_city_subdivision_name = $supSupplierArray['cac:Party']['cac:PostalAddress']
                    ['cbc:CitySubdivisionName'];
                $supSupplier->c_party_postal_address_city_name = $supSupplierArray['cac:Party']['cac:PostalAddress']
                    ['cbc:CityName'];
                $supSupplier->c_party_postal_address_country_subentity = $supSupplierArray['cac:Party']['cac:PostalAddress']
                    ['cbc:CountrySubentity'];
                $supSupplier->c_party_postal_address_district = $supSupplierArray['cac:Party']['cac:PostalAddress']
                    ['cbc:District'];
                $supSupplier->c_party_postal_address_country_identification_code = $supSupplierArray['cac:Party']
                    ['cac:PostalAddress']['cbc:Country']['cbc:IdentificationCode'];
            }

            $supSupplier->c_customer_assigned_account_id = $supSupplierArray['cbc:CustomerAssignedAccountID'];
            $supSupplier->c_additional_account_id = $supSupplierArray['cbc:AdditionalAccountID'];
            $supSupplier->save();
            $supSupplierId = $supSupplier->n_id_supplier;

            # CLIENTE
            $cusCustomerArray = $array['CreditNote']['cac:AccountingCustomerParty'];
            $cusCustomer = CusCustomer::where('n_id_supplier', $supSupplierId)->where('c_customer_assigned_account_id',
                    $cusCustomerArray['cbc:CustomerAssignedAccountID'])->where('c_additional_account_id',
                    $cusCustomerArray['cbc:AdditionalAccountID'])->first();

            if (is_null($cusCustomer)) {
                $cusCustomer = new CusCustomer();
            }

            $cusCustomer->n_id_supplier = $supSupplierId;
            $cusCustomer->c_customer_assigned_account_id = $cusCustomerArray['cbc:CustomerAssignedAccountID'];
            $cusCustomer->c_additional_account_id = $cusCustomerArray['cbc:AdditionalAccountID'];
            $cusCustomer->c_party_party_legal_entity_registration_name = $cusCustomerArray['cac:Party']['cac:PartyLegalEntity']
                ['cbc:RegistrationName'];
            $cusCustomer->c_party_physical_location_description = (isset($cusCustomerArray['cac:Party']
                    ['cac:PhysicalLocation']['cbc:Description'])) ? $cusCustomerArray['cac:Party']['cac:PhysicalLocation']
                ['cbc:Description'] : NULL;
            $cusCustomer->save();
            $cusCustomerId = $cusCustomer->n_id_customer;

            # Documento
            $docInvoiceArray = $array['CreditNote'];
            $docInvoice = DocInvoice::where('c_id', $docInvoiceArray['cbc:ID'])->where('n_id_supplier', $supSupplierId)
                    ->where('c_invoice_type_code', '07')->whereIn('c_status_invoice', array('visible', 'hidden'));
            if ($docInvoice->count()) {
                $docInvoice->update(array('c_status_invoice' => 'deleted'));
            }
            $docInvoice = new DocInvoice();
            $docInvoice->n_id_customer = $cusCustomerId;
            $docInvoice->n_id_supplier = $supSupplierId;
            $docInvoice->d_issue_date = $docInvoiceArray['cbc:IssueDate'];
            $docInvoice->c_invoice_type_code = '07';
            $docInvoice->c_customization_id = $docInvoiceArray['cbc:CustomizationID'];
            $docInvoice->c_ubl_version_id = $docInvoiceArray['cbc:UBLVersionID'];
            $docInvoice->c_document_currency_code = $docInvoiceArray['cbc:DocumentCurrencyCode'];
            $docInvoice->c_id = $docInvoiceArray['cbc:ID'];
            $docInvoice->c_status_invoice = 'visible';
            $cIdExplode = explode('-', $docInvoiceArray['cbc:ID']);
            $docInvoice->c_correlative = end($cIdExplode);
            $docInvoice->n_correlative = (int) end($cIdExplode);
            $docInvoice->c_serie = reset($cIdExplode);
            # Buscar el documento relacionado
            $related = DocInvoice::where('c_id', $array['CreditNote']['cac:DiscrepancyResponse']['cbc:ReferenceID'])->where('c_status_invoice',
                    'visible')
                ->whereIn('c_invoice_type_code', array('03', '01'));
            if ($related->count() == 0) {
                throw new Exception('No existe la boleta/factura: ' . $array['CreditNote']['cac:DiscrepancyResponse']['cbc:ReferenceID']);
            }
            if ($related->count() > 1) {
                throw new Exception('Error de sistema, existe más de un(a) boleta/factura activa: ' . $array['CreditNote']['cac:DiscrepancyResponse']['cbc:ReferenceID']);
            }
            $related = $related->first();
            $docInvoice->n_id_invoice_related = $related->n_id_invoice;
            $docInvoice->save();
            $docInvoiceId = $docInvoice->n_id_invoice;

            $docInvoiceCdrStatus = new DocInvoiceCdrStatus();
            $docInvoiceCdrStatus->n_id_invoice = $docInvoiceId;
            $docInvoiceCdrStatus->n_id_cdr_status = 4;
            $docInvoiceCdrStatus->save();

            $docInvoiceDiscrepancyResponse = new DocInvoiceDiscrepancyResponse;
            $docInvoiceDiscrepancyResponse->n_id_invoice = $docInvoiceId;
            $docInvoiceDiscrepancyResponse->c_reference_id = $array['CreditNote']['cac:DiscrepancyResponse']['cbc:ReferenceID'];
            $docInvoiceDiscrepancyResponse->c_response_code = $array['CreditNote']['cac:DiscrepancyResponse']['cbc:ResponseCode'];
            $docInvoiceDiscrepancyResponse->c_description = $array['CreditNote']['cac:DiscrepancyResponse']['cbc:Description'];
            $docInvoiceDiscrepancyResponse->save();

            if (isset($docInvoiceArray['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']
                    ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal'])) {
                $docInvoiceAdditionalInformationAdditionalMonetaryTotalArray = $docInvoiceArray['ext:UBLExtensions']
                    ['ext:UBLExtension']['ext:ExtensionContent']['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal'];
                foreach ($docInvoiceAdditionalInformationAdditionalMonetaryTotalArray as $key => $value) {
                    $docInvoiceAdditionalInformationAdditionalMonetaryTotal = new
                        DocInvoiceAdditionalInformationAdditionalMonetaryTotal();
                    $docInvoiceAdditionalInformationAdditionalMonetaryTotal->n_id_invoice = $docInvoiceId;
                    $docInvoiceAdditionalInformationAdditionalMonetaryTotal->c_id = $value['cbc:ID'];
                    $docInvoiceAdditionalInformationAdditionalMonetaryTotal->c_payable_amount = $value['cbc:PayableAmount'];
                    $docInvoiceAdditionalInformationAdditionalMonetaryTotal->save();
                }
            }

            if (isset($docInvoiceArray['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']
                    ['sac:AdditionalInformation']['sac:AdditionalProperty'])) {
                $docInvoiceAdditionalInformationAdditionalPropertyArray = $docInvoiceArray['ext:UBLExtensions']
                    ['ext:UBLExtension']['ext:ExtensionContent']['sac:AdditionalInformation']['sac:AdditionalProperty'];
                foreach ($docInvoiceAdditionalInformationAdditionalPropertyArray as $key => $value) {
                    $docInvoiceAdditionalInformationAdditionalProperty = new
                        DocInvoiceAdditionalInformationAdditionalProperty();
                    $docInvoiceAdditionalInformationAdditionalProperty->n_id_invoice = $docInvoiceId;
                    $docInvoiceAdditionalInformationAdditionalProperty->c_id = (isset($value['cbc:ID'])) ? $value['cbc:ID'] :
                        NULL;
                    $docInvoiceAdditionalInformationAdditionalProperty->c_name = (isset($value['cbc:Name'])) ?
                        $value['cbc:Name'] : NULL;
                    $docInvoiceAdditionalInformationAdditionalProperty->c_value = (isset($value['cbc:Value'])) ?
                        $value['cbc:Value'] : NULL;
                    $docInvoiceAdditionalInformationAdditionalProperty->save();
                }
            }

            if (isset($array['CreditNote']['cac:TaxTotal'])) {
                $docInvoiceTaxTotalArray = $array['CreditNote']['cac:TaxTotal'];
                foreach ($docInvoiceTaxTotalArray as $key => $value) {
                    $docInvoiceTaxTotal = new DocInvoiceTaxTotal;
                    $docInvoiceTaxTotal->n_id_invoice = $docInvoiceId;
                    $docInvoiceTaxTotal->c_tax_amount = $value['cbc:TaxAmount'];
                    $docInvoiceTaxTotal->save();
                    $docInvoiceTaxTotalId = $docInvoiceTaxTotal->n_id_invoice_tax_total;

                    $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxScheme = new
                        DocInvoiceTaxTotalTaxSubtotalTaxCategoryTaxScheme;
                    $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxSchemeArray = $value['cac:TaxSubtotal']['cac:TaxCategory']
                        ['cac:TaxScheme'];
                    $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxScheme->n_id_invoice_tax_total = $docInvoiceTaxTotalId;
                    $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxScheme->c_id = $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxSchemeArray['cbc:ID'];
                    $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxScheme->c_name = $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxSchemeArray['cbc:Name'];
                    $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxScheme->c_tax_type_code = $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxSchemeArray['cbc:TaxTypeCode'];
                    $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxScheme->save();

                    $docInvoiceTaxTotalTaxSubtotal = new DocInvoiceTaxTotalTaxSubtotal;
                    $docInvoiceTaxTotalTaxSubtotalArray = $value['cac:TaxSubtotal'];
                    $docInvoiceTaxTotalTaxSubtotal->n_id_invoice_tax_total = $docInvoiceTaxTotalId;
                    $docInvoiceTaxTotalTaxSubtotal->c_tax_amount = $docInvoiceTaxTotalTaxSubtotalArray['cbc:TaxAmount'];
                    $docInvoiceTaxTotalTaxSubtotal->save();
                }
            }

            $docInvoiceLegalMonetaryTotalArray = $docInvoiceArray['cac:LegalMonetaryTotal'];
            $docInvoiceLegalMonetaryTotal = new DocInvoiceLegalMonetaryTotal;
            $docInvoiceLegalMonetaryTotal->n_id_invoice = $docInvoiceId;
            $docInvoiceLegalMonetaryTotal->c_payable_amount = $docInvoiceLegalMonetaryTotalArray['cbc:PayableAmount'];
            $docInvoiceLegalMonetaryTotal->c_charge_total_amount = (isset(
                    $docInvoiceLegalMonetaryTotalArray['cbc:ChargeTotalAmount'])) ?
                $docInvoiceLegalMonetaryTotalArray['cbc:ChargeTotalAmount'] : NULL;
            $docInvoiceLegalMonetaryTotal->save();

            $docInvoiceBillingReferenceInvoiceDocumentReference = new DocInvoiceBillingReferenceInvoiceDocumentReference;
            $docInvoiceBillingReferenceInvoiceDocumentReference->n_id_invoice = $docInvoiceId;
            $docInvoiceBillingReferenceInvoiceDocumentReference->c_id = $array['CreditNote']['cac:BillingReference']
                ['cac:InvoiceDocumentReference']['cbc:ID'];
            $docInvoiceBillingReferenceInvoiceDocumentReference->c_document_type_code = (isset($array['CreditNote']
                    ['cac:BillingReference'] ['cac:InvoiceDocumentReference']['cbc:DocumentTypeCode'])) ? $array['CreditNote']
                ['cac:BillingReference']['cac:InvoiceDocumentReference']['cbc:DocumentTypeCode'] : NULL;
            $docInvoiceBillingReferenceInvoiceDocumentReference->save();

            if (isset($docInvoiceArray['cac:DespatchDocumentReference']) &&
                !empty($docInvoiceArray['cac:DespatchDocumentReference'])) {
                $docInvoiceDespatchDocumentReferenceArray = $docInvoiceArray['cac:DespatchDocumentReference'];
                foreach ($docInvoiceDespatchDocumentReferenceArray as $key => $value) {
                    $docInvoiceDespatchDocumentReference = new DocInvoiceDespatchDocumentReference();
                    $docInvoiceDespatchDocumentReference->n_id_invoice = $docInvoiceId;
                    $docInvoiceDespatchDocumentReference->c_id = $value['cbc:ID'];
                    $docInvoiceDespatchDocumentReference->c_document_type_code = $value['cbc:DocumentTypeCode'];
                    $docInvoiceDespatchDocumentReference->save();
                }
            }

            if (isset($docInvoiceArray['cac:AdditionalDocumentReference']) &&
                !empty($docInvoiceArray['cac:AdditionalDocumentReference'])) {
                $docInvoiceAdditionalDocumentReferenceArray = $docInvoiceArray['cac:AdditionalDocumentReference'];
                foreach ($docInvoiceAdditionalDocumentReferenceArray as $key => $value) {
                    $docInvoiceAdditionalDocumentReference = new DocInvoiceAdditionalDocumentReference();
                    $docInvoiceAdditionalDocumentReference->n_id_invoice = $docInvoiceId;
                    $docInvoiceAdditionalDocumentReference->c_id = $value['cbc:ID'];
                    $docInvoiceAdditionalDocumentReference->c_document_type_code = $value['cbc:DocumentTypeCode'];
                    $docInvoiceAdditionalDocumentReference->save();
                }
            }

            $docInvoiceCustomer = new DocInvoiceCustomer;
            $docInvoiceCustomer->n_id_invoice = $docInvoiceId;
            $docInvoiceCustomer->n_id_customer = $cusCustomerId;
            $docInvoiceCustomer->c_customer_assigned_account_id = $cusCustomerArray['cbc:CustomerAssignedAccountID'];
            $docInvoiceCustomer->c_additional_account_id = $cusCustomerArray['cbc:AdditionalAccountID'];
            $docInvoiceCustomer->c_party_party_legal_entity_registration_name = $cusCustomerArray['cac:Party']['cac:PartyLegalEntity']
                ['cbc:RegistrationName'];
            $docInvoiceCustomer->c_party_physical_location_description = (isset($cusCustomerArray['cac:Party']
                    ['cac:PhysicalLocation']['cbc:Description'])) ? $cusCustomerArray['cac:Party']['cac:PhysicalLocation']
                ['cbc:Description'] : NULL;
            $docInvoiceCustomer->save();

            $docInvoiceSupplier = new DocInvoiceSupplier;
            $docInvoiceSupplier->n_id_invoice = $docInvoiceId;
            $docInvoiceSupplier->n_id_supplier = $supSupplierId;
            $docInvoiceSupplier->c_party_party_legal_entity_registration_name = $supSupplierArray['cac:Party']
                ['cac:PartyLegalEntity']['cbc:RegistrationName'];
            $docInvoiceSupplier->c_party_name_name = (isset($supSupplierArray['cac:Party']['cac:PartyName']['cbc:Name'])) ?
                $supSupplierArray['cac:Party']['cac:PartyName']['cbc:Name'] : NULL;

            if (isset($supSupplierArray['cac:Party']['cac:PostalAddress']['cbc:ID'])) {
                $docInvoiceSupplier->c_party_postal_address_id = $supSupplierArray['cac:Party']['cac:PostalAddress']['cbc:ID'];
                $docInvoiceSupplier->c_party_postal_address_street_name = $supSupplierArray['cac:Party']['cac:PostalAddress']
                    ['cbc:StreetName'];
                $docInvoiceSupplier->c_party_postal_address_city_subdivision_name = $supSupplierArray['cac:Party']['cac:PostalAddress']
                    ['cbc:CitySubdivisionName'];
                $docInvoiceSupplier->c_party_postal_address_city_name = $supSupplierArray['cac:Party']['cac:PostalAddress']
                    ['cbc:CityName'];
                $docInvoiceSupplier->c_party_postal_address_country_subentity = $supSupplierArray['cac:Party']['cac:PostalAddress']
                    ['cbc:CountrySubentity'];
                $docInvoiceSupplier->c_party_postal_address_district = $supSupplierArray['cac:Party']['cac:PostalAddress']
                    ['cbc:District'];
                $docInvoiceSupplier->c_party_postal_address_country_identification_code = $supSupplierArray['cac:Party']
                    ['cac:PostalAddress']['cbc:Country']['cbc:IdentificationCode'];
                $docInvoiceSupplier->c_telephone = $supSupplier->c_telephone;
                $docInvoiceSupplier->c_email = $supSupplier->c_email;
                $docInvoiceSupplier->c_detraction_account = $supSupplier->c_detraction_account;
                $docInvoiceSupplier->c_sunat_bill_resolution = $supSupplier->c_sunat_bill_resolution;
                $docInvoiceSupplier->c_sunat_invoice_resolution = $supSupplier->c_sunat_invoice_resolution;
            }

            $docInvoiceSupplier->c_customer_assigned_account_id = $supSupplierArray['cbc:CustomerAssignedAccountID'];
            $docInvoiceSupplier->c_additional_account_id = $supSupplierArray['cbc:AdditionalAccountID'];
            $docInvoiceSupplier->c_party_name_name = (isset($supSupplierArray['cac:Party']['cac:PartyName']['cbc:Name'])) ?
                $supSupplierArray['cac:Party']['cac:PartyName']['cbc:Name'] : NULL;
            $docInvoiceSupplier->save();

            # ITEM

            $docInvoiceItemArray = $docInvoiceArray['cac:CreditNoteLine'];

            foreach ($docInvoiceItemArray as $key => $value) {
                $docInvoiceItem = new DocInvoiceItem();
                $docInvoiceItem->n_id_invoice = $docInvoiceId;
                if (isset($value['cbc:CreditedQuantity']['@unitCode']) &&
                    !empty($value['cbc:CreditedQuantity']['@unitCode'])) {
                    $docInvoiceItem->c_invoiced_quantity_unit_code = $value['cbc:CreditedQuantity']['@unitCode'];
                }
                if (isset($value['cbc:CreditedQuantity']['amount']) && !empty($value['cbc:CreditedQuantity']['amount'])) {
                    $docInvoiceItem->c_invoiced_quantity = $value['cbc:CreditedQuantity']['amount'];
                }
                if (isset($value['cac:Item']['cac:SellersItemIdentificacion']['cbc:ID']) &&
                    !empty($value['cac:Item']['cac:SellersItemIdentificacion']
                        ['cbc:ID'])) {
                    $docInvoiceItem->c_item_sellers_item_identification_id = $value['cac:Item']
                        ['cac:SellersItemIdentificacion']['cbc:ID'];
                }
                if (isset($value['cac:Price']['cbc:PriceAmount']) && !empty($value['cac:Price']['cbc:PriceAmount'])) {
                    $docInvoiceItem->c_price_price_amount = $value['cac:Price']['cbc:PriceAmount'];
                }
                if (isset($value['cbc:LineExtensionAmount']) && !empty($value['cbc:LineExtensionAmount'])) {
                    $docInvoiceItem->c_line_extension_amount = $value['cbc:LineExtensionAmount'];
                }

                $docInvoiceItem->n_id = $value['cbc:ID'];
                $docInvoiceItem->save();
                $docInvoiceItemId = $docInvoiceItem->n_id_invoice_item;

                if (isset($value['cac:Item']['cbc:Description']) && !empty($value['cac:Item']['cbc:Description'])) {
                    foreach ($value['cac:Item']['cbc:Description'] as $k => $v) {
                        $docInvoiceItemDescription = new DocInvoiceItemDescription();
                        $docInvoiceItemDescription->n_id_invoice_item = $docInvoiceItemId;
                        $docInvoiceItemDescription->n_index = $k;
                        $docInvoiceItemDescription->c_description = $v;
                        $docInvoiceItemDescription->save();
                    }
                }

                if (isset($value['cac:PricingReference']['cac:AlternativeConditionPrice'][0]['cbc:PriceAmount']) && !empty($value['cac:PricingReference']['cac:AlternativeConditionPrice'][0]['cbc:PriceAmount'])) {
                    foreach ($value['cac:PricingReference']['cac:AlternativeConditionPrice'] as $k => $v) {
                        $docInvoiceItemPricingReferenceAlternativeConditionPrice = new
                            DocInvoiceItemPricingReferenceAlternativeConditionPrice;
                        $docInvoiceItemPricingReferenceAlternativeConditionPrice->n_id_invoice_item = $docInvoiceItemId;
                        $docInvoiceItemPricingReferenceAlternativeConditionPrice->c_price_amount = $v['cbc:PriceAmount'];
                        $docInvoiceItemPricingReferenceAlternativeConditionPrice->c_price_type_code = $v['cbc:PriceTypeCode'];
                        $docInvoiceItemPricingReferenceAlternativeConditionPrice->save();
                    }
                }

                if (isset($value['cac:TaxTotal']) && !empty($value['cac:TaxTotal'])) {
                    foreach ($value['cac:TaxTotal'] as $k => $v) {
                        $docInvoiceItemTaxTotal = new DocInvoiceItemTaxTotal;
                        $docInvoiceItemTaxTotal->n_id_invoice_item = $docInvoiceItemId;
                        $docInvoiceItemTaxTotal->c_tax_amount = $v['cbc:TaxAmount'];
                        $docInvoiceItemTaxTotal->save();
                        $docInvoiceItemTaxTotalId = $docInvoiceItemTaxTotal->n_id_invoice_item_tax_total;

                        $docInvoiceItemTaxTotalTaxSubtotal = new DocInvoiceItemTaxTotalTaxSubtotal;
                        $docInvoiceItemTaxTotalTaxSubtotal->n_id_invoice_item_tax_total = $docInvoiceItemTaxTotalId;
                        $docInvoiceItemTaxTotalTaxSubtotal->c_tax_amount = $v['cac:TaxSubtotal']['cbc:TaxAmount'];
                        $docInvoiceItemTaxTotalTaxSubtotal->save();

                        $docInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme = new DocInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme;
                        $docInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme->n_id_invoice_item_tax_total = $docInvoiceItemTaxTotalId;
                        $docInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme->c_id = $v['cac:TaxSubtotal']['cac:TaxCategory']
                            ['cac:TaxScheme']['cbc:ID'];
                        $docInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme->c_name = $v['cac:TaxSubtotal']['cac:TaxCategory']
                            ['cac:TaxScheme']['cbc:Name'];
                        $docInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme->c_tax_type_code = $v['cac:TaxSubtotal']
                            ['cac:TaxCategory']['cac:TaxScheme']['cbc:TaxTypeCode'];
                        $docInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme->save();

                        if (isset($v['cac:TaxSubtotal']['cac:TaxCategory']['cbc:TaxExemptionReasonCode'])) {
                            $docInvoiceItemTaxTotalIgv = new DocInvoiceItemTaxTotalIgv;
                            $docInvoiceItemTaxTotalIgv->n_id_invoice_item_tax_total = $docInvoiceItemTaxTotalId;
                            $docInvoiceItemTaxTotalIgv->c_tax_subtotal_tax_category_tax_exemption_reason_code = $v
                                ['cac:TaxSubtotal']['cac:TaxCategory']['cbc:TaxExemptionReasonCode'];
                            $docInvoiceItemTaxTotalIgv->save();
                        }

                        if (isset($v['cac:TaxSubtotal']['cac:TaxCategory']['cbc:TierRange'])) {
                            $docInvoiceItemTaxTotalIsc = new DocInvoiceItemTaxTotalIsc;
                            $docInvoiceItemTaxTotalIsc->n_id_invoice_item_tax_total = $docInvoiceItemTaxTotalId;
                            $docInvoiceItemTaxTotalIsc->c_tax_subtotal_tax_category_tier_range = $v
                                ['cac:TaxSubtotal']['cac:TaxCategory']['cbc:TierRange'];
                            $docInvoiceItemTaxTotalIsc->save();
                        }
                    }
                }
            }

            # Datos Extra-Oficiales
            if (isset($docInvoiceArray['ExtraOficial'])) {
                $extraOficial = $docInvoiceArray['ExtraOficial'];
                $docInvoicePdfData = new DocInvoicePdfData();
                $docInvoicePdfData->n_id_invoice = $docInvoiceId;
                $docInvoicePdfData->c_purchase_order = isset($extraOficial['OrdenDeCompra']) ? $extraOficial['OrdenDeCompra'] : NULL;
                $docInvoicePdfData->n_terms_of_payment = isset($extraOficial['CondicionesDePago']) ? $extraOficial['CondicionesDePago'] : null;
                $docInvoicePdfData->d_expiration_date = isset($extraOficial['FechaDeVencimiento']) ? date('Y-m-d',
                        strtotime($extraOficial['FechaDeVencimiento'])) : null;
                $docInvoicePdfData->c_observation = isset($extraOficial['Observacion']) ? $extraOficial['Observacion'] : NULL;
                $docInvoicePdfData->c_customer_address = isset($extraOficial['ClienteDireccion']) ? $extraOficial['ClienteDireccion'] : NULL;
                $docInvoicePdfData->save();

                $docInvoiceExtraData = new DocInvoiceExtraData();
                $docInvoiceExtraData->n_id_invoice = $docInvoiceId;
                $docInvoiceExtraData->c_customer_email = isset($extraOficial['ClienteCorreo']) ? $extraOficial['ClienteCorreo'] : NULL;
                $docInvoiceExtraData->c_exchange_rate = $extraOficial['TipoDeCambio'];
                $docInvoiceExtraData->save();

                if (isset($extraOficial['Custom']) && !empty($extraOficial['Custom'])) {
                    $custom = $extraOficial['Custom'];
                    foreach ($custom as $key => $value) {
                        $docInvoicePdfDataCustom = new DocInvoicePdfDataCustom();
                        $docInvoicePdfDataCustom->n_id_invoice = $docInvoiceId;
                        $docInvoicePdfDataCustom->n_index = $key;
                        $docInvoicePdfDataCustom->c_name = $value['c_name'];
                        $docInvoicePdfDataCustom->c_value = $value['c_value'];
                        $docInvoicePdfDataCustom->save();
                    }
                }
            }

            $response['id'] = $docInvoiceId;
            $response['status'] = 1;

            Log::info('Grabado en BD',
                [
                'lgph_id' => 3, 'c_id' => $array['CreditNote']['cbc:ID'], 'c_invoice_type_code' => '07',
                'n_id_invoice' => $docInvoiceId,
                ]
            );
        } catch (Exception $exc) {
            Log::error($exc->getMessage(),
                [
                'lgph_id' => 3, 'c_id' => $array['CreditNote']['cbc:ID'], 'c_invoice_type_code' => '07',
                ]
            );
            $response['message'] = $exc->getMessage();
        }

        return $response;
    }

    /**
     * Arma el XML desde el Id del Documento, retorna un bool STATUS y string MESSAGE.
     * 
     * @param int $invoiceId
     * @return array
     */
    public static function buildXml($creditNoteId)
    {
        $response['status'] = 0;
        $response['message'] = '';
        $response['path'] = '';

        try {
            $docInvoiceData = DocInvoice::with('DocInvoiceSupplier', 'DocInvoiceDiscrepancyResponse',
                    'DocInvoiceCustomer', 'DocInvoiceAdditionalInformationAdditionalMonetaryTotal',
                    'DocInvoiceAdditionalInformationAdditionalProperty', 'DocInvoiceDespatchDocumentReference',
                    'DocInvoiceAdditionalDocumentReference', 'DocInvoiceSupplier', 'DocInvoiceCustomer',
                    'DocInvoiceTaxTotal', 'DocInvoiceLegalMonetaryTotal', 'DocInvoiceItem')->where('n_id_invoice',
                $creditNoteId);

            if ($docInvoiceData->count() == 0) {
                throw new Exception('No se encuentra en BD');
            }

            $docInvoiceData = $docInvoiceData->first();
            $signatureId = 'S' . $docInvoiceData->c_id;
            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->xmlStandalone = false;
            $dom->formatOutput = true;

            $CreditNote = $dom->createElement('CreditNote');
            $newNode = $dom->appendChild($CreditNote);
            $newNode->setAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2');
            $newNode->setAttribute('xmlns:cac',
                'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $newNode->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $newNode->setAttribute('xmlns:ccts', 'urn:un:unece:uncefact:documentation:2');
            $newNode->setAttribute('xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');
            $newNode->setAttribute('xmlns:ext',
                'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
            $newNode->setAttribute('xmlns:qdt', 'urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2');
            $newNode->setAttribute('xmlns:sac',
                'urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1');
            $newNode->setAttribute('xmlns:udt',
                'urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2');
            $newNode->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

            $ublExtensions = $dom->createElement('ext:UBLExtensions');
            $CreditNote->appendChild($ublExtensions);

            # sac:AdditionalInformation
            $docInvoiceAdditionalInformationAdditionalMonetaryTotal = $docInvoiceData
                ->docInvoiceAdditionalInformationAdditionalMonetaryTotal;
            $docInvoiceAdditionalInformationAdditionalProperty = $docInvoiceData
                ->DocInvoiceAdditionalInformationAdditionalProperty;
            if (!is_null($docInvoiceAdditionalInformationAdditionalProperty) ||
                !is_null($docInvoiceAdditionalInformationAdditionalMonetaryTotal)) {
                $ublExtension = $dom->createElement('ext:UBLExtension');
                $ublExtensions->appendChild($ublExtension);
                $extensionContent = $dom->createElement('ext:ExtensionContent');
                $ublExtension->appendChild($extensionContent);
                $additionalInformation = $dom->createElement('sac:AdditionalInformation');
                $extensionContent->appendChild($additionalInformation);
            }

            # DocInvoiceAdditionalInformationAdditionalMonetaryTotal
            $docInvoiceAdditionalInformationAdditionalMonetaryTotal = $docInvoiceData
                ->DocInvoiceAdditionalInformationAdditionalMonetaryTotal;
            if (!is_null($docInvoiceAdditionalInformationAdditionalMonetaryTotal)) {
                foreach ($docInvoiceAdditionalInformationAdditionalMonetaryTotal as $key => $value) {
                    $additionalMonetaryTotal = $dom->createElement('sac:AdditionalMonetaryTotal');
                    $additionalInformation->appendChild($additionalMonetaryTotal);
                    $id = $dom->createElement('cbc:ID');
                    $id->nodeValue = $value->c_id;
                    $additionalMonetaryTotal->appendChild($id);
                    if (isset($value->c_payable_amount) && !empty($value->c_payable_amount)) {
                        $payableAmount = $dom->createElement('cbc:PayableAmount');
                        $payableAmount->nodeValue = $value->c_payable_amount;
                        $currencyId = $dom->createAttribute('currencyID');
                        $currencyId->value = $docInvoiceData->c_document_currency_code;
                        $payableAmount->appendChild($currencyId);
                        $additionalMonetaryTotal->appendChild($payableAmount);
                    }
                }
            }

            # DocInvoiceAdditionalInformationAdditionalProperty
            if (!is_null($docInvoiceAdditionalInformationAdditionalProperty)) {
                foreach ($docInvoiceAdditionalInformationAdditionalProperty as $key => $value) {
                    $additionalProperty = $dom->createElement('sac:AdditionalProperty');
                    $additionalInformation->appendChild($additionalProperty);
                    $id = $dom->createElement('cbc:ID');
                    $id->nodeValue = $value->c_id;
                    $additionalProperty->appendChild($id);
                    if (isset($value->c_value) && !empty($value->c_value)) {
                        $val = $dom->createElement('cbc:Value');
                        $val->nodeValue = $value->c_value;
                        $additionalProperty->appendChild($val);
                    }
                    if (isset($value->c_name) && !empty($value->c_name)) {
                        $name = $dom->createElement('cbc:Name');
                        $name->nodeValue = $value->c_name;
                        $additionalProperty->appendChild($name);
                    }
                }
            }

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
            

            # DocInvoice
            $ublVersionID = $dom->createElement('cbc:UBLVersionID');
            $ublVersionID->nodeValue = $docInvoiceData->c_ubl_version_id;
            $CreditNote->appendChild($ublVersionID);
            $customizationID = $dom->createElement('cbc:CustomizationID');
            $customizationID->nodeValue = $docInvoiceData->c_customization_id;
            $CreditNote->appendChild($customizationID);
            $id = $dom->createElement('cbc:ID');
            $id->nodeValue = $docInvoiceData->c_id;
            $CreditNote->appendChild($id);
            $issueDate = $dom->createElement('cbc:IssueDate');
            $issueDate->nodeValue = $docInvoiceData->d_issue_date;
            $CreditNote->appendChild($issueDate);
            $documentCurrencyCode = $dom->createElement('cbc:DocumentCurrencyCode');
            $documentCurrencyCode->nodeValue = $docInvoiceData->c_document_currency_code;
            $CreditNote->appendChild($documentCurrencyCode);

            # DocInvoiceDiscrepancyresponse
            $docInvoiceDiscrepancyResponseData = $docInvoiceData->docInvoiceDiscrepancyResponse;
            $discrepancyResponse = $dom->createElement('cac:DiscrepancyResponse');
            $CreditNote->appendChild($discrepancyResponse);
            $referenceID = $dom->createElement('cbc:ReferenceID', $docInvoiceDiscrepancyResponseData->c_reference_id);
            $discrepancyResponse->appendChild($referenceID);
            $responseCode = $dom->createElement('cbc:ResponseCode', $docInvoiceDiscrepancyResponseData->c_response_code);
            $discrepancyResponse->appendChild($responseCode);
            $description = $dom->createElement('cbc:Description');
            $description->appendChild($dom->createCDATASection($docInvoiceDiscrepancyResponseData->c_description));
            $discrepancyResponse->appendChild($description);

            # DocBillingReference
            $docInvoiceBillingReferenceInvoiceDocumentReferenceData = $docInvoiceData->docInvoiceBillingReferenceInvoiceDocumentReference;
            $billingReference = $dom->createElement('cac:BillingReference');
            $CreditNote->appendChild($billingReference);
            $invoiceDocumentReference = $dom->createElement('cac:InvoiceDocumentReference');
            $billingReference->appendChild($invoiceDocumentReference);
            $id = $dom->createElement('cbc:ID', $docInvoiceBillingReferenceInvoiceDocumentReferenceData->c_id);
            $invoiceDocumentReference->appendChild($id);
            if (isset($docInvoiceBillingReferenceInvoiceDocumentReferenceData->c_document_type_code) &&
                !empty($docInvoiceBillingReferenceInvoiceDocumentReferenceData->c_document_type_code)) {
                $documentTypeCode = $dom->createElement('cbc:DocumentTypeCode',
                    $docInvoiceBillingReferenceInvoiceDocumentReferenceData
                    ->c_document_type_code);
                $invoiceDocumentReference->appendChild($documentTypeCode);
            }

            # DocInvoiceDespatchDocumentReference
            $docInvoiceDespatchDocumentReference = $docInvoiceData->DocInvoiceDespatchDocumentReference;
            if ($docInvoiceDespatchDocumentReference->count()) {
                foreach ($docInvoiceDespatchDocumentReference as $key => $value) {
                    $despatchDocumentReference = $dom->createElement('cac:DespatchDocumentReference');
                    $CreditNote->appendChild($despatchDocumentReference);
                    $id = $dom->createElement('cbc:ID');
                    $id->nodeValue = $value->c_id;
                    $documentTypeCode = $dom->createElement('cbc:DocumentTypeCode');
                    $documentTypeCode->nodeValue = $value->c_document_type_code;
                    $despatchDocumentReference->appendChild($id);
                    $despatchDocumentReference->appendChild($documentTypeCode);
                }
            }

            # DocInvoiceAdditionalDocumentReference
            $docInvoiceAdditionalDocumentReference = $docInvoiceData->DocInvoiceAdditionalDocumentReference;
            if ($docInvoiceAdditionalDocumentReference) {
                foreach ($docInvoiceAdditionalDocumentReference as $key => $value) {
                    $additionalDocumentReference = $dom->createElement('cac:AdditionalDocumentReference');
                    $CreditNote->appendChild($additionalDocumentReference);
                    $id = $dom->createElement('cbc:ID', $value->c_id);
                    $documentTypeCode = $dom->createElement('cbc:DocumentTypeCode');
                    $documentTypeCode->nodeValue = $value->c_document_type_code;
                    $additionalDocumentReference->appendChild($id);
                    $additionalDocumentReference->appendChild($documentTypeCode);
                }
            }

            # Firma
            $signature = $dom->createElement('cac:Signature');
            $CreditNote->appendChild($signature);
            $id = $dom->createElement('cbc:ID', $signatureId);
            $signature->appendChild($id);
            $signatoryParty = $dom->createElement('cac:SignatoryParty');
            $signature->appendChild($signatoryParty);
            $partyIdentification = $dom->createElement('cac:PartyIdentification');
            $signatoryParty->appendChild($partyIdentification);
            $id = $dom->createElement('cbc:ID', $docInvoiceData->DocInvoiceSupplier->c_customer_assigned_account_id);
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
            $docInvoiceSupplier = $docInvoiceData->docInvoiceSupplier;
            $accountingSupplierParty = $dom->createElement('cac:AccountingSupplierParty');
            $CreditNote->appendChild($accountingSupplierParty);
            $customerAssignedAccountId = $dom->createElement('cbc:CustomerAssignedAccountID',
                $docInvoiceSupplier
                ->c_customer_assigned_account_id);
            $accountingSupplierParty->appendChild($customerAssignedAccountId);
            $additionalAccountId = $dom->createElement('cbc:AdditionalAccountID',
                $docInvoiceSupplier
                ->c_additional_account_id);
            $accountingSupplierParty->appendChild($additionalAccountId);
            $party = $dom->createElement('cac:Party');
            $accountingSupplierParty->appendChild($party);

            if (isset($docInvoiceSupplier->c_party_name_name) && !empty($docInvoiceSupplier->c_party_name_name)) {
                $partyName = $dom->createElement('cac:PartyName');
                $party->appendChild($partyName);
                $name = $dom->createElement('cbc:Name');
                $name->appendChild($dom->createCDATASection($docInvoiceSupplier->c_party_name_name));
                $partyName->appendChild($name);
            }

            if (isset($docInvoiceSupplier->c_party_postal_address_id) &&
                !empty($docInvoiceSupplier->c_party_postal_address_id)) {
                $postalAddress = $dom->createElement('cac:PostalAddress');
                $party->appendChild($postalAddress);

                $id = $dom->createElement('cbc:ID');
                $id->nodeValue = $docInvoiceSupplier->c_party_postal_address_id;
                $streetName = $dom->createElement('cbc:StreetName');
                $streetName->appendChild($dom->createCDATASection($docInvoiceSupplier
                        ->c_party_postal_address_street_name));
                $citySubdivisionName = $dom->createElement('cbc:CitySubdivisionName');
                $citySubdivisionName->appendChild($dom->createCDATASection($docInvoiceSupplier
                        ->c_party_postal_address_city_subdivision_name));
                $cityName = $dom->createElement('cbc:CityName');
                $cityName->appendChild($dom->createCDATASection($docInvoiceSupplier->c_party_postal_address_city_name));
                $countrySubentity = $dom->createElement('cbc:CountrySubentity');
                $countrySubentity->appendChild($dom->createCDATASection($docInvoiceSupplier
                        ->c_party_postal_address_country_subentity));
                $district = $dom->createElement('cbc:District');
                $district->appendChild($dom->createCDATASection($docInvoiceSupplier->c_party_postal_address_district));
                $country = $dom->createElement('cac:Country');
                $identificationCode = $dom->createElement('cbc:IdentificationCode');
                $identificationCode->appendChild($dom->createCDATASection($docInvoiceSupplier
                        ->c_party_postal_address_country_identification_code));
                $postalAddress->appendChild($id);
                $postalAddress->appendChild($streetName);
                $postalAddress->appendChild($citySubdivisionName);
                $postalAddress->appendChild($cityName);
                $postalAddress->appendChild($countrySubentity);
                $postalAddress->appendChild($district);
                $postalAddress->appendChild($country);
                $country->appendChild($identificationCode);
            }

            $partyLegalEntity = $dom->createElement('cac:PartyLegalEntity');
            $party->appendChild($partyLegalEntity);
            $registrationName = $dom->createElement('cbc:RegistrationName');
            $registrationName->appendChild($dom->createCDATASection($docInvoiceSupplier->c_party_party_legal_entity_registration_name));
            $partyLegalEntity->appendChild($registrationName);

            # DocInvoiceCustomer
            $docInvoiceCustomerData = $docInvoiceData->docInvoiceCustomer;
            $accountingCustomerParty = $dom->createElement('cac:AccountingCustomerParty');
            $CreditNote->appendChild($accountingCustomerParty);
            $customerAssignedAccountId = $dom->createElement('cbc:CustomerAssignedAccountID');
            $customerAssignedAccountId->nodeValue = $docInvoiceCustomerData->c_customer_assigned_account_id;
            $accountingCustomerParty->appendChild($customerAssignedAccountId);
            $additionalAccountId = $dom->createElement('cbc:AdditionalAccountID');
            $additionalAccountId->nodeValue = $docInvoiceCustomerData->c_additional_account_id;
            $accountingCustomerParty->appendChild($additionalAccountId);

            $party = $dom->createElement('cac:Party');
            $accountingCustomerParty->appendChild($party);

            if (isset($docInvoiceCustomerData->c_party_physical_location_description) &&
                !empty($docInvoiceCustomerData->c_party_physical_location_description)) {
                $physicalLocation = $dom->createElement('cac:PhysicalLocation');
                $party->appendChild($physicalLocation);
                $description = $dom->createElement('cbc:Description');
                $description->appendChild($dom->createCDATASection($docInvoiceCustomerData
                        ->c_party_physical_location_description));
                $physicalLocation->appendChild($description);
            }

            $partyLegalEntity = $dom->createElement('cac:PartyLegalEntity');
            $party->appendChild($partyLegalEntity);
            $registrationName = $dom->createElement('cbc:RegistrationName');
            $registrationName->appendChild($dom->createCDATASection($docInvoiceCustomerData
                    ->c_party_party_legal_entity_registration_name));
            $partyLegalEntity->appendChild($registrationName);

            # DocInvoiceTaxTotal
            $docInvoiceTaxTotalData = $docInvoiceData->docInvoiceTaxTotal;
            if (!is_null($docInvoiceTaxTotalData)) {
                foreach ($docInvoiceTaxTotalData as $key => $value) {
                    $taxTotal = $dom->createElement('cac:TaxTotal');
                    $CreditNote->appendChild($taxTotal);
                    $taxAmount = $dom->createElement('cbc:TaxAmount', $value->c_tax_amount);
                    $currencyId = $dom->createAttribute('currencyID');
                    $currencyId->nodeValue = $docInvoiceData->c_document_currency_code;
                    $taxAmount->appendChild($currencyId);
                    $taxTotal->appendChild($taxAmount);

                    $docInvoiceTaxTotalTaxSubtotalData = DocInvoiceTaxTotalTaxSubtotal::find($value->n_id_invoice_tax_total);
                    $taxSubtotal = $dom->createElement('cac:TaxSubtotal');
                    $taxTotal->appendChild($taxSubtotal);
                    $taxAmount = $dom->createElement('cbc:TaxAmount', $docInvoiceTaxTotalTaxSubtotalData->c_tax_amount);
                    $currencyId = $dom->createAttribute('currencyID');
                    $currencyId->nodeValue = $docInvoiceData->c_document_currency_code;
                    $taxAmount->appendChild($currencyId);
                    $taxSubtotal->appendChild($taxAmount);

                    $taxCategory = $dom->createElement('cac:TaxCategory');
                    $taxSubtotal->appendChild($taxCategory);
                    $taxScheme = $dom->createElement('cac:TaxScheme');
                    $taxCategory->appendChild($taxScheme);

                    $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxSchemeData = DocInvoiceTaxTotalTaxSubtotalTaxCategoryTaxScheme::find($value->n_id_invoice_tax_total);
                    $id = $dom->createElement('cbc:ID', $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxSchemeData->c_id);
                    $name = $dom->createElement('cbc:Name',
                        $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxSchemeData->c_name);
                    $taxTypeCode = $dom->createElement('cbc:TaxTypeCode',
                        $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxSchemeData->c_tax_type_code);
                    $taxScheme->appendChild($id);
                    $taxScheme->appendChild($name);
                    $taxScheme->appendChild($taxTypeCode);
                }
            }

            # DocInvoiceLegalMonetaryTotal
            $docInvoiceLegalMonetaryTotalData = $docInvoiceData->docInvoiceLegalMonetaryTotal;
            if (!is_null($docInvoiceLegalMonetaryTotalData)) {
                $legalMonetaryTotal = $dom->createElement('cac:LegalMonetaryTotal');
                $CreditNote->appendChild($legalMonetaryTotal);

                $payableAmount = $dom->createElement('cbc:PayableAmount');
                $payableAmount->nodeValue = $docInvoiceLegalMonetaryTotalData->c_payable_amount;
                $legalMonetaryTotal->appendChild($payableAmount);
                $currencyId = $dom->createAttribute('currencyID');
                $currencyId->value = $docInvoiceData->c_document_currency_code;
                $payableAmount->appendChild($currencyId);

                if (isset($docInvoiceLegalMonetaryTotalData->c_charge_total_amount) &&
                    !empty($docInvoiceLegalMonetaryTotalData->c_charge_total_amount)) {
                    $chargeTotalAmount = $dom->createElement('cbc:ChargeTotalAmount');
                    $chargeTotalAmount->nodeValue = $docInvoiceLegalMonetaryTotalData->c_charge_total_amount;
                    $currencyId = $dom->createAttribute('currencyID');
                    $currencyId->value = $docInvoiceData->c_document_currency_code;
                    $chargeTotalAmount->appendChild($currencyId);
                    $legalMonetaryTotal->appendChild($chargeTotalAmount);
                }
            }

            # DocInvoiceItems
            $docInvoiceItemData = $docInvoiceData->docInvoiceItem;
            foreach ($docInvoiceItemData as $key => $value) {
                $invoiceLine = $dom->createElement('cac:CreditNoteLine');
                $CreditNote->appendChild($invoiceLine);

                $id = $dom->createElement('cbc:ID');
                $id->nodeValue = $value->n_id;
                $invoiceLine->appendChild($id);

                if (isset($value->c_invoiced_quantity) && !empty($value->c_invoiced_quantity)) {
                    $creditedQuantity = $dom->createElement('cbc:CreditedQuantity');
                    $creditedQuantity->nodeValue = $value->c_invoiced_quantity;
                    $unitCode = $dom->createAttribute('unitCode');
                    $unitCode->value = $value->c_invoiced_quantity_unit_code;
                    $creditedQuantity->appendChild($unitCode);
                    $invoiceLine->appendChild($creditedQuantity);
                }

                if (isset($value->c_line_extension_amount) && !empty($value->c_line_extension_amount)) {
                    $lineExtensionAmount = $dom->createElement('cbc:LineExtensionAmount');
                    $lineExtensionAmount->nodeValue = $value->c_line_extension_amount;
                    $currencyId = $dom->createAttribute('currencyID');
                    $currencyId->value = $docInvoiceData->c_document_currency_code;
                    $lineExtensionAmount->appendChild($currencyId);
                    $invoiceLine->appendChild($lineExtensionAmount);
                }

                $docInvoiceItemPricingReferenceAlternativeConditionPriceData = $value
                    ->docInvoiceItemPricingReferenceAlternativeConditionPrice;

                if ($docInvoiceItemPricingReferenceAlternativeConditionPriceData->count()) {
                    $pricingReference = $dom->createElement('cac:PricingReference');
                    $invoiceLine->appendChild($pricingReference);
                    foreach ($docInvoiceItemPricingReferenceAlternativeConditionPriceData as $k => $v) {
                        $alternativeConditionPrice = $dom->createElement('cac:AlternativeConditionPrice');
                        $priceAmount = $dom->createElement('cbc:PriceAmount', $v->c_price_amount);
                        $currencyId = $dom->createAttribute('currencyID');
                        $currencyId->nodeValue = $docInvoiceData->c_document_currency_code;
                        $priceAmount->appendChild($currencyId);
                        $priceTypeCode = $dom->createElement('cbc:PriceTypeCode', $v->c_price_type_code);
                        $pricingReference->appendChild($alternativeConditionPrice);
                        $alternativeConditionPrice->appendChild($priceAmount);
                        $alternativeConditionPrice->appendChild($priceTypeCode);
                    }
                }

                $docInvoiceItemTaxTotalData = $value->DocInvoiceItemTaxTotal;
                if ($docInvoiceItemTaxTotalData->count()) {
                    foreach ($docInvoiceItemTaxTotalData as $k => $v) {
                        $taxTotal = $dom->createElement('cac:TaxTotal');
                        $invoiceLine->appendChild($taxTotal);
                        $taxAmount = $dom->createElement('cbc:TaxAmount', $v->c_tax_amount);
                        $currencyId = $dom->createAttribute('currencyID');
                        $currencyId->nodeValue = $docInvoiceData->c_document_currency_code;
                        $taxAmount->appendChild($currencyId);
                        $taxTotal->appendChild($taxAmount);

                        $taxSubtotal = $dom->createElement('cac:TaxSubtotal');
                        $taxTotal->appendChild($taxSubtotal);
                        $docInvoiceItemTaxTotalTaxSubtotal = $v->docInvoiceItemTaxTotalTaxSubtotal;
                        $taxAmount = $dom->createElement('cbc:TaxAmount',
                            $docInvoiceItemTaxTotalTaxSubtotal->c_tax_amount);
                        $currencyId = $dom->createAttribute('currencyID');
                        $currencyId->nodeValue = $docInvoiceData->c_document_currency_code;
                        $taxAmount->appendChild($currencyId);
                        $taxSubtotal->appendChild($taxAmount);

                        $taxCategory = $dom->createElement('cac:TaxCategory');
                        $taxSubtotal->appendChild($taxCategory);

                        $docInvoiceItemTaxTotalIgv = $v->DocInvoiceItemTaxTotalIgv;
                        if (!is_null($docInvoiceItemTaxTotalIgv)) {
                            $taxExemptionReasonCode = $dom->createElement('cbc:TaxExemptionReasonCode',
                                $docInvoiceItemTaxTotalIgv->c_tax_subtotal_tax_category_tax_exemption_reason_code);
                            $taxCategory->appendChild($taxExemptionReasonCode);
                        }

                        $docInvoiceItemTaxTotalIsc = $v->DocInvoiceItemTaxTotalIsc;
                        if (!is_null($docInvoiceItemTaxTotalIsc)) {
                            $tierRange = $dom->createElement('cbc:TierRange',
                                $docInvoiceItemTaxTotalIsc->c_tax_subtotal_tax_category_tier_range);
                            $taxCategory->appendChild($tierRange);
                        }

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

                $docInvoiceItemDescription = $value->DocInvoiceItemDescription;
                if (!is_null($docInvoiceItemDescription) ||
                    (isset($value->c_item_sellers_item_identification_id) && !empty($value->c_item_sellers_item_identification_id))) {
                    $item = $dom->createElement('cac:Item');
                    $invoiceLine->appendChild($item);
                }

                if (!is_null($docInvoiceItemDescription)) {
                    foreach ($docInvoiceItemDescription as $k => $v) {
                        $description = $dom->createElement('cbc:Description');
                        $description->appendChild($dom->createCDATASection($v->c_description));
                        $item->appendChild($description);
                    }
                }

                if (isset($value->c_item_sellers_item_identification_id) && !empty($value->c_item_sellers_item_identification_id)) {
                    $sellersItemIdentification = $dom->createElement('cac:SellersItemIdentification');
                    $id = $dom->createElement('cbc:ID');
                    $id->nodeValue = $value->c_item_sellers_item_identification_id;
                    $item->appendChild($sellersItemIdentification);
                    $sellersItemIdentification->appendChild($id);
                }

                if (isset($value->c_price_price_amount) && !empty($value->c_price_price_amount)) {
                    $price = $dom->createElement('cac:Price');
                    $invoiceLine->appendChild($price);
                    $priceAmount = $dom->createElement('cbc:PriceAmount');
                    $priceAmount->nodeValue = $value->c_price_price_amount;
                    $currencyId = $dom->createAttribute('currencyID');
                    $currencyId->value = $docInvoiceData->c_document_currency_code;
                    $priceAmount->appendChild($currencyId);
                    $price->appendChild($priceAmount);
                }
            }

            $xmlName = sprintf('%s-%s-%s', $docInvoiceSupplier->c_customer_assigned_account_id, '07',
                $docInvoiceData->c_id);
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
                    $objDSig->sign($objKey,$doc->getElementsByTagName($ReferenceNodeName)->item(1));

                    // Add the associated public key to the signature
                    $objDSig->add509Cert(file_get_contents($publicKey));
                    //$objDSig->add509Cert(file_get_contents('certificates/ServerCertificate.cer'));
                    // Append the signature to the XML
                    //die(var_dump($doc->documentElement));
                    $objDSig->appendSignature($doc->getElementsByTagName($ReferenceNodeName)->item(1));
                    //$objDSig->appendSignature($ReferenceNodeName);
                    // Save the signed XML
                    $doc->save($xmlFullPath);


                }


                # Guardando datos de firma del documento
                $ds = UtilHelper::readSignatureXml($xmlFullPath);
                if (isset($ds) && !empty($ds)) {
                    $docInvoiceSignature = new DocInvoiceSignature();
                    $docInvoiceSignature->n_id_invoice = $creditNoteId;
                    $docInvoiceSignature->c_signature_value = preg_replace('/\s+/', '', $ds['SignatureValue']);
                    $docInvoiceSignature->c_digest_value = $ds['DigestValue'];
                    $docInvoiceSignature->save();
                }

            }

            $zipPath = $docInvoiceData->SupSupplier->SupSupplierConfiguration->c_public_path_document . DIRECTORY_SEPARATOR . $xmlName . '.ZIP';
            $zipFullPath = public_path($zipPath);

            # DocInvoiceFile
            $docInvoiceFile = new DocInvoiceFile();
            $docInvoiceFile->n_id_invoice = $creditNoteId;
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

            file_exists($zipFullPath) ? unlink($zipFullPath) : '';
            \Zipper::make($zipFullPath)->add($xmlFullPath)->close();
            unlink($xmlFullPath);

            $response['status'] = 1;
            $response['path'] = $zipFullPath;
            chmod($zipFullPath, 0777);

            Log::info('Generación de XML',
                [
                'lgph_id' => 4, 'n_id_invoice' => $creditNoteId, 'c_invoice_type_code' => '07'
                ]
            );
        } catch (Exception $exc) {
            Log::error($exc->getMessage(),
                [
                'lgph_id' => 4, 'n_id_invoice' => $creditNoteId, 'c_invoice_type_code' => '07'
                ]
            );
            $response['message'] = $exc->getMessage();
        }
        return $response;
    }

}
