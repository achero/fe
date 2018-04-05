<?php
namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Log;
use Exception;

use App\DocInvoiceFile;
use App\DocInvoice;

use App\Http\Controllers\Pdf\A4\App_PDF_A4_Arie;
use App\Http\Controllers\Pdf\A4\App_PDF_A4_Arie_HF;
use App\Http\Controllers\Pdf\A4\App_PDF_A4_Default;
use App\Http\Controllers\Pdf\A4\App_PDF_A4_Default_HF;
use App\Http\Controllers\Pdf\Ticket\App_PDF_Ticket_Arie;

trait UtilHelper
{

    /**
     * Te genera un PDF del ID solicitado, te devuelve la ruta absoluta del archivo.
     * @param int $id
     * @return array 
     * @throws Exception
     */
    public static function pdf($id)
    {

        $response['status'] = 0;
        $response['message'] = '';
        $response['file_name'] = '';
        $response['file_path'] = '';

        //try {
            # Variables globales
            $data['op_exoneradas'] = null;
            $data['op_gratuitas'] = null;
            $data['op_inafectas'] = null;
            $data['op_gravadas'] = null;
            $data['total_descuentos'] = null;
            $data['isc'] = null;
            $data['igv'] = null;
            $data['tipo_documento'] = null;
            $data['leyenda'] = null;
            $data['doc_invoice_related'] = null;
            # Sobre-escritura
            $invoice = DocInvoice::with('SupSupplier.SupSupplierConfiguration',
                    'DocInvoiceAdditionalInformationAdditionalProperty', 'DocInvoiceSupplier', 'DocInvoiceCustomer',
                    'DocInvoicePdfData.DocInvoicePdfDataCustom', 'DocDocumentCurrencyCodeType',
                    'DocInvoiceLegalMonetaryTotal', 'DocInvoiceAdditionalInformationAdditionalMonetaryTotal',
                    'DocInvoiceTaxTotal.DocInvoiceTaxTotalTaxSubtotalTaxCategoryTaxScheme', 'DocInvoiceFile',
                    'DocInvoiceSignature'
                )->find($id);
            if (is_null($invoice)) {
                throw new Exception("El documento no existe.");
            }
            if (is_null($invoice->c_status_invoice != "visible")) {
                throw new Exception("El Documento no está activo (Hay otro que lo reemplaza).");
            }
            $data['emisor_resolucion'] = '';
            switch ($invoice->c_invoice_type_code) {
                case '01':
                    $data['emisor_resolucion'] = $invoice->DocInvoiceSupplier->c_sunat_invoice_resolution;
                    break;
                case '03':
                    $data['emisor_resolucion'] = $invoice->DocInvoiceSupplier->c_sunat_bill_resolution;
                    break;
                case '07':
                case '08':
                    $invoiceChild = DocInvoice::with('DocInvoiceSupplier')->where('n_id_invoice',
                            $invoice->n_id_invoice_related)->first();
                    $data['doc_invoice_related'] = $invoiceChild->c_id;
                    switch ($invoiceChild->c_invoice_type_code) {
                        case '01':
                            $data['emisor_resolucion'] = $invoice->DocInvoiceSupplier->c_sunat_invoice_resolution;
                            break;
                        case '03':
                            $data['emisor_resolucion'] = $invoice->DocInvoiceSupplier->c_sunat_bill_resolution;
                            break;
                    }
                    break;
            }
            $cIdExplode = explode('-', $invoice->c_id);
            $data['emisor_direccion_calle'] = $invoice->DocInvoiceSupplier->c_party_postal_address_street_name;
            $data['c_party_postal_address_street_name'] = $invoice->DocInvoiceSupplier->c_party_postal_address_street_name;
            $data['c_party_postal_address_city_subdivision_name'] = $invoice->DocInvoiceSupplier->c_party_postal_address_city_subdivision_name;
            $data['c_party_postal_address_district'] = $invoice->DocInvoiceSupplier->c_party_postal_address_district;
            $data['c_telephone'] = $invoice->DocInvoiceSupplier->c_telephone;
            $data['emisor_direccion_general'] = sprintf('%s %s %s',
                $invoice->DocInvoiceSupplier->c_party_postal_address_district,
                $invoice->DocInvoiceSupplier->c_party_postal_address_country_subentity,
                $invoice->DocInvoiceSupplier->Country->c_name_short);
            $data['emisor_telefono'] = $invoice->DocInvoiceSupplier->c_telephone;
            $data['emisor_ruc'] = $invoice->DocInvoiceSupplier->c_customer_assigned_account_id;
            $data['emisor_detraccion'] = $invoice->DocInvoiceSupplier->c_detraction_account;
            $data['serie'] = reset($cIdExplode);
            $data['correlativo'] = end($cIdExplode);
            $data['cliente_razon_social'] = $invoice->DocInvoiceCustomer->c_party_party_legal_entity_registration_name;
            $data['cliente_direccion'] = $invoice->DocInvoiceCustomer->c_party_physical_location_description;

            $data['c_additional_account_id'] = $invoice->DocInvoiceCustomer->c_additional_account_id;
            $data['c_customer_assigned_account_id'] = $invoice->DocInvoiceCustomer->c_customer_assigned_account_id;

            $data['tipo_moneda'] = $invoice->DocDocumentCurrencyCodeType->c_description;
            $data['fecha_emision'] = date('d/m/Y', strtotime($invoice->d_issue_date));

            if (!empty($invoice->DocInvoicePdfData->d_expiration_date)) {
                $data['fecha_vencimiento'] = date('d/m/Y', strtotime($invoice->DocInvoicePdfData->d_expiration_date));
            }

            $data['cliente_codigo'] = $invoice->DocInvoiceCustomer->c_customer_assigned_account_id;
            $data['compra_orden'] = $invoice->DocInvoicePdfData->c_purchase_order;

            switch ($invoice->DocInvoicePdfData->n_terms_of_payment) {
                case '0':
                case 0:
                    $data['pago_condiciones'] = 'CONTADO';
                    break;
                default:
                    $data['pago_condiciones'] = sprintf('A %s Días', $invoice->DocInvoicePdfData->n_terms_of_payment);
                    break;
            }

            # Valores personalizados en LOLIMSA
            $docInvoicePdfDataCustom = $invoice->DocInvoicePdfData->DocInvoicePdfDataCustom;
            if ($docInvoicePdfDataCustom->count() > 0) {
                foreach ($docInvoicePdfDataCustom as $value) {
                    $data[(string) $value->c_name] = (string) $value->c_value;
                }
            }

            $data['items'] = $invoice->DocInvoiceItem;
            $data['total'] = $invoice->DocInvoiceLegalMonetaryTotal->c_payable_amount;
            $data['observacion'] = $invoice->DocInvoicePdfData->c_observation;
            $data['zona'] = null;
            $data['pedido_numero'] = null;
            $data['guia_numero'] = null;
            $data['monto_en_letras'] = null;

            foreach ($invoice->DocInvoiceAdditionalInformationAdditionalProperty as $value) {
                switch ($value->c_id) {
                    case '1000':
                        $data['monto_en_letras'] = $value->c_value;
                        break;
                }
            }

            $data['op_detraccion'] = null;

            foreach ($invoice->DocInvoiceAdditionalInformationAdditionalMonetaryTotal as $value) {
                switch ($value->c_id) {
                    case '1003':
                        $data['op_exoneradas'] = $value->c_payable_amount;
                        break;
                    case '1002':
                        $data['op_inafectas'] = $value->c_payable_amount;
                        break;
                    case '1001':
                        $data['op_gravadas'] = $value->c_payable_amount;
                        break;
                    # detraccion
                    case '2003':
                        $data['op_detraccion'] = $value->c_payable_amount;
                        break;
                    case '2005':
                        $data['total_descuentos'] = $value->c_payable_amount;
                        break;
                }
            }

            foreach ($invoice->DocInvoiceTaxTotal as $value) {
                switch ($value->DocInvoiceTaxTotalTaxSubtotalTaxCategoryTaxScheme->c_id) {
                    case '1000':
                        $data['igv'] = $value->c_tax_amount;
                        break;
                    case '2000':
                        $data['isc'] = $value->c_tax_amount;
                        break;
                    case '9999':
                        break;
                }
            }

            switch ($invoice->c_invoice_type_code) {
                case '01':
                    $data['tipo_documento'] = 'FACTURA ELECTRÓNICA';
                    break;
                case '03':
                    $data['tipo_documento'] = 'BOLETA DE VENTA';
                    break;
                case '07':
                    $data['tipo_documento'] = 'NOTA DE CRÉDITO';
                    break;
                case '08':
                    $data['tipo_documento'] = 'NOTA DE DÉBITO';
                    break;
            }

            # Leyenda
            if ($data['total'] == $data['op_exoneradas']) {
                $data['leyenda'] = 'OPERACIÓN EXONERADA.';
            }

            if ($data['total'] == $data['op_inafectas']) {
                $data['leyenda'] = 'OPERACIÓN INAFECTA.';
            }
            if ($data['total'] == $data['op_gratuitas']) {
                switch ($invoice->DocInvoiceItem->count()) {
                    case 1:
                        $data['leyenda'] = 'TRANSFERENCIA GRATUITA.';
                        break;
                    default:
                        $data['leyenda'] = 'SERVICIO PRESTADO GRATUITAMENTE.';
                        break;
                }
            }

            $data['codigoBarra'] = sprintf('%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s', $data['emisor_ruc'],
                $invoice->c_invoice_type_code, $invoice->c_serie, $invoice->c_correlative, $data['igv'], $data['total'],
                $data['fecha_emision'], $invoice->DocInvoiceCustomer->c_additional_account_id,
                $invoice->DocInvoiceCustomer->c_customer_assigned_account_id,
                isset($invoice->DocInvoiceSignature) ? $invoice->DocInvoiceSignature->c_digest_value : '0',
                isset($invoice->DocInvoiceSignature) ? $invoice->DocInvoiceSignature->c_signature_value : '0');
            $docInvoiceFile = $invoice->DocInvoiceFile;
            $nameExplode = explode('.', $docInvoiceFile->c_document_name);
            $name = reset($nameExplode) . '.PDF';

            $data['path'] = public_path($invoice->SupSupplier->SupSupplierConfiguration->c_public_path_pdf . DIRECTORY_SEPARATOR . $name);
            file_exists($data['path']) ? unlink($data['path']) : '';

            #$pdf = new App_PDF_Ticket_Arie();
            switch (env('TICKET')) {
                case true:
                    switch ($invoice->c_invoice_type_code) {
                        case '03':
                            $pdf = new App_PDF_Ticket_Arie();
                            break;
                        default:
                            switch ($_ENV['PDF.FORMATO']) {
                                case 'arie':
                                    $pdf = new App_PDF_A4_Arie();
                                    break;
                                case 'default':
                                default :
                                    $pdf = new App_PDF_A4_Default();
                                    break;
                            }
                            break;
                    }
                    break;
                case false:
                    switch (env('PDF_FORMATO')) {
                        case 'arie':
                            $pdf = new App_PDF_A4_Arie();
                            break;
                        case 'default':
                        default :
                            $pdf = new App_PDF_A4_Default();
                            break;
                    }
                    break;
            }

            $pdf->generar($data);

            $docInvoiceFile->c_has_pdf = 'yes';
            $docInvoiceFile->c_pdf_name = $name;
            $docInvoiceFile->save();

            $response['status'] = 1;
            $response['file_name'] = $name;
            # Necesario para el API
            $response['file_path'] = public_path($invoice->SupSupplier->SupSupplierConfiguration->c_public_path_pdf . DIRECTORY_SEPARATOR . $name);

            Log::info('Generar PDF',
                [
                'lgph_id' => 8, 'n_id_invoice' => $id, 'c_invoice_type_code' => $invoice->c_invoice_type_code,
                ]
            );
        /*} catch (Exception $exc) {
            $response['message'] = $exc->getMessage();

            Log::error($response['message'],
                [
                'lgph_id' => 8, 'n_id_invoice' => $id, 'c_invoice_type_code' => $invoice->c_invoice_type_code,
                ]
            );
        }*/
        return $response;
    }

    # Envio de CDR (TXT) al FTP en base al ID.
    # herramienta de uso interno

    public static function sendReport($id)
    {
        $output['status'] = true;
        try {
            # Averiguar FTP
            $docInvoice = DocInvoice::find($id);
            if (
                ($docInvoice->DocInvoiceFile->c_has_sunat_response == 'yes') &&
                ($docInvoice->DocInvoiceFile->c_is_cdr_processed_dispatched == 'no')
            ) {

                if ($docInvoice->c_status_invoice != "visible") {
                    throw new Exception("El documento no está activo.");
                }

                $docInvoiceFtp = $docInvoice->SupSupplier->SupSupplierFtp;
                $username = $docInvoiceFtp->c_username;
                $password = $docInvoiceFtp->c_password;
                $host = $docInvoiceFtp->c_host;
                $port = $docInvoiceFtp->n_port;
                $passive = $docInvoiceFtp->c_is_passive;
                $cnxName = 'cnx' . $id;

                # Conectividad
                Config::set('ftp::connections.' . $cnxName,
                    array(
                    'host' => $host,
                    'port' => $port,
                    'username' => $username,
                    'password' => Crypt::decrypt($password),
                    'passive' => ($passive == 'yes') ? true : false,
                ));
                $cnx = FTP::connection($cnxName);

                $fileName = substr($docInvoice->DocInvoiceInput->c_input_name, 8);


                $cdrResponseDate = $docInvoice->DocInvoiceCdr->d_response_date;
                $cdrResponseDate = (string) date('dmY', strtotime($cdrResponseDate));
                $fileName = $cdrResponseDate . $fileName;
                $pathTemp = storage_path('sunat/temporal/' . $fileName);

                $file = fopen($pathTemp, 'w');
                $status = null;
                $response = null;
                $invoiceTypeCode = $docInvoice->c_invoice_type_code;
                $serie = (in_array($docInvoice->c_invoice_type_code, array('RA', 'RC'))) ? '' : $docInvoice->c_serie;
                $correlativo = $docInvoice->c_correlative;
                $ruc = $docInvoice->SupSupplier->c_customer_assigned_account_id;
                $appendTxt = '';
                switch ($docInvoice->DocInvoiceCdr->DocInvoiceCdrDocumentResponse->c_response_response_code) {
                    case '0':
                        # Aprobado
                        $status = 1;
                        $response = $docInvoice->DocInvoiceCdr->DocInvoiceCdrDocumentResponse->c_response_description;
                        if ($docInvoice->DocInvoiceCdr->DocInvoiceCdrNote->count()) {
                            # Observado
                            $status = 2;
                            foreach ($docInvoice->DocInvoiceCdr->DocInvoiceCdrNote as $key => $value) {
                                $appendTxt .= "\n";
                                $appendTxt .= sprintf("%s|%s|%s|%s|%s|%s|", $ruc, $invoiceTypeCode, $serie,
                                    $correlativo, $value->c_code, $value->c_description);
                            }
                        }
                        $txt = sprintf('%s|%s|%s|%s|%s|%s|', $ruc, $invoiceTypeCode, $serie, $correlativo, $status,
                            $response);
                        $txt = $txt . $appendTxt;
                        break;
                    default:
                        # Rechazado
                        $status = 3;
                        $response = $docInvoice->DocInvoiceCdr->DocInvoiceCdrDocumentResponse->c_response_description;
                        if ($docInvoice->DocInvoiceCdr->DocInvoiceCdrNote->count()) {
                            foreach ($docInvoice->DocInvoiceCdr->DocInvoiceCdrNote as $key => $value) {
                                $appendTxt .= "\n";
                                $appendTxt .= sprintf("%s|%s|%s|%s|%s|%s|", $ruc, $invoiceTypeCode, $serie,
                                    $correlativo, $value->c_code, $value->c_description);
                            }
                        }
                        $txt = sprintf('%s|%s|%s|%s|%s|%s|', $ruc, $invoiceTypeCode, $serie, $correlativo, $status,
                            $response);
                        $txt = $txt . $appendTxt;
                        break;
                }
                fwrite($file, $txt);
                fclose($file);
                $send['path'] = $pathTemp;
                $send['name'] = $fileName;
                $path = $_ENV['CRONJOB.RESPONSE'];
                $upload = $cnx->uploadFile($send['path'], $path . '/' . $fileName);
                $docInvoice->DocInvoiceFile->c_is_cdr_processed_dispatched = 'yes';
                $docInvoice->DocInvoiceFile->save();
                if ($upload) {
                    unlink($send['path']);
                } else {
                    throw new Exception("No se pudo subir el archivo al Servidor FTP del Emisor.");
                }
                FTP::disconnect($cnxName);
                $output['message'] = 'El CDR procesado fue enviado correctamente al Servidor FTP del Emisor.';

                $output['message'] = 'No hay CDR procesado por enviar al Servidor FTP del Emisor.';
            } else {
                throw new Exception('El documento no cuenta con lo mínimo para enviar CDR procesado al Servidor FTP del Emisor.');
            }
        } catch (Exception $e) {
            $output['status'] = false;
            $output['message'] = $e->getMessage();
        }
        return $output;
    }

    /**
     * Invocador de servicio a utilizar SUNAT
     * @param string $sunatServer
     * @return SoapClient
     */
    public static function newSunatRequest($sunatServer)
    {
        $response['status'] = 0;
        $response['message'] = '';
        $response['client'] = '';
        try {
            $user = env('SUNAT_USER');
            $password = env('SUNAT_PASSWORD');
            $wsdl = null;
            switch ($sunatServer) {
                case 'beta':
                    # Beta nuevo
                    $wsdl = 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService?wsdl';
                    # Beta antiguo
                    #$wsdl = 'https://www.sunat.gob.pe/ol-ti-itcpgem-beta/billService?wsdl';
                    break;
                case 'homologacion':
                    $wsdl = 'https://www.sunat.gob.pe/ol-ti-itcpgem-sqa/billService?wsdl';
                    break;
                case 'production':
                    # $wsdl = 'https://www.sunat.gob.pe/ol-ti-itcpfegem/billService?wsdl';
                    $wsdl = public_path('static/webservice/production.xml');
                    break;
                case 'cdr':
                    $wsdl = 'https://www.sunat.gob.pe/ol-it-wsconscpegem/billConsultService?wsdl';
                    break;
            }
            $auth = sprintf('<wsse:Security mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-' .
                '200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401' .
                '-wss-wssecurity-utility-1.0.xsd"><wsse:UsernameToken wsu:Id="UsernameToken-EBDA6BCF18BE1AEBD51424373' .
                '01228913"><wsse:Username>%s</wsse:Username><wsse:Password Type="http://docs.oasis-open.org/wss/2004/' .
                '01/oasis-200401-wss-username-token-profile-1.0#PasswordText">%s</wsse:Password></wsse:UsernameToken>' .
                '</wsse:Security>', $user, $password);

            $authValues = new \SoapVar($auth, XSD_ANYXML);
            $options = [
                'uri' => 'http://schemas.xmlsoap.org/soap/envelope/',
                'style' => SOAP_RPC,
                'use' => SOAP_ENCODED,
                'soap_version' => SOAP_1_1,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'connection_timeout' => 0,
                'trace' => true,
                'encoding' => 'UTF-8',
                'exceptions' => false,
            ];
            $client = new \SoapClient($wsdl, $options);
            $header = new \SoapHeader('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd',
                'Security', $authValues, false);
            $client->__setSoapHeaders($header);
            $response['status'] = 1;
            $response['client'] = $client;
        } catch (Exception $exc) {
            $response['message'] = $exc->getMessage();
        }

        $sunatRequest = $response;
        $client = $sunatRequest['client'];
        $params['ticket'] = '1519462691945';
        $getStatus = $client->__soapCall('getStatus', array($params));


        //dd($getStatus);
        return $response;
    }

    /**
     * Guarda datos del input
     * @param int $id
     * @param string $type
     * @param File $file
     * @return array
     * @throws Exception
     */
    public static function saveInvoiceInput($id, $type, $file = null)
    {
        $response['status'] = 0;
        $response['message'] = '';

        $docInvoiceFile = DocInvoiceFile::with('DocInvoice.SupSupplier.SupSupplierConfiguration')
                ->where('n_id_invoice', $id)->first();
        $invoiceTypeCode = $docInvoiceFile->DocInvoice->c_invoice_type_code;
        try {
            switch ($type) {
                case 'WEBSITE':
                    $inputName = $file->getClientOriginalName();
                    $content = file_get_contents($file);
                    $hash = md5($content);
                    break;
                case 'WEBSERVICE':
                    $inputName = $file['file_name'];
                    $content = $file['file_content'];
                    $hash = $file['hash'];
            }

            $path = public_path(
                $docInvoiceFile->DocInvoice->SupSupplier->SupSupplierConfiguration->c_public_path_input .
                DIRECTORY_SEPARATOR . $inputName
            );
            \File::put($path, $content);
            chmod($path, 0777);

            $docInvoiceFile->c_input_name = $inputName;
            $docInvoiceFile->c_input_hash = $hash;

            $fileNameExplode = explode('-', $inputName);
            $fileDate = reset($fileNameExplode);
            $day = substr($fileDate, 0, 2);
            $month = substr($fileDate, 2, 2);
            $year = substr($fileDate, 4, 4);
            $docInvoiceFile->d_date_input_created = date('Y-m-d', strtotime(sprintf('%s-%s-%s', $day, $month, $year)));
            $docInvoiceFile->save();

            $response['status'] = 1;

            Log::info('Almacenamiento Input BD/File',
                [
                'lgph_id' => 7, 'n_id_invoice' => $id, 'c_invoice_type_code' => $invoiceTypeCode,
                ]
            );
        } catch (Exception $exc) {
            Log::error($exc->getMessage(),
                [
                'lgph_id' => 7, 'n_id_invoice' => $id, 'c_invoice_type_code' => $invoiceTypeCode,
                ]
            );
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    /**
     * Lectura del CDR y su posterior conversion a un array.
     * @param string $path
     * @return array
     * @throws Exception
     */
    public static function cdr($path)
    {
        if (!file_exists($path)) {
            throw new Exception('No existe el archivo XML del CDR.');
        }

        $cdr = [];

        $xml = simplexml_load_file($path, null, LIBXML_NOCDATA);
        $namespaces = $xml->getNamespaces(true);
        $dataCac = $xml->children($namespaces['cac']);
        $dataCbc = $xml->children($namespaces['cbc']);


        foreach ($dataCbc as $key => $value) {
            switch ($key) {
                case 'UBLVersionID':
                case 'CustomizationID':
                case 'ID':
                case 'IssueDate':
                case 'IssueTime':
                case 'ResponseDate':
                case 'ResponseTime':
                    $cdr[$key] = (string) $value;
                    break;
                case 'Note':
                    $cdr[$key][] = (string) $value;
                    break;
            }
        }
        foreach ($dataCac as $key => $value) {
            switch ($key) {
                case 'SenderParty':
                case 'ReceiverParty':
                case 'DocumentResponse':
                    foreach ($value->children($namespaces['cac']) as $ke => $va) {
                        switch ($ke) {
                            case 'PartyIdentification':
                            case 'Response':
                            case 'DocumentReference':
                                foreach ($va->children($namespaces['cbc']) as $k => $v) {
                                    switch ($k) {
                                        case 'ID':
                                        case 'ReferenceID':
                                        case 'ResponseCode':
                                        case 'Description':
                                            $cdr[$key][$ke][$k] = (string) $v;
                                            break;
                                    }
                                }
                                break;
                            case 'RecipientParty':
                                foreach ($va->children($namespaces['cac']) as $k => $v) {
                                    switch ($k) {
                                        case 'PartyIdentification':
                                            foreach ($v->children($namespaces['cbc']) as $l => $b) {
                                                $cdr[$key][$ke][$k][$l] = (string) $b;
                                            }
                                            break;
                                    }
                                }
                                break;
                        }
                    }
                    break;
            }
        }

        return $cdr;
    }

    /**
     * Verifica el documento y si puede ser procesable en estos casos:
     * Existencia en BD
     * Respuesta SUNAT
     * Evaluacion de CDR
     * Evaluacion de contenido
     * @param string $fileName
     * @param string $type
     * @param file $file
     * @return array
     * @throws Exception
     */
    public static function verifyDocument($fileName, $type = 'WEBSITE', $file = null)
    {
        $response['status'] = 0;
        $response['message'] = '';

        $docInvoiceFile = DocInvoiceFile::with('DocInvoice.DocInvoiceCdr.DocInvoiceCdrDocumentResponse.ErrErrorCode')
                ->whereHas('DocInvoice',
                    function($query) {
                    $query->where('c_status_invoice', 'visible');
                })->where('c_input_name', $fileName);

        try {
            # No existe en BD
            if ($docInvoiceFile->count() == 0) {
                $response['status'] = 1;
                $response['message'] = $fileName . ' no existe en la BD';

                Log::info($response['message'], [
                    'lgph_id' => 12,
                    ]
                );
                return $response;
            }
            # Consistencia en BD
            if ($docInvoiceFile->count() > 1) {
                throw new Exception($fileName . ' error de consistencia en BD, no puede haber más de un documento activo');
            }

            $invoiceFile = $docInvoiceFile->first();

            # Verifica si no tiene respuesta de SUNAT
            if ($invoiceFile->c_has_sunat_response == 'no') {
                $response['status'] = 1;
                $response['message'] = $fileName . ' el documento no presenta respuesta al WS de la SUNAT';

                Log::info($response['message'], [
                    'lgph_id' => 12,
                    ]
                );
                return $response;
            }

            # Verificar si paso correctamente
            if (!is_null($invoiceFile->DocInvoice->DocInvoiceCdr)) {
                $errErrorCode = $invoiceFile->DocInvoice->DocInvoiceCdr->DocInvoiceCdrDocumentResponse->ErrErrorCode;
                $cIdErrorCode = $errErrorCode->c_id_error_code;
                switch ($cIdErrorCode) {
                    case '0':
                        throw new Exception($fileName . ' existe en la BD y presenta un CDR aprobado');
                    default:
                        switch ($errErrorCode->c_is_forwardable) {
                            case 0:
                                throw new Exception($fileName . ' existe en la BD y presenta un CDR con error no re-enviable');
                            case 1:
                                # Verifico el HASH, si es diferente permite reenviar
                                switch ($type) {
                                    case 'WEBSITE':
                                        $content = file_get_contents($file);
                                        $hash = md5($content);
                                        break;
                                    case 'WEBSERVICE':
                                        $hash = $file['hash'];
                                }
                                if ($invoiceFile->c_input_hash == $hash) {
                                    throw new Exception($fileName . ' presenta un CDR con error re-enviable, pero no'
                                    . ' se puede procesar ya que presenta el mismo contenido');
                                }
                                break;
                        }
                        break;
                }
            }

            $response['status'] = 1;

            Log::info($fileName . ' verificar documento', [
                'lgph_id' => 12,
                ]
            );
        } catch (Exception $exc) {
            $response['message'] = $exc->getMessage();

            Log::error($response['message'], ['lgph_id' => 12]);
        }
        return $response;
    }

    public static function readSignatureXml($path)
    {
        $ds = array();
        if (file_exists($path)) {
            $xml = simplexml_load_file($path, null, LIBXML_NOCDATA);
            $namespaces = $xml->getNamespaces(true);
            $dataExt = $xml->children($namespaces['ext']);
            $ds = array();
            foreach ($dataExt as $key => $value) {
                switch ($key) {
                    case 'UBLExtensions':
                        $i = 0;
                        foreach ($value->children($namespaces['ext']) as $ke => $va) {
                            switch ($i) {
                                case 1:
                                    foreach ($va->children($namespaces['ext']) as $k => $v) {
                                        switch ($k) {
                                            case 'ExtensionContent':
                                                foreach ($v->children($namespaces['ds']) as $ky => $vl) {
                                                    switch ($ky) {
                                                        case 'Signature':
                                                            foreach ($vl->children($namespaces['ds']) as $keyk => $valv) {
                                                                switch ($keyk) {
                                                                    case 'SignedInfo':
                                                                        foreach ($valv->children($namespaces['ds']) as $keyke => $valva) {
                                                                            switch ($keyke) {
                                                                                case 'Reference':
                                                                                    foreach ($valva->children($namespaces['ds']) as $keykey => $valval) {
                                                                                        switch ($keykey) {
                                                                                            case 'DigestValue':
                                                                                                $ds[$keykey] = (string) $valval;
                                                                                                break;
                                                                                        }
                                                                                    }
                                                                                    break;
                                                                            }
                                                                        }
                                                                        break;
                                                                    case 'SignatureValue':
                                                                        $ds[$keyk] = (string) $valv;
                                                                        break;
                                                                }
                                                            }
                                                            break;
                                                    }
                                                }
                                                break;
                                        }
                                    }
                                    break;
                            }
                            $i++;
                        }
                        break;
                }
            }
        }
        return $ds;
    }

}
