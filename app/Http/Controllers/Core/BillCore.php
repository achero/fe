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

use App\Http\Controllers\Traits\UtilHelper;

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Boleta de venta
 */
class BillCore
{

    use UtilHelper;        

    /**
     * Lee un archivo de texto y lo convierte en array
     * @param array $file
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
            $count['Invoice']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']['sac:AdditionalInformation']
                ['sac:AdditionalMonetaryTotal'] = 0;
            $count['Invoice']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']['sac:AdditionalInformation']
                ['sac:AdditionalProperty'] = 0;
            $count['Invoice']['cac:TaxTotal'] = 0;
            $count['Invoice']['cac:InvoiceLine'] = 0;
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
                                /* Fecha de Emisión */
                                case 0:
                                    $response['Invoice']['cbc:IssueDate'] = $v;
                                    break;
                                /* Apellidos y nombres, denominación o razón social */
                                case 1:
                                    $response['Invoice']['cac:AccountingSupplierParty']['cac:Party']['cac:PartyLegalEntity']
                                        ['cbc:RegistrationName'] = $v;
                                    break;
                                /* Nombre Comercial */
                                case 2:
                                    if (isset($v) && !empty($v)) {
                                        $response['Invoice']['cac:AccountingSupplierParty']['cac:Party']
                                            ['cac:PartyName']['cbc:Name'] = $v;
                                    }
                                    break;
                                /* Domicilio Fiscal */
                                case 3:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {
                                                /* Codigo de Ubigeo - Catalago No 13 */
                                                case 0:
                                                    $response['Invoice']['cac:AccountingSupplierParty']['cac:Party']
                                                        ['cac:PostalAddress']['cbc:ID'] = $va;
                                                    break;
                                                /* Direccion completa y detallada */
                                                case 1:
                                                    $response['Invoice']['cac:AccountingSupplierParty']['cac:Party']
                                                        ['cac:PostalAddress']['cbc:StreetName'] = $va;
                                                    break;
                                                /* Urbanizacion */
                                                case 2:
                                                    $response['Invoice']['cac:AccountingSupplierParty']['cac:Party']
                                                        ['cac:PostalAddress']['cbc:CitySubdivisionName'] = $va;
                                                    break;
                                                /* Provincia */
                                                case 3:
                                                    $response['Invoice']['cac:AccountingSupplierParty']['cac:Party']
                                                        ['cac:PostalAddress']['cbc:CityName'] = $va;
                                                    break;
                                                /* Departamento */
                                                case 4:
                                                    $response['Invoice']['cac:AccountingSupplierParty']['cac:Party']
                                                        ['cac:PostalAddress']['cbc:CountrySubentity'] = $va;
                                                    break;
                                                /* Distrito */
                                                case 5:
                                                    $response['Invoice']['cac:AccountingSupplierParty']['cac:Party']
                                                        ['cac:PostalAddress']['cbc:District'] = $va;
                                                    break;
                                                /* Codigo de pais - Catalogo No 04 */
                                                case 6:
                                                    $response['Invoice']['cac:AccountingSupplierParty']['cac:Party']
                                                        ['cac:PostalAddress']['cbc:Country']['cbc:IdentificationCode'] = $va;
                                                    break;
                                            }
                                        }
                                    }
                                    break;
                                /* Numero de RUC */
                                case 4:
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            /*  Numero de RUC */
                                            case 0:
                                                $response['Invoice']['cac:AccountingSupplierParty']
                                                    ['cbc:CustomerAssignedAccountID'] = $va;
                                                break;
                                            /* Tipo de documento - Catalogo No 06 */
                                            case 1:
                                                $response['Invoice']['cac:AccountingSupplierParty']
                                                    ['cbc:AdditionalAccountID'] = $va;
                                                break;
                                        }
                                    }
                                    break;
                                /* Tipo de documento (Boleta) - Catalogo No 01 */
                                case 5:
                                    $response['Invoice']['cbc:InvoiceTypeCode'] = $v;
                                    break;
                                /* Numeracion, conformada por serie y numero correlativo */
                                case 6:
                                    $response['Invoice']['cbc:ID'] = $v;
                                    break;
                                /* Tipo y numero de documento de identidad del adquiriente o usuario */
                                case 7:
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            /* Numero de documento */
                                            case 0:
                                                $response['Invoice']['cac:AccountingCustomerParty']
                                                    ['cbc:CustomerAssignedAccountID'] = $va;
                                                break;
                                            /* Tipo de documento - Catalogo No 06 */
                                            case 1:
                                                $response['Invoice']['cac:AccountingCustomerParty']
                                                    ['cbc:AdditionalAccountID'] = $va;
                                                break;
                                        }
                                    }
                                    break;
                                /* Apelidos y Nombres del adquiriente o usuario */
                                case 8:
                                    $response['Invoice']['cac:AccountingCustomerParty']['cac:Party']['cac:PartyLegalEntity']
                                        ['cbc:RegistrationName'] = $v;
                                    break;
                                /* Direccion en el Pais del adquiriente o lugar de destino */
                                case 9:
                                    if (isset($v) && !empty($v)) {
                                        $response['Invoice']['cac:AccountingCustomerParty']['cac:Party']
                                            ['cac:PhysicalLocation']['cbc:Description'] = $v;
                                    }
                                    break;
                                /* Total valor de venta - operaciones gravadas */
                                case 10:
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            /* Codigo de tipo de monto - Catalogo No 14 */
                                            case 0:
                                                $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                                                    ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                    ['sac:AdditionalMonetaryTotal'][$count['Invoice']['ext:UBLExtensions']
                                                    ['ext:UBLExtension']['ext:ExtensionContent']['sac:AdditionalInformation']
                                                    ['sac:AdditionalMonetaryTotal']]['cbc:ID'] = $va;
                                                break;
                                            /* Monto */
                                            case 1:
                                                $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                                                    ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                    ['sac:AdditionalMonetaryTotal'][$count['Invoice']['ext:UBLExtensions']
                                                    ['ext:UBLExtension']['ext:ExtensionContent']['sac:AdditionalInformation']
                                                    ['sac:AdditionalMonetaryTotal']]['cbc:PayableAmount'] = $va;
                                                break;
                                        }
                                    }
                                    $count['Invoice']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']
                                        ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal'] ++;
                                    break;
                                /* Total valor de venta - operaciones inafectas */
                                case 11:
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            /* Codigo de tipo de monto - Catalogo No 14 */
                                            case 0:
                                                $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                                                    ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                    ['sac:AdditionalMonetaryTotal'][$count['Invoice']['ext:UBLExtensions']
                                                    ['ext:UBLExtension']['ext:ExtensionContent']['sac:AdditionalInformation']
                                                    ['sac:AdditionalMonetaryTotal']]['cbc:ID'] = $va;
                                                break;
                                            /* Monto */
                                            case 1:
                                                $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                                                    ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                    ['sac:AdditionalMonetaryTotal'][$count['Invoice']['ext:UBLExtensions']
                                                    ['ext:UBLExtension']['ext:ExtensionContent']['sac:AdditionalInformation']
                                                    ['sac:AdditionalMonetaryTotal']]['cbc:PayableAmount'] = $va;
                                                break;
                                        }
                                    }
                                    $count['Invoice']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']
                                        ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal'] ++;
                                    break;
                                /* Total valor de venta - operaciones exoneradas */
                                case 12:
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            /* Codigo de tipo de monto - Catalogo No 14 */
                                            case 0:
                                                $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                                                    ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                    ['sac:AdditionalMonetaryTotal'][$count['Invoice']['ext:UBLExtensions']
                                                    ['ext:UBLExtension']['ext:ExtensionContent']['sac:AdditionalInformation']
                                                    ['sac:AdditionalMonetaryTotal']]['cbc:ID'] = $va;
                                                break;
                                            /* Monto */
                                            case 1:
                                                $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                                                    ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                    ['sac:AdditionalMonetaryTotal'][$count['Invoice']['ext:UBLExtensions']
                                                    ['ext:UBLExtension']['ext:ExtensionContent']['sac:AdditionalInformation']
                                                    ['sac:AdditionalMonetaryTotal']]['cbc:PayableAmount'] = $va;
                                                break;
                                        }
                                    }
                                    $count['Invoice']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']
                                        ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal'] ++;
                                    break;
                                /* Sumatoria IGV */
                                case 13:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {
                                                /* Sumatoria de IGV */
                                                case 0:
                                                    $response['Invoice']['cac:TaxTotal'][$count['Invoice']['cac:TaxTotal']]
                                                        ['cbc:TaxAmount'] = $va;
                                                    break;
                                                /* Sumatorio de IGV (Subtotal) */
                                                case 1:
                                                    $response['Invoice']['cac:TaxTotal'][$count['Invoice']['cac:TaxTotal']]
                                                        ['cac:TaxSubtotal']['cbc:TaxAmount'] = $va;
                                                    break;
                                                /* Codigo de tributo - Catalogo No 05 */
                                                case 2:
                                                    $response['Invoice']['cac:TaxTotal'][$count['Invoice']['cac:TaxTotal']]
                                                        ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:ID'] = $va;
                                                    break;
                                                /* Nombre de tributo - Catalogo No 05 */
                                                case 3:
                                                    $response['Invoice']['cac:TaxTotal'][$count['Invoice']['cac:TaxTotal']]
                                                        ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:Name'] = $va;
                                                    break;
                                                /* Codigo internacional tributo - Catalogo No 05 */
                                                case 4:
                                                    $response['Invoice']['cac:TaxTotal'][$count['Invoice']['cac:TaxTotal']]
                                                        ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']
                                                        ['cbc:TaxTypeCode'] = $va;
                                                    break;
                                            }
                                        }
                                        $count['Invoice']['cac:TaxTotal'] ++;
                                    }
                                    break;
                                /* Sumatoria ISC */
                                case 14:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {
                                                /* Sumatoria de ISC */
                                                case 0:
                                                    $response['Invoice']['cac:TaxTotal'][$count['Invoice']['cac:TaxTotal']]
                                                        ['cbc:TaxAmount'] = $va;
                                                    break;
                                                /* Sumatorio de ISC (Subtotal) */
                                                case 1:
                                                    $response['Invoice']['cac:TaxTotal'][$count['Invoice']['cac:TaxTotal']]
                                                        ['cac:TaxSubtotal']['cbc:TaxAmount'] = $va;
                                                    break;
                                                /* Codigo de tributo - Catalogo No 05 */
                                                case 2:
                                                    $response['Invoice']['cac:TaxTotal'][$count['Invoice']['cac:TaxTotal']]
                                                        ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:ID'] = $va;
                                                    break;
                                                /* Nombre de tributo - Catalogo No 05 */
                                                case 3:
                                                    $response['Invoice']['cac:TaxTotal'][$count['Invoice']['cac:TaxTotal']]
                                                        ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:Name'] = $va;
                                                    break;
                                                /* Codigo internacional tributo - Catalogo No 05 */
                                                case 4:
                                                    $response['Invoice']['cac:TaxTotal'][$count['Invoice']['cac:TaxTotal']]
                                                        ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']
                                                        ['cbc:TaxTypeCode'] = $va;
                                                    break;
                                            }
                                        }
                                        $count['Invoice']['cac:TaxTotal'] ++;
                                    }
                                    break;
                                /* Sumatoria otros tributos */
                                case 15:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {
                                                /* Sumatoria de Otros Tributos */
                                                case 0:
                                                    $response['Invoice']['cac:TaxTotal'][$count['Invoice']['cac:TaxTotal']]
                                                        ['cbc:TaxAmount'] = $va;
                                                    break;
                                                /* Sumatorio de Otros Tributos (Subtotal) */
                                                case 1:
                                                    $response['Invoice']['cac:TaxTotal'][$count['Invoice']['cac:TaxTotal']]
                                                        ['cac:TaxSubtotal']['cbc:TaxAmount'] = $va;
                                                    break;
                                                /* Codigo de tributo - Catalogo No 05 */
                                                case 2:
                                                    $response['Invoice']['cac:TaxTotal'][$count['Invoice']['cac:TaxTotal']]
                                                        ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:ID'] = $va;
                                                    break;
                                                /* Nombre de tributo - Catalogo No 05 */
                                                case 3:
                                                    $response['Invoice']['cac:TaxTotal'][$count['Invoice']['cac:TaxTotal']]
                                                        ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:Name'] = $va;
                                                    break;
                                                /* Codigo internacional tributo - Catalogo No 05 */
                                                case 4:
                                                    $response['Invoice']['cac:TaxTotal'][$count['Invoice']['cac:TaxTotal']]
                                                        ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']
                                                        ['cbc:TaxTypeCode'] = $va;
                                                    break;
                                            }
                                        }
                                        $count['Invoice']['cac:TaxTotal'] ++;
                                    }
                                    break;
                                /* Sumatoria otros Cargos */
                                case 16:
                                    if (isset($v) && !empty($v)) {
                                        $response['Invoice']['cac:LegalMonetaryTotal']['cbc:ChargeTotalAmount'] = $v;
                                    }
                                    break;
                                /* Total descuentos */
                                case 17:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {
                                                /* Codigo de Tipo de monto - Catalogo No 14 */
                                                case 0:
                                                    $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                                                        ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:AdditionalMonetaryTotal'][$count['Invoice']['ext:UBLExtensions']
                                                        ['ext:UBLExtension']['ext:ExtensionContent']
                                                        ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal']]['cbc:ID'] = $va;
                                                    break;
                                                /* Monto */
                                                case 1:
                                                    $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                                                        ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:AdditionalMonetaryTotal'][$count['Invoice']['ext:UBLExtensions']
                                                        ['ext:UBLExtension']['ext:ExtensionContent']
                                                        ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal']]
                                                        ['cbc:PayableAmount'] = $va;
                                                    break;
                                            }
                                        }
                                        $count['Invoice']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']
                                            ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal'] ++;
                                    }
                                    break;
                                /* Importe total de la venta, sesion en uso o del servicio prestado */
                                case 18:
                                    $response['Invoice']['cac:LegalMonetaryTotal']['cbc:PayableAmount'] = $v;
                                    break;
                                /* Tipo de moneda en la cual se emite la boleta de venta electronica */
                                case 19:
                                    $response['Invoice']['cbc:DocumentCurrencyCode'] = $v;
                                    break;
                                /* Tipo y numero de la guia de remision relacionada con la operacion */
                                case 20:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            $expl = explode('¦', $va);
                                            if (isset($expl[0]) && !empty($expl[0])) {
                                                foreach ($expl as $k => $v) {
                                                    switch ($k) {
                                                        /* Numero de Guia */
                                                        case 0:
                                                            $response['Invoice']['cac:DespatchDocumentReference'][$ke]
                                                                ['cbc:ID'] = $v;
                                                            break;
                                                        /* Tipo de documento - Catalogo No 01 */
                                                        case 1:
                                                            $response['Invoice']['cac:DespatchDocumentReference'][$ke]
                                                                ['cbc:DocumentTypeCode'] = $v;
                                                            break;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    break;
                                /* Leyendas */
                                case 21:
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
                                                                $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                                                                    ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                                    ['sac:AdditionalProperty'][$count['Invoice']
                                                                    ['ext:UBLExtensions']['ext:UBLExtension']
                                                                    ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                                    ['sac:AdditionalProperty']]['cbc:ID'] = $v;
                                                                break;
                                                            /* Descripcion de la leyenda */
                                                            case 1:
                                                                $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                                                                    ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                                    ['sac:AdditionalProperty'][$count['Invoice']
                                                                    ['ext:UBLExtensions']['ext:UBLExtension']
                                                                    ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                                    ['sac:AdditionalProperty']]['cbc:Value'] = $v;
                                                                break;
                                                        }
                                                    }
                                                }
                                                $count['Invoice']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']
                                                    ['sac:AdditionalInformation']['sac:AdditionalProperty'] ++;
                                            }
                                        }
                                    }
                                    break;
                                /* Tipo de numero de otro documento y codigo relacionado con la operación */
                                case 22:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            $expl = explode('¦', $va);
                                            if (isset($expl[0]) && !empty($expl[0])) {
                                                foreach ($expl as $k => $v) {
                                                    switch ($k) {
                                                        /* Numero de Guia */
                                                        case 0:
                                                            $response['Invoice']['cac:AdditionalDocumentReference'][$ke]
                                                                ['cbc:ID'] = $v;
                                                            break;
                                                        /* Tipo de documento - Catalogo No 01 */
                                                        case 1:
                                                            $response['Invoice']['cac:AdditionalDocumentReference'][$ke]
                                                                ['cbc:DocumentTypeCode'] = $v;
                                                            break;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    break;
                                /* Firma Digital */
                                case 23:
                                    break;
                                /* Version del UBL */
                                case 24:
                                    $response['Invoice']['cbc:UBLVersionID'] = $v;
                                    break;
                                /* Version de la estructura del documento */
                                case 25:
                                    $response['Invoice']['cbc:CustomizationID'] = $v;
                                    break;
                                /* Importe de la percepción de la moneda nacional */
                                case 26:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {
                                                /* Codigo de tipo de monto - Catalogo No 14 */
                                                case 0:
                                                    $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                                                        ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:AdditionalMonetaryTotal'][$count['Invoice']['ext:UBLExtensions']
                                                        ['ext:UBLExtension']['ext:ExtensionContent']
                                                        ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal']]['cbc:ID'] = $va;
                                                    break;
                                                /* Base imponible de percepcion */
                                                case 1:
                                                    $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                                                        ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:AdditionalMonetaryTotal'][$count['Invoice']['ext:UBLExtensions']
                                                        ['ext:UBLExtension']['ext:ExtensionContent']
                                                        ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal']]
                                                        ['sac:ReferenceAmount'] = $va;
                                                    break;
                                                /* Monto de la percepcion */
                                                case 2:
                                                    $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                                                        ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:AdditionalMonetaryTotal'][$count['Invoice']['ext:UBLExtensions']
                                                        ['ext:UBLExtension']['ext:ExtensionContent']
                                                        ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal']]
                                                        ['cbc:PayableAmount'] = $va;
                                                    break;
                                                /* Monto total incluido la percepcion */
                                                case 3:
                                                    $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                                                        ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:AdditionalMonetaryTotal'][$count['Invoice']['ext:UBLExtensions']
                                                        ['ext:UBLExtension']['ext:ExtensionContent']
                                                        ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal']]
                                                        ['sac:TotalAmount'] = $va;
                                                    break;
                                            }
                                        }
                                        $count['Invoice']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']
                                            ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal'] ++;
                                    }
                                    break;

                                /* Total Valor de Venta - Operaciones Gratuitas */
                                case 27:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {
                                                /* Codigo del tipo de elemento - Catalogo No 14 */
                                                case 0:
                                                    $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                                                        ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:AdditionalMonetaryTotal'][$count['Invoice']['ext:UBLExtensions']
                                                        ['ext:UBLExtension']['ext:ExtensionContent']
                                                        ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal']]['cbc:ID'] = $va;
                                                    break;
                                                /* Total Valor Venta Operaciones Gratuitas */
                                                case 1:
                                                    $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                                                        ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:AdditionalMonetaryTotal'][$count['Invoice']['ext:UBLExtensions']
                                                        ['ext:UBLExtension']['ext:ExtensionContent']
                                                        ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal']]
                                                        ['cbc:PayableAmount'] = $va;
                                                    break;
                                            }
                                        }
                                        $count['Invoice']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']
                                            ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal'] ++;
                                    }
                                    break;
                                /* Descuentos Globales */
                                case 28:
                                    if (isset($v) && !empty($v)) {
                                        $response['Invoice']['cac:LegalMonetaryTotal']['cbc:AllowanceTotalAmount'] = $v;
                                    }
                                    break;
                                # Datos extra-oficiales
                                /* Orden de Compra */
                                case 29:
                                    if (isset($v) && !empty($v)) {
                                        $response['Invoice']['ExtraOficial']['OrdenDeCompra'] = $v;
                                    }
                                    break;
                                /* Condiciones de Pago */
                                case 30:
                                    if (isset($v) && !empty($v)) {
                                        $response['Invoice']['ExtraOficial']['CondicionesDePago'] = $v;
                                    }
                                    break;
                                /* Fecha de Vencimiento */
                                case 31:
                                    if (isset($v) && !empty($v)) {
                                        $response['Invoice']['ExtraOficial']['FechaDeVencimiento'] = $v;
                                    }
                                    break;
                                /* Observación */
                                case 32:
                                    if (isset($v) && !empty($v)) {
                                        $response['Invoice']['ExtraOficial']['Observacion'] = $v;
                                    }
                                    break;
                                /* Correo del cliente */
                                case 33:
                                    if (isset($v) && !empty($v)) {
                                        $response['Invoice']['ExtraOficial']['ClienteCorreo'] = $v;
                                    }
                                    break;
                                /* Tipo de cambio */
                                case 34:
                                    $response['Invoice']['ExtraOficial']['TipoDeCambio'] = $v;
                                    break;
                                ## LOLIMSA - INICIO ##
                                case 35:
                                    if (isset($v) && !empty($v)) {
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['c_value'] = $v;
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['c_name'] = 'ticket_titulo_1';
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['n_index'] = $a;
                                        $a++;
                                    }
                                    break;
                                case 36:
                                    if (isset($v) && !empty($v)) {
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['c_value'] = $v;
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['c_name'] = 'ticket_titulo_2';
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['n_index'] = $a;
                                        $a++;
                                    }
                                    break;
                                case 37:
                                    if (isset($v) && !empty($v)) {
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['c_value'] = $v;
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['c_name'] = 'serie_ticket';
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['n_index'] = $a;
                                        $a++;
                                    }
                                    break;
                                case 38:
                                    if (isset($v) && !empty($v)) {
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['c_value'] = $v;
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['c_name'] = 'fecha_emision_hora';
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['n_index'] = $a;
                                        $a++;
                                    }
                                    break;
                                case 39:
                                    if (isset($v) && !empty($v)) {
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['c_value'] = $v;
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['c_name'] = 's_caja';
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['n_index'] = $a;
                                        $a++;
                                    }
                                    break;
                                case 40:
                                    if (isset($v) && !empty($v)) {
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['c_value'] = $v;
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['c_name'] = 'paciente';
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['n_index'] = $a;
                                        $a++;
                                    }
                                    break;
                                case 41:
                                    if (isset($v) && !empty($v)) {
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['c_value'] = $v;
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['c_name'] = 'prf_nro';
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['n_index'] = $a;
                                        $a++;
                                    }
                                    break;
                                case 42:
                                    if (isset($v) && !empty($v)) {
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['c_value'] = $v;
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['c_name'] = 'hc';
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['n_index'] = $a;
                                        $a++;
                                    }
                                    break;
                                case 43:
                                    if (isset($v) && !empty($v)) {
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['c_value'] = $v;
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['c_name'] = 'pago_con';
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['n_index'] = $a;
                                        $a++;
                                    }
                                    break;
                                case 44:
                                    if (isset($v) && !empty($v)) {
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['c_value'] = $v;
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['c_name'] = 'vuelto';
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['n_index'] = $a;
                                        $a++;
                                    }
                                    break;
                                case 45:
                                    if (isset($v) && !empty($v)) {
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['c_value'] = $v;
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['c_name'] = 'usuario';
                                        $response['Invoice']['ExtraOficial']['Custom'][$a]['n_index'] = $a;
                                        $a++;
                                    }
                                    break;
                                ## LOLIMSA - FIN ##
                                # Información adicional - Anticipos | INICIO
                                case 46:
                                    if (isset($v) && !empty($v)) {
                                        $explode = explode('!', $v);
                                        if (isset($explode[0]) && !empty($explode[0])) {
                                            foreach ($explode as $ke => $va) {
                                                $expl = explode('¦', $va);
                                                if (isset($expl[0]) && !empty($expl[0])) {
                                                    foreach ($expl as $k => $v) {
                                                        switch ($k) {
                                                            # Monto prepagado o anticipado
                                                            case 0:
                                                                $response['Invoice']['cac:PrepaidPayment'][$ke]['cbc:PaidAmount'] = $v;
                                                                break;
                                                            # Tipo de doc. catálogo no. 12
                                                            case 1:
                                                                $response['Invoice']['cac:PrepaidPayment'][$ke]['cbc:ID']['@schemeID'] = $v;
                                                                break;
                                                            # Serie - numero de documento
                                                            case 2:
                                                                $response['Invoice']['cac:PrepaidPayment'][$ke]['cbc:ID']['value'] = $v;
                                                                break;
                                                            # Tipo de documento catalogo no. 6
                                                            case 3:
                                                                $response['Invoice']['cac:PrepaidPayment'][$ke]['cbc:InstructionID']['@schemeID'] = $v;
                                                                break;
                                                            # Numero de documento
                                                            case 4:
                                                                $response['Invoice']['cac:PrepaidPayment'][$ke]['cbc:InstructionID']['value'] = $v;
                                                                break;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    break;
                                # Total Anticipos
                                case 47:
                                    if (isset($v) && !empty($v)) {
                                        # Monto del descuento
                                        $response['Invoice']['cac:LegalMonetaryTotal']['cbc:PrepaidAmount'] = $v;
                                    }
                                    break;
                                # Información adicional - Anticipos | FIN
                                # Información adicional | INICIO
                                case 48:
                                    if (isset($v) && !empty($v)) {
                                        # Codigo del tipo de operacion - Catalogo No. 17
                                        $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']
                                            ['sac:AdditionalInformation']['sac:SUNATTransaction']['cbc:ID'] = $v;
                                    }
                                    break;
                                # Información adicional | FIN
                                # Información adicional - Guía Resumen| INICIO
                                # Direccion del punto de llegada
                                case 49:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {
                                                # (Código de ubigeo - Catálogo No. 13)
                                                case 0:
                                                    if (empty($va)) {
                                                        break;
                                                    }
                                                    $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                                                        ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:SUNATEmbededDespatchAdvice']['cac:DeliveryAddress']['cbc:ID'] = $va;
                                                    break;
                                                # (Dirección completa y detallada)
                                                case 1:
                                                    if (empty($va)) {
                                                        break;
                                                    }
                                                    $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                                                        ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:SUNATEmbededDespatchAdvice']['cac:DeliveryAddress']['cbc:StreetName'] = $va;
                                                    break;
                                                # (Urbanización)
                                                case 2:
                                                    if (empty($va)) {
                                                        break;
                                                    }
                                                    $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                                                        ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:SUNATEmbededDespatchAdvice']['cac:DeliveryAddress']['cbc:CitySubdivisionName'] = $va;
                                                    break;
                                                # (Provincia)
                                                case 3:
                                                    if (empty($va)) {
                                                        break;
                                                    }
                                                    $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                                                        ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:SUNATEmbededDespatchAdvice']['cac:DeliveryAddress']['cbc:CityName'] = $va;
                                                    break;
                                                # (Departamento)
                                                case 4:
                                                    if (empty($va)) {
                                                        break;
                                                    }
                                                    $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                                                        ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:SUNATEmbededDespatchAdvice']['cac:DeliveryAddress']['cbc:CountrySubentity'] = $va;
                                                    break;
                                                # (Distrito)
                                                case 5:
                                                    if (empty($va)) {
                                                        break;
                                                    }
                                                    $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                                                        ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:SUNATEmbededDespatchAdvice']['cac:DeliveryAddress']['cbc:District'] = $va;
                                                    break;
                                                # (Código de país - Catálogo No. 04)
                                                case 6:
                                                    if (empty($va)) {
                                                        break;
                                                    }
                                                    $response['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                                                        ['ext:ExtensionContent']['sac:AdditionalInformation']
                                                        ['sac:SUNATEmbededDespatchAdvice']['cac:DeliveryAddress']['cac:Country']['cbc:IdentificationCode'] = $va;
                                                    break;
                                            }
                                        }
                                    }
                                    break;
                                # Información adicional - Guía Resumen | FIN
                                case 50:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {
                                                /* Codigo de Ubigeo - Catalago No 13 */
                                                case 0:
                                                    $response['Invoice']['cac:SellerSupplierParty']['cac:Party']
                                                        ['cac:PostalAddress']['cbc:ID'] = $va;
                                                    break;
                                                /* Direccion completa y detallada */
                                                case 1:
                                                    $response['Invoice']['cac:SellerSupplierParty']['cac:Party']
                                                        ['cac:PostalAddress']['cbc:StreetName'] = $va;
                                                    break;
                                                /* Urbanizacion */
                                                case 2:
                                                    $response['Invoice']['cac:SellerSupplierParty']['cac:Party']
                                                        ['cac:PostalAddress']['cbc:CitySubdivisionName'] = $va;
                                                    break;
                                                /* Provincia */
                                                case 3:
                                                    $response['Invoice']['cac:SellerSupplierParty']['cac:Party']
                                                        ['cac:PostalAddress']['cbc:CityName'] = $va;
                                                    break;
                                                /* Departamento */
                                                case 4:
                                                    $response['Invoice']['cac:SellerSupplierParty']['cac:Party']
                                                        ['cac:PostalAddress']['cbc:CountrySubentity'] = $va;
                                                    break;
                                                /* Distrito */
                                                case 5:
                                                    $response['Invoice']['cac:SellerSupplierParty']['cac:Party']
                                                        ['cac:PostalAddress']['cbc:District'] = $va;
                                                    break;
                                                /* Codigo de pais - Catalogo No 04 */
                                                case 6:
                                                    $response['Invoice']['cac:SellerSupplierParty']['cac:Party']
                                                        ['cac:PostalAddress']['cac:Country']['cbc:IdentificationCode'] = $va;
                                                    break;
                                            }
                                        }
                                    }
                                    break;
                            }
                        }
                        break;
                    /* Detalle */
                    default:
                        $c['Invoice']['cac:InvoiceLine']['cac:PricingReference']['cac:AlternativeConditionPrice'] = 0;
                        $c['Invoice']['cac:InvoiceLine']['cac:TaxTotal'] = 0;
                        foreach ($line as $k => $v) {
                            switch ($k) {
                                /* Unidad de medida por item */
                                case 0:
                                    $response['Invoice']['cac:InvoiceLine'][$count['Invoice']['cac:InvoiceLine']]
                                        ['cbc:InvoicedQuantity']['@unitCode'] = $v;
                                    break;
                                /* Cantidad de unidades por item */
                                case 1:
                                    $response['Invoice']['cac:InvoiceLine'][$count['Invoice']['cac:InvoiceLine']]
                                        ['cbc:InvoicedQuantity']['amount'] = $v;
                                    break;
                                /* Descripcion detallada del servicio prestado, bien vendido o cedido en uso indicando las 
                                 * caracteristicas */
                                case 2:
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        $response['Invoice']['cac:InvoiceLine'][$count['Invoice']['cac:InvoiceLine']]
                                            ['cac:Item']['cbc:Description'][$ke] = $va;
                                    }
                                    break;
                                /* Precio de venta unitario por item y codigo */
                                case 3:
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            /* Monto de Precio de venta */
                                            case 0:
                                                $response['Invoice']['cac:InvoiceLine'][$count['Invoice']
                                                    ['cac:InvoiceLine']]['cac:PricingReference']
                                                    ['cac:AlternativeConditionPrice'][$c['Invoice']['cac:InvoiceLine']
                                                    ['cac:PricingReference']['cac:AlternativeConditionPrice']]
                                                    ['cbc:PriceAmount'] = $va;
                                                break;
                                            /* Codigo de tipo de precio - Catalogo No 16 */
                                            case 1:
                                                $response['Invoice']['cac:InvoiceLine'][$count['Invoice']
                                                    ['cac:InvoiceLine']]['cac:PricingReference']
                                                    ['cac:AlternativeConditionPrice'][$c['Invoice']['cac:InvoiceLine']
                                                    ['cac:PricingReference']['cac:AlternativeConditionPrice']]
                                                    ['cbc:PriceTypeCode'] = $va;
                                                break;
                                        }
                                    }
                                    $c['Invoice']['cac:InvoiceLine']['cac:PricingReference']
                                        ['cac:AlternativeConditionPrice'] ++;
                                    break;
                                /* Afectacion al IGV por item */
                                case 4:
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            /* Monto de IGV de la linea */
                                            case 0:
                                                $response['Invoice']['cac:InvoiceLine'][$count['Invoice']['cac:InvoiceLine']]
                                                    ['cac:TaxTotal'][$c['Invoice']['cac:InvoiceLine']['cac:TaxTotal']]
                                                    ['cbc:TaxAmount'] = $va;
                                                break;
                                            /* Monto de IGV de la linea */
                                            case 1:
                                                $response['Invoice']['cac:InvoiceLine'][$count['Invoice']['cac:InvoiceLine']]
                                                    ['cac:TaxTotal'][$c['Invoice']['cac:InvoiceLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cbc:TaxAmount'] = $va;
                                                break;
                                            /* Afectacion al IGV - Catalogo No 07 */
                                            case 2:
                                                $response['Invoice']['cac:InvoiceLine'][$count['Invoice']['cac:InvoiceLine']]
                                                    ['cac:TaxTotal'][$c['Invoice']['cac:InvoiceLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cac:TaxCategory']['cbc:TaxExemptionReasonCode'] = $va;
                                                break;
                                            /* Codigo de tributo - Catalogo No 05 */
                                            case 3:
                                                $response['Invoice']['cac:InvoiceLine'][$count['Invoice']['cac:InvoiceLine']]
                                                    ['cac:TaxTotal'][$c['Invoice']['cac:InvoiceLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:ID'] = $va;
                                                break;
                                            /* Nombre de tributo - Catalogo No 05 */
                                            case 4:
                                                $response['Invoice']['cac:InvoiceLine'][$count['Invoice']['cac:InvoiceLine']]
                                                    ['cac:TaxTotal'][$c['Invoice']['cac:InvoiceLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:Name'] = $va;
                                                break;
                                            /* Codigo internacional tributo - Catalogo No 05 */
                                            case 5:
                                                $response['Invoice']['cac:InvoiceLine'][$count['Invoice']['cac:InvoiceLine']]
                                                    ['cac:TaxTotal'][$c['Invoice']['cac:InvoiceLine']['cac:TaxTotal']]
                                                    ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:TaxTypeCode'] = $va;
                                                break;
                                        }
                                    }
                                    $c['Invoice']['cac:InvoiceLine']['cac:TaxTotal'] ++;
                                    break;
                                /* Sistema de ISC Por Item */
                                case 5:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {
                                                /* Monto de ISC de la linea */
                                                case 0:
                                                    $response['Invoice']['cac:InvoiceLine'][$count['Invoice']['cac:InvoiceLine']]
                                                        ['cac:TaxTotal'][$c['Invoice']['cac:InvoiceLine']['cac:TaxTotal']]
                                                        ['cbc:TaxAmount'] = $va;
                                                    break;
                                                /* Monto de ISC de la linea */
                                                case 1:
                                                    $response['Invoice']['cac:InvoiceLine'][$count['Invoice']['cac:InvoiceLine']]
                                                        ['cac:TaxTotal'][$c['Invoice']['cac:InvoiceLine']['cac:TaxTotal']]
                                                        ['cac:TaxSubtotal']['cbc:TaxAmount'] = $va;
                                                    break;
                                                /* Tipo de sistema de ISC - Catalogo No 08 */
                                                case 2:
                                                    $response['Invoice']['cac:InvoiceLine'][$count['Invoice']
                                                        ['cac:InvoiceLine']]['cac:TaxTotal'][$c['Invoice']['cac:InvoiceLine']
                                                        ['cac:TaxTotal']]['cac:TaxSubtotal']['cac:TaxCategory']['cbc:TierRange'] = $va;
                                                    break;
                                                /* Codigo de tributo - Catalogo No 05 */
                                                case 3:
                                                    $response['Invoice']['cac:InvoiceLine'][$count['Invoice']['cac:InvoiceLine']]
                                                        ['cac:TaxTotal'][$c['Invoice']['cac:InvoiceLine']['cac:TaxTotal']]
                                                        ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:ID'] = $va;
                                                    break;
                                                /* Nombre de tributo - Catalogo No 05 */
                                                case 4:
                                                    $response['Invoice']['cac:InvoiceLine'][$count['Invoice']['cac:InvoiceLine']]
                                                        ['cac:TaxTotal'][$c['Invoice']['cac:InvoiceLine']['cac:TaxTotal']]
                                                        ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']['cbc:Name'] = $va;
                                                    break;
                                                /* Codigo internacional tributo - Catalogo No 05 */
                                                case 5:
                                                    $response['Invoice']['cac:InvoiceLine'][$count['Invoice']['cac:InvoiceLine']]
                                                        ['cac:TaxTotal'][$c['Invoice']['cac:InvoiceLine']['cac:TaxTotal']]
                                                        ['cac:TaxSubtotal']['cac:TaxCategory']['cac:TaxScheme']
                                                        ['cbc:TaxTypeCode'] = $va;
                                                    break;
                                            }
                                        }
                                        $c['Invoice']['cac:InvoiceLine']['cac:TaxTotal'] ++;
                                    }
                                    break;
                                /* Numero de orden del item */
                                case 6:
                                    $response['Invoice']['cac:InvoiceLine'][$count['Invoice']['cac:InvoiceLine']]['cbc:ID'] = $v;
                                    break;
                                /* Codigo del producto */
                                case 7:
                                    if (isset($v) && !empty($v)) {
                                        $response['Invoice']['cac:InvoiceLine'][$count['Invoice']['cac:InvoiceLine']]
                                            ['cac:Item']['cac:SellersItemIdentificacion']['cbc:ID'] = $v;
                                    }
                                    break;
                                /* Valor unitario por item */
                                case 8:
                                    $response['Invoice']['cac:InvoiceLine'][$count['Invoice']['cac:InvoiceLine']]
                                        ['cac:Price']['cbc:PriceAmount'] = $v;
                                    break;
                                /* Valor de Venta por Item */
                                case 9:
                                    $response['Invoice']['cac:InvoiceLine'][$count['Invoice']['cac:InvoiceLine']]
                                        ['cbc:LineExtensionAmount'] = $v;
                                    break;
                                /* Valor referencial unitario por item en operaciones no onerosas y codigo */
                                case 10:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {
                                                /* Monto de valor referencial unitario */
                                                case 0:
                                                    $response['Invoice']['cac:InvoiceLine'][$count['Invoice']
                                                        ['cac:InvoiceLine']]['cac:PricingReference']
                                                        ['cac:AlternativeConditionPrice'][$c['Invoice']['cac:InvoiceLine']
                                                        ['cac:PricingReference']['cac:AlternativeConditionPrice']]
                                                        ['cbc:PriceAmount'] = $va;
                                                    break;
                                                /* Codigo de tipo de precio - Catalogo No 16 */
                                                case 1:
                                                    $response['Invoice']['cac:InvoiceLine'][$count['Invoice']
                                                        ['cac:InvoiceLine']]['cac:PricingReference']
                                                        ['cac:AlternativeConditionPrice'][$c['Invoice']['cac:InvoiceLine']
                                                        ['cac:PricingReference']['cac:AlternativeConditionPrice']]
                                                        ['cbc:PriceTypeCode'] = $va;
                                                    break;
                                            }
                                        }
                                        $c['Invoice']['cac:InvoiceLine']['cac:PricingReference']
                                            ['cac:AlternativeConditionPrice'] ++;
                                    }
                                    break;
                                /* Descuentos por item */
                                case 11:
                                    $explode = explode('!', $v);
                                    if (isset($explode[0]) && !empty($explode[0])) {
                                        foreach ($explode as $ke => $va) {
                                            switch ($ke) {
                                                # Indicador de descuento, colocar <false>
                                                case 0:
                                                    $response['Invoice']['cac:InvoiceLine'][$count['Invoice']['cac:InvoiceLine']]['cac:AllowanceCharge']
                                                        ['cbc:ChargeIndicator'] = $va;
                                                    break;
                                                # Monto de descuento
                                                case 1:
                                                    $response['Invoice']['cac:InvoiceLine'][$count['Invoice']
                                                        ['cac:InvoiceLine']]['cac:AllowanceCharge']
                                                        ['cbc:Amount'] = $va;
                                                    break;
                                            }
                                        }
                                    }
                                    break;
                            }
                        }
                        $count['Invoice']['cac:InvoiceLine'] ++;
                        break;
                }
            }
            $output['status'] = true;
            $output['document'] = $response;

            Log::info('Generación de array',
                [
                'lgph_id' => 1, 'c_id' => $response['Invoice']['cbc:ID'],
                'c_invoice_type_code' => $response['Invoice']['cbc:InvoiceTypeCode'],
                ]
            );
        } catch (Exception $exc) {
            Log::error($exc->getMessage(), ['lgph_id' => 1]);
            $output['message'] = $exc->getMessage();
        }
        return $output;
    }

    /**
     * Validador en linea de array
     * @param array $array
     * @throws Exception
     */
    public static function validateFile($array)
    {
        $validation = array(
            'Invoice.cbc:UBLVersionID' => 'required|max:10',
            'Invoice.cbc:CustomizationID' => 'required|max:10',
            'Invoice.cbc:ID' => 'required|max:13',
            'Invoice.cbc:IssueDate' => 'required|max:10',
            'Invoice.cbc:InvoiceTypeCode' => 'required|size:2',
            'Invoice.cbc:DocumentCurrencyCode' => 'required|size:3',
        );
        $messages = array(
            'Invoice.cbc:UBLVersionID.required' => 'Invoice.cbc:UBLVersionID La version del UBL es requerido.',
            'Invoice.cbc:UBLVersionID.max' => 'Invoice.cbc:UBLVersionID La version del UBL excedió la cantidad de caracteres.',
            'Invoice.cbc:CustomizationID.required' => 'Invoice.cbc:CustomizationID La versión de la estructura del Documento es requerido.',
            'Invoice.cbc:CustomizationID.max' => 'Invoice.cbc:CustomizationID La versión de la estructura del Documento excedió la cantidad de caracteres.',
            'Invoice.cbc:ID.required' => 'Invoice.cbc:ID La serie y el numero correlativo es requerido.',
            'Invoice.cbc:ID.max' => 'Invoice.cbc:ID La serie y el numero correlativo excedió la cantidad de caracteres.',
            'Invoice.cbc:IssueDate.required' => 'Invoice.cbc:IssueDate La Fecha de emisión es requerido.',
            'Invoice.cbc:IssueDate.max' => 'Invoice.cbc:IssueDate La Fecha de emisión excedió la cantidad de caracteres.',
            'Invoice.cbc:InvoiceTypeCode.required' => 'Invoice.cbc:InvoiceTypeCode El código del Tipo de documento es requerido.',
            'Invoice.cbc:InvoiceTypeCode.size' => 'Invoice.cbc:InvoiceTypeCode El código del Tipo de documento no tiene 2 caracteres.',
            'Invoice.cbc:DocumentCurrencyCode.required' => 'El tipo de moneda es requerido.',
            'Invoice.cbc:DocumentCurrencyCode.size' => 'El tipo de moneda no tiene 3 caracteres.',
        );
        $validator = Validator::make($array, $validation, $messages);
        if ($validator->fails()) {
            Log::error('Error en cabecera: ' . implode(',', $validator->messages()->all()),
                ['lgph_id' => 2, 'c_id' => $array['Invoice']['cbc:ID'], 'c_invoice_type_code' => $array['Invoice']['cbc:InvoiceTypeCode'],]);
            throw new Exception(implode(',', $validator->messages()->all()));
        }

        if (isset($array['Invoice']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']
                ['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal'])) {
            foreach ($array['Invoice']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']
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
                        ['lgph_id' => 2, 'c_id' => $array['Invoice']['cbc:ID'], 'c_invoice_type_code' => $array['Invoice']['cbc:InvoiceTypeCode'],]);
                    throw new Exception(implode(',', $validator->messages()->all()));
                }
            }
        }

        #sac:AdditionalMonetaryTotal
        if (isset($array['Invoice']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']
                ['sac:AdditionalInformation']['sac:AdditionalProperty'])) {
            foreach ($array['Invoice']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']
            ['sac:AdditionalInformation']['sac:AdditionalProperty'] as $value) {
                $validation = array(
                    'cbc:ID' => 'required|size:4',
                    'cbc:Value' => 'required',
                );
                $messages = array(
                    'cbc:ID.required' => 'Código del concepto adicional requerido.',
                    'cbc:ID.size' => 'Código del concepto adicional no tiene 4 caracteres.',
                    'cbc:Value.required' => 'Valor del concepto requerido.',
                );
                $validator = Validator::make($value, $validation, $messages);
                if ($validator->fails()) {
                    Log::error('Error en cabecera: ' . implode(',', $validator->messages()->all()),
                        ['lgph_id' => 2, 'c_id' => $array['Invoice']['cbc:ID'], 'c_invoice_type_code' => $array['Invoice']['cbc:InvoiceTypeCode'],]);
                    throw new Exception(implode(',', $validator->messages()->all()));
                }
            }
        }

        if (isset($array['Invoice']['cac:DespatchDocumentReference']) &&
            !empty($array['Invoice']['cac:DespatchDocumentReference'])) {
            $i = 1;
            foreach ($array['Invoice']['cac:DespatchDocumentReference'] as $key => $value) {
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
                        ['lgph_id' => 2, 'c_id' => $array['Invoice']['cbc:ID'], 'c_invoice_type_code' => $array['Invoice']['cbc:InvoiceTypeCode'],]);
                    throw new Exception(implode(',', $validator->messages()->all()));
                }
                $i++;
            }
        }

        if (isset($array['Invoice']['cac:AdditionalDocumentReference']) &&
            !empty($array['Invoice']['cac:AdditionalDocumentReference'])) {
            $i = 1;
            foreach ($array['Invoice']['cac:AdditionalDocumentReference'] as $key => $value) {
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
                        ['lgph_id' => 2, 'c_id' => $array['Invoice']['cbc:ID'], 'c_invoice_type_code' => $array['Invoice']['cbc:InvoiceTypeCode'],]);
                    throw new Exception(implode(',', $validator->messages()->all()));
                }
                $i++;
            }
        }

        #AccountingSupplierParty
        $validation = array(
            'Invoice.cac:AccountingSupplierParty.cbc:CustomerAssignedAccountID' => 'required|size:11',
            'Invoice.cac:AccountingSupplierParty.cbc:AdditionalAccountID' => 'required|size:1',
        );
        $messages = array(
            'Invoice.cac:AccountingSupplierParty.cbc:CustomerAssignedAccountID.required' => 'El número de documento de identidad (RUC) es requerido.',
            'Invoice.cac:AccountingSupplierParty.cbc:CustomerAssignedAccountID.size' => 'El número de documento de identidad (RUC) no tiene 11 caracteres.',
            'Invoice.cac:AccountingSupplierParty.cbc:AdditionalAccountID.required' => 'El tipo de documento de identificación es requerido.',
            'Invoice.cac:AccountingSupplierParty.cbc:AdditionalAccountID.size' => 'El tipo de documento de identificación no tiene 2 caracter.',
        );
        $validator = Validator::make($array, $validation, $messages);
        if ($validator->fails()) {
            Log::error('Error en cabecera: ' . implode(',', $validator->messages()->all()),
                ['lgph_id' => 2, 'c_id' => $array['Invoice']['cbc:ID'], 'c_invoice_type_code' => $array['Invoice']['cbc:InvoiceTypeCode'],]);
            throw new Exception(implode(',', $validator->messages()->all()));
        }

        if (isset($array['Invoice']['cac:AccountingSupplierParty']['cac:Party'])) {
            if (isset($array['Invoice']['cac:AccountingSupplierParty']['cac:Party']['cac:PartyName'])) {
                $validation = array(
                    'Invoice.cac:AccountingSupplierParty.cac:Party.cac:PartyName.cbc:Name' => 'required|max:100',
                    'Invoice.cac:AccountingSupplierParty.cac:Party.cac:PartyLegalEntity.cbc:RegistrationName' => 'required|max:100',
                );
                $messages = array(
                    'Invoice.cac:AccountingSupplierParty.cac:Party.cac:PartyName.cbc:Name.required' => 'Nombre comercial es requerido.',
                    'Invoice.cac:AccountingSupplierParty.cac:Party.cac:PartyName.cbc:Name.max' => 'Nombre comercial no puede exceder 100 caracteres',
                    'Invoice.cac:AccountingSupplierParty.cac:Party.cac:PartyLegalEntity.cbc:RegistrationName.required' => 'Apellidos y nombres o denominación o razón social es requerido.',
                    'Invoice.cac:AccountingSupplierParty.cac:Party.cac:PartyLegalEntity.cbc:RegistrationName.max' => 'Apellidos y nombres o denominación o razón social no puede exceder 100 caracteres',
                );
                $validator = Validator::make($array, $validation, $messages);
                if ($validator->fails()) {
                    Log::error('Error en cabecera: ' . implode(',', $validator->messages()->all()),
                        ['lgph_id' => 2, 'c_id' => $array['Invoice']['cbc:ID'], 'c_invoice_type_code' => $array['Invoice']['cbc:InvoiceTypeCode'],]);
                    throw new Exception(implode(',', $validator->messages()->all()));
                }
            }

            if (isset($array['Invoice']['cac:AccountingSupplierParty']['cac:Party']['cac:PostalAddress'])) {
                $validation = array(
                    'Invoice.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:ID' => 'size:6',
                    'Invoice.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:StreetName' => 'required|max:100',
                    'Invoice.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:CitySubdivisionName' => 'max:25',
                    'Invoice.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:CityName' => 'max:30',
                    'Invoice.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:CountrySubentity' => 'max:30',
                    'Invoice.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:District' => 'max:30',
                    'Invoice.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cac:Country.cbc:IdentificationCode' => 'size:2',
                );
                $messages = array(
                    'Invoice.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:ID.size' => 'El código de UBIGEO no tiene 6 caracteres.',
                    'Invoice.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:StreetName.required' => 'La dirección completa y detallada es requerida.',
                    'Invoice.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:StreetName.max' => 'La dirección completa y detallada excedió los 100 caracteres.',
                    'Invoice.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:CitySubdivisionName.max' => 'La ubicación o zona excedió los 25 caracteres.',
                    'Invoice.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:CityName.max' => 'El departamento excedió los 30 caracteres.',
                    'Invoice.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:CountrySubentity.max' => 'La provincia excedió los 30 caracteres.',
                    'Invoice.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cbc:District.max' => 'El distrito excedió los 30 caracteres.',
                    'Invoice.cac:AccountingSupplierParty.cac:Party.cac:PostalAddress.cac:Country.cbc:IdentificationCode.size' => 'El código del pais no tiene 2 caracteres.',
                );
                $validator = Validator::make($array, $validation, $messages);
                if ($validator->fails()) {
                    Log::error('Error en cabecera: ' . implode(',', $validator->messages()->all()),
                        ['lgph_id' => 2, 'c_id' => $array['Invoice']['cbc:ID'], 'c_invoice_type_code' => $array['Invoice']['cbc:InvoiceTypeCode'],]);
                    throw new Exception(implode(',', $validator->messages()->all()));
                }
            }
        }

        #AccountingCustomerParty
        $validation = array(
            'Invoice.cac:AccountingCustomerParty.cbc:CustomerAssignedAccountID' => 'required|max:15',
            'Invoice.cac:AccountingCustomerParty.cbc:AdditionalAccountID' => 'required|size:1',
            'Invoice.cac:AccountingCustomerParty.cac:Party.cac:PartyLegalEntity.cbc:RegistrationName' => 'required|max:100',
            'Invoice.cac:AccountingCustomerParty.cac:Party.cac:PhysicalLocation.cbc:Description' => 'max:100',
        );
        $messages = array(
            'Invoice.cac:AccountingCustomerParty.cbc:CustomerAssignedAccountID.required' => 'El número de documento de identidad es requerido.',
            'Invoice.cac:AccountingCustomerParty.cbc:CustomerAssignedAccountID.max' => 'El número de documento de identidad excedió los 15 caracteres.',
            'Invoice.cac:AccountingCustomerParty.cbc:AdditionalAccountID.required' => 'Tipo de documento de identificación es requerido.',
            'Invoice.cac:AccountingCustomerParty.cbc:AdditionalAccountID.size' => 'Tipo de documento de identificación no tiene 1 caracter.',
            'Invoice.cac:AccountingCustomerParty.cac:Party.cac:PartyLegalEntity.cbc:RegistrationName.required' => 'Apellidos y nombres o denominación o razón social según RUC es requerido.',
            'Invoice.cac:AccountingCustomerParty.cac:Party.cac:PartyLegalEntity.cbc:RegistrationName.max' => 'Apellidos y nombres o denominación o razón social según RUC excedió los 100 caracteres.',
            'Invoice.cac:AccountingCustomerParty.cac:Party.cac:PhysicalLocation.cbc:Description.max' => 'Dirección en el país Global del adquiriente o lugar de destino excedió los 100 caracteres.',
        );
        $validator = Validator::make($array, $validation, $messages);
        if ($validator->fails()) {
            Log::error('Error en cabecera: ' . implode(',', $validator->messages()->all()),
                ['lgph_id' => 2, 'c_id' => $array['Invoice']['cbc:ID'], 'c_invoice_type_code' => $array['Invoice']['cbc:InvoiceTypeCode'],]);
            throw new Exception(implode(',', $validator->messages()->all()));
        }

        if (isset($array['Invoice']['cac:SellerSupplierParty']['cac:Party']['cac:PostalAddress'])) {
            $validation = array(
                'Invoice.cac:SellerSupplierParty.cac:Party.cac:PostalAddress.cbc:ID' => 'size:6',
                'Invoice.cac:SellerSupplierParty.cac:Party.cac:PostalAddress.cbc:StreetName' => 'required|max:100',
                'Invoice.cac:SellerSupplierParty.cac:Party.cac:PostalAddress.cbc:CitySubdivisionName' => 'max:25',
                'Invoice.cac:SellerSupplierParty.cac:Party.cac:PostalAddress.cbc:CityName' => 'max:30',
                'Invoice.cac:SellerSupplierParty.cac:Party.cac:PostalAddress.cbc:CountrySubentity' => 'max:30',
                'Invoice.cac:SellerSupplierParty.cac:Party.cac:PostalAddress.cbc:District' => 'max:30',
                'Invoice.cac:SellerSupplierParty.cac:Party.cac:PostalAddress.cac:Country.cbc:IdentificationCode' => 'size:2',
            );
            $messages = array(
                'Invoice.cac:SellerSupplierParty.cac:Party.cac:PostalAddress.cbc:ID.size' => 'Invoice.cac:SellerSupplierParty.cac:Party.cac:PostalAddress.cbc:ID El código de UBIGEO no tiene 6 caracteres.',
                'Invoice.cac:SellerSupplierParty.cac:Party.cac:PostalAddress.cbc:StreetName.required' => 'Invoice.cac:SellerSupplierParty.cac:Party.cac:PostalAddress.cbc:StreetName La dirección completa y detallada es requerida.',
                'Invoice.cac:SellerSupplierParty.cac:Party.cac:PostalAddress.cbc:StreetName.max' => 'Invoice.cac:SellerSupplierParty.cac:Party.cac:PostalAddress.cbc:StreetName La dirección completa y detallada excedió los 100 caracteres.',
                'Invoice.cac:SellerSupplierParty.cac:Party.cac:PostalAddress.cbc:CitySubdivisionName.max' => 'Invoice.cac:SellerSupplierParty.cac:Party.cac:PostalAddress.cbc:CitySubdivisionName La ubicación o zona excedió los 25 caracteres.',
                'Invoice.cac:SellerSupplierParty.cac:Party.cac:PostalAddress.cbc:CityName.max' => 'Invoice.cac:SellerSupplierParty.cac:Party.cac:PostalAddress.cbc:CityName El departamento excedió los 30 caracteres.',
                'Invoice.cac:SellerSupplierParty.cac:Party.cac:PostalAddress.cbc:CountrySubentity.max' => 'Invoice.cac:SellerSupplierParty.cac:Party.cac:PostalAddress.cbc:CountrySubentity La provincia excedió los 30 caracteres.',
                'Invoice.cac:SellerSupplierParty.cac:Party.cac:PostalAddress.cbc:District.max' => 'Invoice.cac:SellerSupplierParty.cac:Party.cac:PostalAddress.cbc:District El distrito excedió los 30 caracteres.',
                'Invoice.cac:SellerSupplierParty.cac:Party.cac:PostalAddress.cac:Country.cbc:IdentificationCode.size' => 'Invoice.cac:SellerSupplierParty.cac:Party.cac:PostalAddress.cac:Country.cbc:IdentificationCode El código del pais no tiene 2 caracteres.',
            );
            $validator = Validator::make($array, $validation, $messages);
            if ($validator->fails()) {
                Log::error('Error en cabecera: ' . implode(',', $validator->messages()->all()),
                    ['lgph_id' => 2, 'c_id' => $array['Invoice']['cbc:ID'], 'c_invoice_type_code' => $array['Invoice']['cbc:InvoiceTypeCode'],]);
                throw new Exception(implode(',', $validator->messages()->all()));
            }
        }

        if (isset($array['Invoice']['cac:TaxTotal'])) {
            foreach ($array['Invoice']['cac:TaxTotal'] as $value) {
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
                        ['lgph_id' => 2, 'c_id' => $array['Invoice']['cbc:ID'], 'c_invoice_type_code' => $array['Invoice']['cbc:InvoiceTypeCode'],]);
                    throw new Exception(implode(',', $validator->messages()->all()));
                }
            }
        }

        $validation = array(
            'Invoice.cac:LegalMonetaryTotal.cbc:ChargeTotalAmount' => 'max:15',
            'Invoice.cac:LegalMonetaryTotal.cbc:PayableAmount' => 'required|max:15',
        );
        $messages = array(
            'Invoice.cac:LegalMonetaryTotal.cbc:ChargeTotalAmount.max' => 'Importe total de cargos aplicados al total de la factura excedió los 15 caracteres.',
            'Invoice.cac:LegalMonetaryTotal.cbc:PayableAmount.required' => 'Moneda e Importe total a pagar es requerido.',
            'Invoice.cac:LegalMonetaryTotal.cbc:PayableAmount.max' => 'Moneda e Importe total a pagar excedió los 15 caracteres.',
        );
        $validator = Validator::make($array, $validation, $messages);
        if ($validator->fails()) {
            Log::error('Error en cabecera: ' . implode(',', $validator->messages()->all()),
                ['lgph_id' => 2, 'c_id' => $array['Invoice']['cbc:ID'], 'c_invoice_type_code' => $array['Invoice']['cbc:InvoiceTypeCode'],]);
            throw new Exception(implode(',', $validator->messages()->all()));
        }

        foreach ($array['Invoice']['cac:InvoiceLine'] as $key => $value) {
            $itemIndex = $key + 1;
            $validation = array(
                'cbc:ID' => 'required|max:3',
                'cbc:InvoicedQuantity.amount' => 'required|max:16',
                'cbc:InvoicedQuantity.@unitCode' => 'required|max:3',
                'cbc:LineExtensionAmount' => 'required|max:15',
                'cac:Item.cbc:Description' => 'required|max:250',
                'cac:Item.cac:SellersItemIdentification.cbc:ID' => 'max:30',
                'cac:Price.cbc:PriceAmount' => 'required|max:15',
            );
            $messages = array(
                'cbc:ID.required' => 'Número de orden del Ítem es requerido.',
                'cbc:ID.max' => 'Número de orden del Ítem excedió los 3 caracteres.',
                'cbc:InvoicedQuantity.@unitCode.required' => 'Unidad de medida por Ítem (UN/ECE rec 20) es requerido.',
                'cbc:InvoicedQuantity.@unitCode.max' => 'Unidad de medida por Ítem (UN/ECE rec 20) excedió los 3 caracteres.',
                'cbc:InvoicedQuantity.amount.required' => 'Cantidad de unidades por Ítem es requerido.',
                'cbc:InvoicedQuantity.amount.max' => 'Cantidad de unidades por Ítem excedió los 3 caracteres.',
                'cbc:LineExtensionAmount.required' => 'Moneda e Importe monetario que es el total de la línea de detalle, incluyendo variaciones de precio (subvenciones, cargos o descuentos) pero sin impuestos es requerido.',
                'cbc:LineExtensionAmount.max' => 'Moneda e Importe monetario que es el total de la línea de detalle, incluyendo variaciones de precio (subvenciones, cargos o descuentos) pero sin impuestos excedió los 15 caraceteres.',
                'cac:Item.cbc:Description.required' => 'Descripción detallada del bien vendido o cedido en uso, descripción o tipo de servicio prestado por ítem es requerido.',
                'cac:Item.cbc:Description.max' => 'Descripción detallada del bien vendido o cedido en uso, descripción o tipo de servicio prestado por ítem excedió los 250 caracteres.',
                'cac:Item.cac:SellersItemIdentification.cbc:ID.max' => 'Código del producto excedió los 30 caracteres.',
                'cac:Price.cbc:PriceAmount.required' => 'Valores de venta unitarios por ítem (VU) no incluye impuestos es requerido.',
                'cac:Price.cbc:PriceAmount.max' => 'Valores de venta unitarios por ítem (VU) no incluye impuestos excedió los 15 caracteres.',
            );
            $validator = Validator::make($value, $validation, $messages);
            if ($validator->fails()) {
                Log::error("Error en detalle, Ítem #{$itemIndex}: " . implode(',', $validator->messages()->all()),
                    ['lgph_id' => 2, 'c_id' => $array['Invoice']['cbc:ID'], 'c_invoice_type_code' => $array['Invoice']['cbc:InvoiceTypeCode'],
                    'c_item_sellers_item_identification_id' => $value['cac:Item']['cac:SellersItemIdentification']['cbc:ID']]);
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
                        ['lgph_id' => 2, 'c_id' => $array['Invoice']['cbc:ID'], 'c_invoice_type_code' => $array['Invoice']['cbc:InvoiceTypeCode'],
                        'c_item_sellers_item_identification_id' => $value['cac:Item']['cac:SellersItemIdentification']['cbc:ID']]);
                    throw new Exception(implode(',', $validator->messages()->all()));
                }
            }

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
                        ['lgph_id' => 2, 'c_id' => $array['Invoice']['cbc:ID'], 'c_invoice_type_code' => $array['Invoice']['cbc:InvoiceTypeCode'],
                        'c_item_sellers_item_identification_id' => $value['cac:Item']['cac:SellersItemIdentification']['cbc:ID']]);
                    throw new Exception(implode(',', $validator->messages()->all()));
                }
            }
        }

        Log::info('Validación de array',
            [
            'lgph_id' => 2, 'c_id' => $array['Invoice']['cbc:ID'],
            'c_invoice_type_code' => $array['Invoice']['cbc:InvoiceTypeCode'],
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
            $supSupplierArray = $array['Invoice']['cac:AccountingSupplierParty'];
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

            if (isset($supSupplierArray['cac:Party']['cac:PostalAddress']['cbc:ID']) &&
                !empty($supSupplierArray['cac:Party']['cac:PostalAddress']['cbc:ID'])) {
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
            $supSupplier->save();
            $supSupplierId = $supSupplier->n_id_supplier;

            # CLIENTE
            $cusCustomerArray = $array['Invoice']['cac:AccountingCustomerParty'];
            $cusCustomer = CusCustomer::where('n_id_supplier', $supSupplierId)
                ->where('c_customer_assigned_account_id', $cusCustomerArray['cbc:CustomerAssignedAccountID'])
                ->where('c_additional_account_id', $cusCustomerArray['cbc:AdditionalAccountID']);

            if ($cusCustomer->count()) {
                $cusCustomer = $cusCustomer->first();
            } else {
                $cusCustomer = new CusCustomer();
                $cusCustomer->n_id_supplier = $supSupplierId;
                $cusCustomer->c_customer_assigned_account_id = $cusCustomerArray['cbc:CustomerAssignedAccountID'];
            }

            $cusCustomer->c_additional_account_id = $cusCustomerArray['cbc:AdditionalAccountID'];
            $cusCustomer->c_party_party_legal_entity_registration_name = $cusCustomerArray['cac:Party']['cac:PartyLegalEntity']
                ['cbc:RegistrationName'];
            $cusCustomer->c_party_physical_location_description = (isset($cusCustomerArray['cac:Party']
                    ['cac:PhysicalLocation']['cbc:Description'])) ? $cusCustomerArray['cac:Party']['cac:PhysicalLocation']
                ['cbc:Description'] : NULL;
            $cusCustomer->save();
            $cusCustomerId = $cusCustomer->n_id_customer;

            #BOLETA DE VENTA
            $docInvoiceArray = $array['Invoice'];
            $docInvoice = DocInvoice::where('c_id', $docInvoiceArray['cbc:ID'])->where('n_id_supplier', $supSupplierId)
                ->where('c_invoice_type_code', $docInvoiceArray['cbc:InvoiceTypeCode'])
                ->whereIn('c_status_invoice', array('visible', 'hidden'));
            if ($docInvoice->count()) {
                $docInvoice->update(array('c_status_invoice' => 'deleted'));
            }
            $docInvoice = new DocInvoice();
            $docInvoice->n_id_customer = $cusCustomerId;
            $docInvoice->n_id_supplier = $supSupplierId;
            $docInvoice->d_issue_date = $docInvoiceArray['cbc:IssueDate'];
            $docInvoice->c_invoice_type_code = $docInvoiceArray['cbc:InvoiceTypeCode'];
            $docInvoice->c_id = $docInvoiceArray['cbc:ID'];
            $docInvoice->c_document_currency_code = $docInvoiceArray['cbc:DocumentCurrencyCode'];
            $docInvoice->c_ubl_version_id = $docInvoiceArray['cbc:UBLVersionID'];
            $docInvoice->c_customization_id = $docInvoiceArray['cbc:CustomizationID'];
            $docInvoice->c_status_invoice = 'visible';
            $cIdExplode = explode('-', $docInvoiceArray['cbc:ID']);
            $docInvoice->c_correlative = end($cIdExplode);
            $docInvoice->n_correlative = (int) end($cIdExplode);
            $docInvoice->c_serie = reset($cIdExplode);
            if (
                isset(
                    $docInvoiceArray['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']
                    ['sac:AdditionalInformation']['sac:SUNATTransaction']['cbc:ID']
                )
            ) {
                $docInvoice->c_additional_information_sunat_transaction_id = $docInvoiceArray['ext:UBLExtensions']
                    ['ext:UBLExtension']['ext:ExtensionContent']['sac:AdditionalInformation']['sac:SUNATTransaction']
                    ['cbc:ID'];
            }
            $docInvoice->save();
            $docInvoiceId = $docInvoice->n_id_invoice;

            # doc_seller_supplier_party
            # Direccion del lugar en el que se entrega el bien o se presta el servicio
            if (
                isset($docInvoiceArray['cac:SellerSupplierParty']['cac:Party']['cac:PostalAddress']['cbc:ID']) &&
                !empty($docInvoiceArray['cac:SellerSupplierParty']['cac:Party']['cac:PostalAddress']['cbc:ID'])
            ) {
                $sellerSupplierPartyArray = $docInvoiceArray['cac:SellerSupplierParty'];
                $sellerSupplierParty = new DocSellerSupplierParty();
                $sellerSupplierParty->n_id_invoice = $docInvoiceId;
                $sellerSupplierParty->ssp_party_postal_address_id = $sellerSupplierPartyArray['cac:Party']
                    ['cac:PostalAddress']['cbc:ID'];
                $sellerSupplierParty->ssp_party_postal_address_street_name = $sellerSupplierPartyArray['cac:Party']
                    ['cac:PostalAddress']['cbc:StreetName'];
                $sellerSupplierParty->ssp_party_postal_address_city_subdivision_name = $sellerSupplierPartyArray
                    ['cac:Party']['cac:PostalAddress']['cbc:CitySubdivisionName'];
                $sellerSupplierParty->ssp_party_postal_address_city_name = $sellerSupplierPartyArray['cac:Party']
                    ['cac:PostalAddress']['cbc:CityName'];
                $sellerSupplierParty->ssp_party_postal_address_country_subentity = $sellerSupplierPartyArray
                    ['cac:Party']['cac:PostalAddress']['cbc:CountrySubentity'];
                $sellerSupplierParty->ssp_party_postal_address_district = $sellerSupplierPartyArray['cac:Party']
                    ['cac:PostalAddress']['cbc:District'];
                $sellerSupplierParty->ssp_party_postal_address_country_identification_code = $sellerSupplierPartyArray
                    ['cac:Party']['cac:PostalAddress']['cac:Country']['cbc:IdentificationCode'];
                $sellerSupplierParty->save();
            }

            $docInvoiceCdrStatus = new DocInvoiceCdrStatus();
            $docInvoiceCdrStatus->n_id_invoice = $docInvoiceId;
            $docInvoiceCdrStatus->n_id_cdr_status = 4;
            $docInvoiceCdrStatus->save();

            if (isset($docInvoiceArray['cac:PrepaidPayment'])) {
                foreach ($docInvoiceArray['cac:PrepaidPayment'] as $value) {
                    $docInvoiceAnticipos = new DocInvoiceAnticipos();
                    $docInvoiceAnticipos->n_id_invoice = $docInvoice->n_id_invoice;
                    $docInvoiceAnticipos->ant_paid_amount = $value['cbc:PaidAmount'];
                    $docInvoiceAnticipos->ant_cbc_id = $value['cbc:ID']['value'];
                    $docInvoiceAnticipos->ant_cbc_id_scheme_id = $value['cbc:ID']['@schemeID'];
                    $docInvoiceAnticipos->ant_instruction_id = $value['cbc:InstructionID']['value'];
                    $docInvoiceAnticipos->ant_instruction_id_scheme_id = $value['cbc:InstructionID']['@schemeID'];
                    $docInvoiceAnticipos->save();
                }
            }

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
                    $docInvoiceAdditionalInformationAdditionalProperty->c_value = (isset($value['cbc:Value'])) ?
                        $value['cbc:Value'] : NULL;
                    $docInvoiceAdditionalInformationAdditionalProperty->save();
                }
            }

            if (isset($array['Invoice']['ext:UBLExtensions']['ext:UBLExtension']['ext:ExtensionContent']
                    ['sac:AdditionalInformation']['sac:SUNATEmbededDespatchAdvice'])) {
                $sunatEmbededDespatchAdvice = $array['Invoice']['ext:UBLExtensions']['ext:UBLExtension']
                    ['ext:ExtensionContent']['sac:AdditionalInformation']['sac:SUNATEmbededDespatchAdvice'];
                if (isset($sunatEmbededDespatchAdvice['cac:DeliveryAddress'])) {
                    $docInvoiceSunatEmbededDespatchAdvice = new DocInvoiceSunatEmbededDespatchAdvice();
                    $docInvoiceSunatEmbededDespatchAdvice->n_id_invoice = $docInvoiceId;
                    $docInvoiceSunatEmbededDespatchAdvice->save();

                    $deliveryAddress = $sunatEmbededDespatchAdvice['cac:DeliveryAddress'];
                    $docInvoiceSunatEmbededDespatchAdviceOriginDelivery = new
                        DocInvoiceSunatEmbededDespatchAdviceOriginDelivery();
                    $docInvoiceSunatEmbededDespatchAdviceOriginDelivery->n_id_invoice = $docInvoiceId;
                    $docInvoiceSunatEmbededDespatchAdviceOriginDelivery->isedaod_type = 'delivery';
                    if (isset($deliveryAddress['cbc:ID'])) {
                        $docInvoiceSunatEmbededDespatchAdviceOriginDelivery->isedaod_address_id = $deliveryAddress['cbc:ID'];
                    }
                    if (isset($deliveryAddress['cbc:StreetName'])) {
                        $docInvoiceSunatEmbededDespatchAdviceOriginDelivery->isedaod_street_name = $deliveryAddress['cbc:StreetName'];
                    }
                    if (isset($deliveryAddress['cbc:CitySubdivisionName'])) {
                        $docInvoiceSunatEmbededDespatchAdviceOriginDelivery->isedaod_city_subdivision_name = $deliveryAddress['cbc:CitySubdivisionName'];
                    }
                    if (isset($deliveryAddress['cbc:CityName'])) {
                        $docInvoiceSunatEmbededDespatchAdviceOriginDelivery->isedaod_city_name = $deliveryAddress['cbc:CityName'];
                    }
                    if (isset($deliveryAddress['cbc:CountrySubentity'])) {
                        $docInvoiceSunatEmbededDespatchAdviceOriginDelivery->isedaod_country_subentity = $deliveryAddress['cbc:CountrySubentity'];
                    }
                    if (isset($deliveryAddress['cbc:District'])) {
                        $docInvoiceSunatEmbededDespatchAdviceOriginDelivery->isedaod_district = $deliveryAddress['cbc:District'];
                    }
                    if (isset($deliveryAddress['cac:Country']['cbc:IdentificationCode'])) {
                        $docInvoiceSunatEmbededDespatchAdviceOriginDelivery->isedaod_country_identification_code = $deliveryAddress['cac:Country']['cbc:IdentificationCode'];
                    }
                    $docInvoiceSunatEmbededDespatchAdviceOriginDelivery->save();
                }
            }

            $docInvoiceLegalMonetaryTotalArray = $docInvoiceArray['cac:LegalMonetaryTotal'];
            $docInvoiceLegalMonetaryTotal = new DocInvoiceLegalMonetaryTotal();
            $docInvoiceLegalMonetaryTotal->n_id_invoice = $docInvoiceId;
            $docInvoiceLegalMonetaryTotal->c_charge_total_amount = (isset(
                    $docInvoiceLegalMonetaryTotalArray['cbc:ChargeTotalAmount'])) ?
                $docInvoiceLegalMonetaryTotalArray['cbc:ChargeTotalAmount'] : NULL;
            $docInvoiceLegalMonetaryTotal->c_payable_amount = $docInvoiceLegalMonetaryTotalArray['cbc:PayableAmount'];
            $docInvoiceLegalMonetaryTotal->c_allowance_total_amount = (isset(
                    $docInvoiceLegalMonetaryTotalArray['cbc:AllowanceTotalAmount'])) ?
                $docInvoiceLegalMonetaryTotalArray['cbc:AllowanceTotalAmount'] : NULL;
            if (isset($docInvoiceLegalMonetaryTotalArray['cbc:PrepaidAmount'])) {
                $docInvoiceLegalMonetaryTotal->c_prepaid_amount = $docInvoiceLegalMonetaryTotalArray['cbc:PrepaidAmount'];
            }
            $docInvoiceLegalMonetaryTotal->save();

            $docInvoiceAdditionalInformationAdditionalMonetaryTotalArray = $docInvoiceArray['ext:UBLExtensions']
                ['ext:UBLExtension']['ext:ExtensionContent']['sac:AdditionalInformation']['sac:AdditionalMonetaryTotal'];
            foreach ($docInvoiceAdditionalInformationAdditionalMonetaryTotalArray as $key => $value) {
                $docInvoiceAdditionalInformationAdditionalMonetaryTotal = new
                    DocInvoiceAdditionalInformationAdditionalMonetaryTotal();
                $docInvoiceAdditionalInformationAdditionalMonetaryTotal->n_id_invoice = $docInvoiceId;
                $docInvoiceAdditionalInformationAdditionalMonetaryTotal->c_id = $value['cbc:ID'];
                $docInvoiceAdditionalInformationAdditionalMonetaryTotal->c_payable_amount = (isset($value['cbc:PayableAmount'])) ? $value['cbc:PayableAmount'] : NULL;
                $docInvoiceAdditionalInformationAdditionalMonetaryTotal->c_reference_amount = (isset($value['sac:ReferenceAmount'])) ? $value['sac:ReferenceAmount'] : NULL;
                $docInvoiceAdditionalInformationAdditionalMonetaryTotal->c_total_amount = (isset($value['sac:TotalAmount'])) ? $value['sac:TotalAmount'] : NULL;
                $docInvoiceAdditionalInformationAdditionalMonetaryTotal->save();
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

            if (isset($docInvoiceArray['cac:TaxTotal'])) {
                $docInvoiceTaxTotalArray = $docInvoiceArray['cac:TaxTotal'];
                foreach ($docInvoiceTaxTotalArray as $key => $value) {
                    $docInvoiceTaxTotal = new DocInvoiceTaxTotal();
                    $docInvoiceTaxTotal->n_id_invoice = $docInvoiceId;
                    $docInvoiceTaxTotal->c_tax_amount = $value['cbc:TaxAmount'];
                    $docInvoiceTaxTotal->save();
                    $docInvoiceTaxTotalId = $docInvoiceTaxTotal->n_id_invoice_tax_total;

                    $docInvoiceTaxTotalTaxSubtotal = new DocInvoiceTaxTotalTaxSubtotal();
                    $docInvoiceTaxTotalTaxSubtotalArray = $value['cac:TaxSubtotal'];
                    $docInvoiceTaxTotalTaxSubtotal->n_id_invoice_tax_total = $docInvoiceTaxTotalId;
                    $docInvoiceTaxTotalTaxSubtotal->c_tax_amount = $docInvoiceTaxTotalTaxSubtotalArray['cbc:TaxAmount'];
                    $docInvoiceTaxTotalTaxSubtotal->save();

                    $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxScheme = new
                        DocInvoiceTaxTotalTaxSubtotalTaxCategoryTaxScheme();
                    $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxSchemeArray = $value['cac:TaxSubtotal']['cac:TaxCategory']
                        ['cac:TaxScheme'];
                    $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxScheme->n_id_invoice_tax_total = $docInvoiceTaxTotalId;
                    $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxScheme->c_id = $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxSchemeArray['cbc:ID'];
                    $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxScheme->c_name = $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxSchemeArray['cbc:Name'];
                    $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxScheme->c_tax_type_code = $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxSchemeArray['cbc:TaxTypeCode'];
                    $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxScheme->save();
                }
            }

            $docInvoiceCustomer = new DocInvoiceCustomer();
            $docInvoiceCustomer->n_id_invoice = $docInvoiceId;
            $docInvoiceCustomer->n_id_customer = $cusCustomerId;
            $docInvoiceCustomer->c_customer_assigned_account_id = $cusCustomerArray['cbc:CustomerAssignedAccountID'];
            $docInvoiceCustomer->c_additional_account_id = $cusCustomerArray['cbc:AdditionalAccountID'];
            $docInvoiceCustomer->c_party_party_legal_entity_registration_name = $cusCustomerArray['cac:Party']
                ['cac:PartyLegalEntity']['cbc:RegistrationName'];
            $docInvoiceCustomer->c_party_physical_location_description = (isset($cusCustomerArray['cac:Party']
                    ['cac:PhysicalLocation']['cbc:Description'])) ? $cusCustomerArray['cac:Party']['cac:PhysicalLocation']
                ['cbc:Description'] : NULL;
            $docInvoiceCustomer->save();

            $docInvoiceSupplier = new DocInvoiceSupplier;
            $docInvoiceSupplier->n_id_invoice = $docInvoiceId;
            $docInvoiceSupplier->n_id_supplier = $supSupplierId;
            $docInvoiceSupplier->c_party_party_legal_entity_registration_name = $supSupplierArray['cac:Party']
                ['cac:PartyLegalEntity']['cbc:RegistrationName'];


            if (isset($supSupplierArray['cac:Party']['cac:PostalAddress']['cbc:ID']) &&
                !empty($supSupplierArray['cac:Party']['cac:PostalAddress']['cbc:ID'])) {
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

            $docInvoiceItemArray = $docInvoiceArray['cac:InvoiceLine'];

            foreach ($docInvoiceItemArray as $key => $value) {
                $docInvoiceItem = new DocInvoiceItem();
                $docInvoiceItem->n_id_invoice = $docInvoiceId;
                $docInvoiceItem->c_invoiced_quantity_unit_code = $value['cbc:InvoicedQuantity']['@unitCode'];
                $docInvoiceItem->c_invoiced_quantity = $value['cbc:InvoicedQuantity']['amount'];
                $docInvoiceItem->n_id = $value['cbc:ID'];
                $docInvoiceItem->c_item_sellers_item_identification_id = $value['cac:Item']['cac:SellersItemIdentificacion']
                    ['cbc:ID'];
                $docInvoiceItem->c_line_extension_amount = $value['cbc:LineExtensionAmount'];
                $docInvoiceItem->c_price_price_amount = $value['cac:Price']['cbc:PriceAmount'];
                $docInvoiceItem->save();
                $docInvoiceItemId = $docInvoiceItem->n_id_invoice_item;

                foreach ($value['cac:Item']['cbc:Description'] as $k => $v) {
                    $docInvoiceItemDescription = new DocInvoiceItemDescription();
                    $docInvoiceItemDescription->n_id_invoice_item = $docInvoiceItemId;
                    $docInvoiceItemDescription->n_index = $k;
                    $docInvoiceItemDescription->c_description = $v;
                    $docInvoiceItemDescription->save();
                }

                foreach ($value['cac:PricingReference']['cac:AlternativeConditionPrice'] as $k => $v) {
                    $docInvoiceItemPricingReferenceAlternativeConditionPrice = new
                        DocInvoiceItemPricingReferenceAlternativeConditionPrice();
                    $docInvoiceItemPricingReferenceAlternativeConditionPrice->n_id_invoice_item = $docInvoiceItemId;
                    $docInvoiceItemPricingReferenceAlternativeConditionPrice->c_price_amount = $v['cbc:PriceAmount'];
                    $docInvoiceItemPricingReferenceAlternativeConditionPrice->c_price_type_code = $v['cbc:PriceTypeCode'];
                    $docInvoiceItemPricingReferenceAlternativeConditionPrice->save();
                }

                if (isset($value['cac:AllowanceCharge']['cbc:ChargeIndicator']) &&
                    !empty($value['cac:AllowanceCharge']['cbc:ChargeIndicator'])) {
                    $docInvoiceItemAllowancecharge = new DocInvoiceItemAllowancecharge();
                    $docInvoiceItemAllowancecharge->n_id_invoice_item = $docInvoiceItemId;
                    $docInvoiceItemAllowancecharge->c_charge_indicator = $value['cac:AllowanceCharge']
                        ['cbc:ChargeIndicator'];
                    $docInvoiceItemAllowancecharge->c_amount = $value['cac:AllowanceCharge']['cbc:Amount'];
                    $docInvoiceItemAllowancecharge->save();
                }

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

                    if (isset($v['cac:TaxSubtotal']['cac:TaxCategory']['cbc:TaxExemptionReasonCode'])) {
                        $docInvoiceItemTaxTotalIgv = new DocInvoiceItemTaxTotalIgv();
                        $docInvoiceItemTaxTotalIgv->n_id_invoice_item_tax_total = $docInvoiceItemTaxTotalId;
                        $docInvoiceItemTaxTotalIgv->c_tax_subtotal_tax_category_tax_exemption_reason_code = $v
                            ['cac:TaxSubtotal']['cac:TaxCategory']['cbc:TaxExemptionReasonCode'];
                        $docInvoiceItemTaxTotalIgv->save();
                    }

                    if (isset($v['cac:TaxSubtotal']['cac:TaxCategory']['cbc:TierRange'])) {
                        $docInvoiceItemTaxTotalIsc = new DocInvoiceItemTaxTotalIsc();
                        $docInvoiceItemTaxTotalIsc->n_id_invoice_item_tax_total = $docInvoiceItemTaxTotalId;
                        $docInvoiceItemTaxTotalIsc->c_tax_subtotal_tax_category_tier_range = $v
                            ['cac:TaxSubtotal']['cac:TaxCategory']['cbc:TierRange'];
                        $docInvoiceItemTaxTotalIsc->save();
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
                'lgph_id' => 3, 'c_id' => $array['Invoice']['cbc:ID'], 'c_invoice_type_code' => $array['Invoice']['cbc:InvoiceTypeCode'],
                'n_id_invoice' => $docInvoiceId
                ]
            );
        } catch (Exception $exc) {
            $response['message'] = $exc->getMessage();

            Log::error($exc->getMessage(),
                [
                'lgph_id' => 3, 'c_id' => $array['Invoice']['cbc:ID'], 'c_invoice_type_code' => $array['Invoice']['cbc:InvoiceTypeCode'],
                ]
            );
        }

        return $response;
    }

    /**
     * Arma el XML desde el Id del Documento, retorna un bool STATUS y string MESSAGE.
     * 
     * @param int $invoiceId
     * @return array
     */
    public static function buildXml($billId)
    {
        /* Busqueda Sin Filtros, de eso se encarga otra capa. */
        $response['status'] = 0;
        $response['message'] = '';
        $response['path'] = '';

        try {
            $docInvoiceData = DocInvoice::with(
                    [
                        'DocInvoiceAdditionalInformationAdditionalMonetaryTotal',
                        'DocInvoiceAdditionalInformationAdditionalProperty', 'DocInvoiceDespatchDocumentReference',
                        'DocInvoiceAdditionalDocumentReference', 'DocInvoiceSupplier', 'DocInvoiceCustomer',
                        'DocInvoiceTaxTotal', 'DocInvoiceLegalMonetaryTotal', 'DocInvoiceItem',
                        'DocInvoiceSunatEmbededDespatchAdvice.DocInvoiceSunatEmbededDespatchAdviceOriginDelivery',
                        'DocInvoiceAnticipos', 'DocSellerSupplierParty', 'SupSupplier.SupSupplierConfiguration'
                    ]
                )->where('n_id_invoice', $billId);

            if ($docInvoiceData->count() == 0) {
                throw new Exception('No se encuentra en BD');
            }

            $docInvoiceData = $docInvoiceData->first();
            $signatureId = 'S' . $docInvoiceData->c_id;
            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->xmlStandalone = false;
            $dom->formatOutput = true;

            $invoice = $dom->createElement('Invoice');
            $newNode = $dom->appendChild($invoice);
            $newNode->setAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
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
            $invoice->appendChild($ublExtensions);

            # DocInvoiceAdditionalInformationAdditionalMonetaryTotal
            $docInvoiceAdditionalInformationAdditionalMonetaryTotal = $docInvoiceData
                ->DocInvoiceAdditionalInformationAdditionalMonetaryTotal;
            if (isset($docInvoiceAdditionalInformationAdditionalMonetaryTotal) &&
                !empty($docInvoiceAdditionalInformationAdditionalMonetaryTotal)) {
                $ublExtension = $dom->createElement('ext:UBLExtension');
                $ublExtensions->appendChild($ublExtension);
                $extensionContent = $dom->createElement('ext:ExtensionContent');
                $ublExtension->appendChild($extensionContent);
                $additionalInformation = $dom->createElement('sac:AdditionalInformation');
                $extensionContent->appendChild($additionalInformation);
                foreach ($docInvoiceAdditionalInformationAdditionalMonetaryTotal as $key => $value) {
                    $additionalMonetaryTotal = $dom->createElement('sac:AdditionalMonetaryTotal');
                    $additionalInformation->appendChild($additionalMonetaryTotal);

                    $id = $dom->createElement('cbc:ID');
                    $id->nodeValue = $value->c_id;
                    $additionalMonetaryTotal->appendChild($id);

                    if (isset($value->c_reference_amount) && !empty($value->c_reference_amount)) {
                        $referenceAmount = $dom->createElement('sac:ReferenceAmount');
                        $referenceAmount->nodeValue = $value->c_reference_amount;
                        $currencyId = $dom->createAttribute('currencyID');
                        $currencyId->nodeValue = $docInvoiceData->c_document_currency_code;
                        $referenceAmount->appendChild($currencyId);
                        $additionalMonetaryTotal->appendChild($referenceAmount);
                    }

                    if (isset($value->c_payable_amount) && !empty($value->c_payable_amount)) {
                        $payableAmount = $dom->createElement('cbc:PayableAmount');
                        $payableAmount->nodeValue = $value->c_payable_amount;
                        $currencyId = $dom->createAttribute('currencyID');
                        $currencyId->value = $docInvoiceData->c_document_currency_code;
                        $payableAmount->appendChild($currencyId);
                        $additionalMonetaryTotal->appendChild($payableAmount);
                    }

                    if (isset($value->c_total_amount) && !empty($value->c_total_amount)) {
                        $totalAmount = $dom->createElement('sac:TotalAmount');
                        $totalAmount->nodeValue = $value->c_total_amount;
                        $currencyId = $dom->createAttribute('currencyID');
                        $currencyId->value = $docInvoiceData->c_document_currency_code;
                        $totalAmount->appendChild($currencyId);
                        $additionalMonetaryTotal->appendChild($totalAmount);
                    }
                }
            }

            # DocInvoiceAdditionalInformationAdditionalProperty
            $docInvoiceAdditionalInformationAdditionalProperty = $docInvoiceData
                ->DocInvoiceAdditionalInformationAdditionalProperty;
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

            if (!empty($docInvoiceData->c_additional_information_sunat_transaction_id)) {
                $sunatTransaction = $dom->createElement('sac:SUNATTransaction');
                $additionalInformation->appendChild($sunatTransaction);

                $id = $dom->createElement('cbc:ID', $docInvoiceData->c_additional_information_sunat_transaction_id);
                $sunatTransaction->appendChild($id);
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
            $invoice->appendChild($ublVersionID);
            $customizationID = $dom->createElement('cbc:CustomizationID');
            $customizationID->nodeValue = $docInvoiceData->c_customization_id;
            $invoice->appendChild($customizationID);
            $id = $dom->createElement('cbc:ID');
            $id->nodeValue = $docInvoiceData->c_id;
            $invoice->appendChild($id);
            $issueDate = $dom->createElement('cbc:IssueDate');
            $issueDate->nodeValue = $docInvoiceData->d_issue_date;
            $invoice->appendChild($issueDate);
            $invoiceTypeCode = $dom->createElement('cbc:InvoiceTypeCode');
            $invoiceTypeCode->nodeValue = $docInvoiceData->c_invoice_type_code;
            $invoice->appendChild($invoiceTypeCode);
            $documentCurrencyCode = $dom->createElement('cbc:DocumentCurrencyCode');
            $documentCurrencyCode->nodeValue = $docInvoiceData->c_document_currency_code;
            $invoice->appendChild($documentCurrencyCode);
                

            # DocInvoiceDespatchDocumentReference
            $docInvoiceDespatchDocumentReference = $docInvoiceData->DocInvoiceDespatchDocumentReference;
            if ($docInvoiceDespatchDocumentReference->count()) {
                foreach ($docInvoiceDespatchDocumentReference as $key => $value) {
                    $despatchDocumentReference = $dom->createElement('cac:DespatchDocumentReference');
                    $invoice->appendChild($despatchDocumentReference);
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
                    $invoice->appendChild($additionalDocumentReference);
                    $id = $dom->createElement('cbc:ID', $value->c_id);
                    $documentTypeCode = $dom->createElement('cbc:DocumentTypeCode');
                    $documentTypeCode->nodeValue = $value->c_document_type_code;
                    $additionalDocumentReference->appendChild($id);
                    $additionalDocumentReference->appendChild($documentTypeCode);
                }
            }

            # DocInvoiceSunatEmbededDespatchAdvice
            if (!is_null($docInvoiceData->DocInvoiceSunatEmbededDespatchAdvice)) {
                $docInvoiceSunatEmbededDespatchAdvice = $docInvoiceData->DocInvoiceSunatEmbededDespatchAdvice;
                $sunatEmbededDespatchAdvice = $dom->createElement('sac:SUNATEmbededDespatchAdvice');
                $additionalInformation->appendChild($sunatEmbededDespatchAdvice);

                # DocInvoiceSunatEmbededDespatchAdviceOriginDelivery
                if ($docInvoiceSunatEmbededDespatchAdvice->DocInvoiceSunatEmbededDespatchAdviceOriginDelivery->count()) {
                    foreach ($docInvoiceSunatEmbededDespatchAdvice->DocInvoiceSunatEmbededDespatchAdviceOriginDelivery as $key => $value) {
                        if (empty($value->isedaod_type)) {
                            throw new Exception('No se definió si es partida/llegada la Guía Resumen');
                        }
                        switch ($value->isedaod_type) {
                            case 'origin':
                                $originDelivery = $dom->createElement('cac:OriginAddress');
                                break;
                            case 'delivery':
                                $originDelivery = $dom->createElement('cac:DeliveryAddress');
                                break;
                        }
                        $sunatEmbededDespatchAdvice->appendChild($originDelivery);

                        if (!empty($value->isedaod_address_id)) {
                            $id = $dom->createElement('cbc:ID', $value->isedaod_address_id);
                            $originDelivery->appendChild($id);
                        }
                        if (!empty($value->isedaod_street_name)) {
                            $streetName = $dom->createElement('cbc:StreetName');
                            $streetName->appendChild($dom->createCDATASection($value->isedaod_street_name));
                            $originDelivery->appendChild($streetName);
                        }
                        if (!empty($value->isedaod_city_subdivision_name)) {
                            $citySubdivisionName = $dom->createElement('cbc:CitySubdivisionName');
                            $citySubdivisionName->appendChild($dom->createCDATASection($value->isedaod_city_subdivision_name));
                            $originDelivery->appendChild($citySubdivisionName);
                        }
                        if (!empty($value->isedaod_city_name)) {
                            $cityName = $dom->createElement('cbc:CityName');
                            $cityName->appendChild($dom->createCDATASection($value->isedaod_city_name));
                            $originDelivery->appendChild($cityName);
                        }
                        if (!empty($value->isedaod_country_subentity)) {
                            $countrySubentity = $dom->createElement('cbc:CountrySubentity');
                            $countrySubentity->appendChild($dom->createCDATASection($value->isedaod_country_subentity));
                            $originDelivery->appendChild($countrySubentity);
                        }
                        if (!empty($value->isedaod_district)) {
                            $district = $dom->createElement('cbc:District');
                            $district->appendChild($dom->createCDATASection($value->isedaod_district));
                            $originDelivery->appendChild($district);
                        }
                        if (!empty($value->isedaod_country_identification_code)) {
                            $country = $dom->createElement('cac:Country');
                            $originDelivery->appendChild($country);
                            $identificationCode = $dom->createElement('cbc:IdentificationCode');
                            $identificationCode->appendChild($dom->createCDATASection($value->isedaod_country_identification_code));
                            $country->appendChild($identificationCode);
                        }
                    }
                }
            }

            # Firma
            $signature = $dom->createElement('cac:Signature');
            $invoice->appendChild($signature);
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
            $docInvoiceSupplier = $docInvoiceData->DocInvoiceSupplier;
            $accountingSupplierParty = $dom->createElement('cac:AccountingSupplierParty');
            $invoice->appendChild($accountingSupplierParty);
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
                $postalAddress->appendChild($id);
                $streetName = $dom->createElement('cbc:StreetName');
                $streetName->appendChild($dom->createCDATASection(
                        $docInvoiceSupplier->c_party_postal_address_street_name));
                $postalAddress->appendChild($streetName);
                $citySubdivisionName = $dom->createElement('cbc:CitySubdivisionName');
                $citySubdivisionName->appendChild($dom->createCDATASection(
                        $docInvoiceSupplier->c_party_postal_address_city_subdivision_name));
                $postalAddress->appendChild($citySubdivisionName);
                $cityName = $dom->createElement('cbc:CityName');
                $cityName->appendChild($dom->createCDATASection($docInvoiceSupplier->c_party_postal_address_city_name));
                $postalAddress->appendChild($cityName);
                $countrySubentity = $dom->createElement('cbc:CountrySubentity');
                $countrySubentity->appendChild($dom->createCDATASection($docInvoiceSupplier
                        ->c_party_postal_address_country_subentity));
                $postalAddress->appendChild($countrySubentity);
                $district = $dom->createElement('cbc:District');
                $district->appendChild($dom->createCDATASection($docInvoiceSupplier->c_party_postal_address_district));
                $postalAddress->appendChild($district);
                $country = $dom->createElement('cac:Country');
                $postalAddress->appendChild($country);
                $identificationCode = $dom->createElement('cbc:IdentificationCode');
                $identificationCode->appendChild($dom->createCDATASection(
                        $docInvoiceSupplier->c_party_postal_address_country_identification_code));
                $country->appendChild($identificationCode);
            }

            $partyLegalEntity = $dom->createElement('cac:PartyLegalEntity');
            $party->appendChild($partyLegalEntity);
            $registrationName = $dom->createElement('cbc:RegistrationName');
            $registrationName->appendChild($dom->createCDATASection($docInvoiceSupplier->c_party_party_legal_entity_registration_name));
            $partyLegalEntity->appendChild($registrationName);

            # DocInvoiceCustomer
            $docInvoiceCustomer = $docInvoiceData->docInvoiceCustomer;
            $accountingCustomerParty = $dom->createElement('cac:AccountingCustomerParty');
            $invoice->appendChild($accountingCustomerParty);
            $customerAssignedAccountId = $dom->createElement('cbc:CustomerAssignedAccountID');
            $customerAssignedAccountId->nodeValue = $docInvoiceCustomer->c_customer_assigned_account_id;
            $accountingCustomerParty->appendChild($customerAssignedAccountId);
            $additionalAccountId = $dom->createElement('cbc:AdditionalAccountID');
            $additionalAccountId->nodeValue = $docInvoiceCustomer->c_additional_account_id;
            $accountingCustomerParty->appendChild($additionalAccountId);

            $party = $dom->createElement('cac:Party');
            $accountingCustomerParty->appendChild($party);

            if (isset($docInvoiceCustomer->c_party_physical_location_description) &&
                !empty($docInvoiceCustomer->c_party_physical_location_description)) {
                $physicalLocation = $dom->createElement('cac:PhysicalLocation');
                $party->appendChild($physicalLocation);
                $description = $dom->createElement('cbc:Description');
                $description->appendChild($dom->createCDATASection($docInvoiceCustomer
                        ->c_party_physical_location_description));
                $physicalLocation->appendChild($description);
            }

            $partyLegalEntity = $dom->createElement('cac:PartyLegalEntity');
            $party->appendChild($partyLegalEntity);
            $registrationName = $dom->createElement('cbc:RegistrationName');
            $registrationName->appendChild($dom->createCDATASection($docInvoiceCustomer
                    ->c_party_party_legal_entity_registration_name));
            $partyLegalEntity->appendChild($registrationName);
            
            # DocSellerSupplierParty
            if (isset($docInvoiceData->DocSellerSupplierParty) && !empty($docInvoiceData->DocSellerSupplierParty)) {
                $docSellerSupplierParty = $docInvoiceData->DocSellerSupplierParty;

                $sellerSupplierParty = $dom->createElement('cac:SellerSupplierParty');
                $invoice->appendChild($sellerSupplierParty);
                $party = $dom->createElement('cac:Party');
                $sellerSupplierParty->appendChild($party);
                $postalAddress = $dom->createElement('cac:PostalAddress');
                $party->appendChild($postalAddress);

                $id = $dom->createElement('cbc:ID');
                $id->nodeValue = $docSellerSupplierParty->ssp_party_postal_address_id;
                $postalAddress->appendChild($id);
                $streetName = $dom->createElement('cbc:StreetName');
                $streetName->appendChild($dom->createCDATASection(
                        $docSellerSupplierParty->ssp_party_postal_address_street_name));
                $postalAddress->appendChild($streetName);
                $citySubdivisionName = $dom->createElement('cbc:CitySubdivisionName');
                $citySubdivisionName->appendChild($dom->createCDATASection(
                        $docSellerSupplierParty->ssp_party_postal_address_city_subdivision_name));
                $postalAddress->appendChild($citySubdivisionName);
                $cityName = $dom->createElement('cbc:CityName');
                $cityName->appendChild($dom->createCDATASection($docSellerSupplierParty->ssp_party_postal_address_city_name));
                $postalAddress->appendChild($cityName);
                $countrySubentity = $dom->createElement('cbc:CountrySubentity');
                $countrySubentity->appendChild($dom->createCDATASection($docSellerSupplierParty
                        ->ssp_party_postal_address_country_subentity));
                $postalAddress->appendChild($countrySubentity);
                $district = $dom->createElement('cbc:District');
                $district->appendChild($dom->createCDATASection($docSellerSupplierParty->ssp_party_postal_address_district));
                $postalAddress->appendChild($district);
                $country = $dom->createElement('cac:Country');
                $postalAddress->appendChild($country);
                $identificationCode = $dom->createElement('cbc:IdentificationCode');
                $identificationCode->appendChild($dom->createCDATASection(
                        $docSellerSupplierParty->ssp_party_postal_address_country_identification_code));
                $country->appendChild($identificationCode);
            }

            # DocInvoiceAnticipos
            $docInvoiceAnticipos = $docInvoiceData->DocInvoiceAnticipos;
            if (!is_null($docInvoiceAnticipos)) {
                foreach ($docInvoiceAnticipos as $key => $value) {
                    $prepaidPayment = $dom->createElement('cac:PrepaidPayment');
                    $invoice->appendChild($prepaidPayment);

                    $id = $dom->createElement('cbc:ID');
                    $id->nodeValue = $value->ant_cbc_id;
                    $schemeID = $dom->createAttribute('schemeID');
                    $schemeID->value = $value->ant_cbc_id_scheme_id;
                    $id->appendChild($schemeID);
                    $prepaidPayment->appendChild($id);

                    $paidAmount = $dom->createElement('cbc:PaidAmount');
                    $paidAmount->nodeValue = $value->ant_paid_amount;
                    $currencyId = $dom->createAttribute('currencyID');
                    $currencyId->value = $docInvoiceData->c_document_currency_code;
                    $paidAmount->appendChild($currencyId);
                    $prepaidPayment->appendChild($paidAmount);

                    $instructionID = $dom->createElement('cbc:InstructionID');
                    $instructionID->nodeValue = $value->ant_instruction_id;
                    $schemeID = $dom->createAttribute('schemeID');
                    $schemeID->value = $value->ant_instruction_id_scheme_id;
                    $instructionID->appendChild($schemeID);
                    $prepaidPayment->appendChild($instructionID);
                }
            }

            # DocInvoiceTaxTotal
            $docInvoiceTaxTotalData = $docInvoiceData->DocInvoiceTaxTotal;
            if (!is_null($docInvoiceTaxTotalData)) {
                foreach ($docInvoiceTaxTotalData as $key => $value) {
                    $taxTotal = $dom->createElement('cac:TaxTotal');
                    $invoice->appendChild($taxTotal);
                    $taxAmount = $dom->createElement('cbc:TaxAmount', $value->c_tax_amount);
                    $currencyId = $dom->createAttribute('currencyID');
                    $currencyId->nodeValue = $docInvoiceData->c_document_currency_code;
                    $taxAmount->appendChild($currencyId);
                    $taxTotal->appendChild($taxAmount);

                    $docInvoiceTaxTotalTaxSubtotalData = $value->DocInvoiceTaxTotalTaxSubtotal;
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

                    $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxSchemeData = $value->DocInvoiceTaxTotalTaxSubtotalTaxCategoryTaxScheme;
                    $id = $dom->createElement('cbc:ID', $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxSchemeData->c_id);
                    $taxScheme->appendChild($id);
                    $name = $dom->createElement('cbc:Name',
                        $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxSchemeData->c_name);
                    $taxScheme->appendChild($name);
                    $taxTypeCode = $dom->createElement('cbc:TaxTypeCode',
                        $docInvoiceTaxTotalTaxSubtotalTaxCategoryTaxSchemeData->c_tax_type_code);
                    $taxScheme->appendChild($taxTypeCode);
                }
            }
            # DocInvoiceLegalMonetaryTotal
            $docInvoiceLegalMonetaryTotal = $docInvoiceData->DocInvoiceLegalMonetaryTotal;
            if (!is_null($docInvoiceLegalMonetaryTotal)) {
                $legalMonetaryTotal = $dom->createElement('cac:LegalMonetaryTotal');
                $invoice->appendChild($legalMonetaryTotal);

                if (isset($docInvoiceLegalMonetaryTotal->c_allowance_total_amount) &&
                    !empty($docInvoiceLegalMonetaryTotal->c_allowance_total_amount)) {
                    $allowanceTotalAmount = $dom->createElement('cbc:AllowanceTotalAmount');
                    $allowanceTotalAmount->nodeValue = $docInvoiceLegalMonetaryTotal->c_allowance_total_amount;
                    $currencyId = $dom->createAttribute('currencyID');
                    $currencyId->value = $docInvoiceData->c_document_currency_code;
                    $allowanceTotalAmount->appendChild($currencyId);
                    $legalMonetaryTotal->appendChild($allowanceTotalAmount);
                }

                if (isset($docInvoiceLegalMonetaryTotal->c_charge_total_amount) &&
                    !empty($docInvoiceLegalMonetaryTotal->c_charge_total_amount)) {
                    $chargeTotalAmount = $dom->createElement('cbc:ChargeTotalAmount');
                    $chargeTotalAmount->nodeValue = $docInvoiceLegalMonetaryTotal->c_charge_total_amount;
                    $currencyId = $dom->createAttribute('currencyID');
                    $currencyId->value = $docInvoiceData->c_document_currency_code;
                    $chargeTotalAmount->appendChild($currencyId);
                    $legalMonetaryTotal->appendChild($chargeTotalAmount);
                }

                if (isset($docInvoiceLegalMonetaryTotal->c_prepaid_amount) &&
                    !empty($docInvoiceLegalMonetaryTotal->c_prepaid_amount)) {
                    $prepaidAmount = $dom->createElement('cbc:PrepaidAmount');
                    $prepaidAmount->nodeValue = $docInvoiceLegalMonetaryTotal->c_prepaid_amount;
                    $currencyId = $dom->createAttribute('currencyID');
                    $currencyId->value = $docInvoiceData->c_document_currency_code;
                    $prepaidAmount->appendChild($currencyId);
                    $legalMonetaryTotal->appendChild($prepaidAmount);
                }

                $payableAmount = $dom->createElement('cbc:PayableAmount');
                $payableAmount->nodeValue = $docInvoiceLegalMonetaryTotal->c_payable_amount;
                $legalMonetaryTotal->appendChild($payableAmount);
                $currencyId = $dom->createAttribute('currencyID');
                $currencyId->value = $docInvoiceData->c_document_currency_code;
                $payableAmount->appendChild($currencyId);
            }

            $docInvoiceItemData = $docInvoiceData->DocInvoiceItem;
            # DocInvoiceItems
            foreach ($docInvoiceItemData as $key => $value) {
                $invoiceLine = $dom->createElement('cac:InvoiceLine');
                $invoice->appendChild($invoiceLine);

                $id = $dom->createElement('cbc:ID');
                $id->nodeValue = $value->n_id;
                $invoiceLine->appendChild($id);
                $invoicedQuantity = $dom->createElement('cbc:InvoicedQuantity');
                $invoicedQuantity->nodeValue = $value->c_invoiced_quantity;
                $unitCode = $dom->createAttribute('unitCode');
                $unitCode->value = $value->c_invoiced_quantity_unit_code;
                $invoicedQuantity->appendChild($unitCode);
                $invoiceLine->appendChild($invoicedQuantity);

                $lineExtensionAmount = $dom->createElement('cbc:LineExtensionAmount');
                $lineExtensionAmount->nodeValue = $value->c_line_extension_amount;
                $invoiceLine->appendChild($lineExtensionAmount);
                $currencyId = $dom->createAttribute('currencyID');
                $currencyId->value = $docInvoiceData->c_document_currency_code;
                $lineExtensionAmount->appendChild($currencyId);

                # DocInvoiceItemPricingReferenceAlternativeConditionPrice
                $docInvoiceItemPricingReferenceAlternativeConditionPriceData = $value
                    ->DocInvoiceItemPricingReferenceAlternativeConditionPrice;

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

                # DocInvoiceItemAllowancecharge
                $docInvoiceItemAllowancechargeData = $value->DocInvoiceItemAllowancecharge;
                if (isset($docInvoiceItemAllowancechargeData) && !empty($docInvoiceItemAllowancechargeData)) {
                    $allowanceCharge = $dom->createElement('cac:AllowanceCharge');
                    $chargeIndicator = $dom->createElement('cbc:ChargeIndicator',
                        $docInvoiceItemAllowancechargeData
                        ->c_charge_indicator);
                    $amount = $dom->createElement('cbc:Amount',
                        $docInvoiceItemAllowancechargeData
                        ->c_amount);
                    $currencyId = $dom->createAttribute('currencyID');
                    $currencyId->value = $docInvoiceData->c_document_currency_code;
                    $amount->appendChild($currencyId);
                    $invoiceLine->appendChild($allowanceCharge);
                    $allowanceCharge->appendChild($chargeIndicator);
                    $allowanceCharge->appendChild($amount);
                }

                $docInvoiceItemTaxTotalData = $value->DocInvoiceItemTaxTotal;

                foreach ($docInvoiceItemTaxTotalData as $k => $v) {
                    $taxTotal = $dom->createElement('cac:TaxTotal');
                    $invoiceLine->appendChild($taxTotal);
                    $taxItemAmount = $dom->createElement('cbc:TaxAmount', $v->c_tax_amount);
                    $taxTotal->appendChild($taxItemAmount);
                    $currencyItemId = $dom->createAttribute('currencyID');
                    $currencyItemId->nodeValue = $docInvoiceData->c_document_currency_code;
                    $taxItemAmount->appendChild($currencyItemId);


                    $taxSubtotal = $dom->createElement('cac:TaxSubtotal');
                    $taxTotal->appendChild($taxSubtotal);
                    $docInvoiceItemTaxTotalTaxSubtotal = $v->DocInvoiceItemTaxTotalTaxSubtotal;
                    $taxItemSubtotalAmount = $dom->createElement('cbc:TaxAmount',
                        $docInvoiceItemTaxTotalTaxSubtotal->c_tax_amount);
                    $taxSubtotal->appendChild($taxItemSubtotalAmount);
                    $currencyItemSubtotalId = $dom->createAttribute('currencyID');
                    $currencyItemSubtotalId->nodeValue = $docInvoiceData->c_document_currency_code;
                    $taxItemSubtotalAmount->appendChild($currencyItemSubtotalId);

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

                $item = $dom->createElement('cac:Item');
                $invoiceLine->appendChild($item);

                $docInvoiceItemDescription = $value->DocInvoiceItemDescription;
                if (!is_null($docInvoiceItemDescription)) {
                    foreach ($docInvoiceItemDescription as $k => $v) {
                        $description = $dom->createElement('cbc:Description');
                        $description->appendChild($dom->createCDATASection($v->c_description));
                        $item->appendChild($description);
                    }
                }

                if (isset($v->c_item_sellers_item_identification_id) &&
                    !empty($v->c_item_sellers_item_identification_id)) {
                    $sellersItemIdentification = $dom->createElement('cac:SellersItemIdentification');
                    $item->appendChild($sellersItemIdentification);
                    $id = $dom->createElement('cbc:ID');
                    $id->nodeValue = $value->c_item_sellers_item_identification_id;
                    $sellersItemIdentification->appendChild($id);
                }

                $price = $dom->createElement('cac:Price');
                $invoiceLine->appendChild($price);
                $priceItemAmount = $dom->createElement('cbc:PriceAmount');
                $priceItemAmount->nodeValue = $value->c_price_price_amount;
                $currencyId = $dom->createAttribute('currencyID');
                $currencyId->nodeValue = $docInvoiceData->c_document_currency_code;
                $priceItemAmount->appendChild($currencyId);
                $price->appendChild($priceItemAmount);
            }

            $xmlName = sprintf('%s-%s-%s', $docInvoiceSupplier->c_customer_assigned_account_id, '03',
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
                    $docInvoiceSignature->n_id_invoice = $billId;
                    $docInvoiceSignature->c_signature_value = preg_replace('/\s+/', '', $ds['SignatureValue']);
                    $docInvoiceSignature->c_digest_value = $ds['DigestValue'];
                    $docInvoiceSignature->save();
                }
            }

            $zipPath = $docInvoiceData->SupSupplier->SupSupplierConfiguration->c_public_path_document . DIRECTORY_SEPARATOR . $xmlName . '.ZIP';
            $zipFullPath = public_path($zipPath);

            # DocInvoiceFile
            $docInvoiceFile = new DocInvoiceFile();
            $docInvoiceFile->n_id_invoice = $billId;
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

            \Zipper::make($zipFullPath)->add($xmlFullPath)->close();
            unlink($xmlFullPath);

            $response['status'] = 1;
            $response['path'] = $zipFullPath;
            chmod($zipFullPath, 0777);

            Log::info('Generación de XML',
                [
                'lgph_id' => 4, 'n_id_invoice' => $billId, 'c_invoice_type_code' => '03'
                ]
            );
        } catch (Exception $exc) {
            Log::error($exc->getMessage(),
                [
                'lgph_id' => 4, 'n_id_invoice' => $billId, 'c_invoice_type_code' => '03'
                ]
            );
            $response['message'] = $exc->getMessage();
        }
        return $response;
    }

}
