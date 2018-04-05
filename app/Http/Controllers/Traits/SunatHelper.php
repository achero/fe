<?php
namespace App\Http\Controllers\Traits;

use XmlIterator\XmlIterator;
use App\DocInvoiceFile;
use App\ErrErrorCode;
use Exception;
use Illuminate\Support\Facades\Log;

use App\DocInvoiceCdr;
use App\DocInvoiceCdrSenderParty;
use App\DocInvoiceCdrReceiverParty;
use App\DocInvoiceCdrDocumentResponse;
use App\DocInvoiceCdrNote;
use App\DocInvoiceCdrStatus;
use App\DocInvoiceTicket;

/**
 * Helper que permite el procesamiento relacionado a la SUNAT y manejo de respuestas, implica almacenamiento en DB.
 */
trait SunatHelper
{

    public static function ticket($cTicket)
    {
        $response['status'] = 0;
        $response['message'] = '';
        $response['path'] = '';

        try {
            $sunatRequest = UtilHelper::newSunatRequest(env('SUNAT_SERVER'));
            die('bb');
            if (!$sunatRequest['status']) {
                throw new Exception($sunatRequest['message']);
            }
            $client = $sunatRequest['client'];
            $params['ticket'] = $cTicket;
            $getStatus = $client->__soapCall('getStatus', array($params));

            switch (get_class($getStatus)) {
                case 'stdClass':
                    $docInvoiceTicket = DocInvoiceTicket::with('DocInvoice.DocInvoiceFile',
                            'DocInvoice.SupSupplier.SupSupplierConfiguration')->where('c_ticket', $cTicket);
                    if ($docInvoiceTicket->count() == 0) {
                        throw new Exception(sprintf('No se encuentra en la Base de Datos el ticket %s.', $cTicket));
                    }
                    $docInvoiceTicket = $docInvoiceTicket->first();
                    $docInvoiceTicket->c_ticket_code = $getStatus->status->statusCode;
                    $docInvoiceTicket->save();
                    $response['code'] = $getStatus->status->statusCode;
                    switch ($response['code']) {
                        case '99':
                        case '0':
                            $response['status'] = 1;

                            $docInvoiceFile = $docInvoiceTicket->DocInvoice->DocInvoiceFile;
                            $file = $getStatus->status->content;

                            $nameCdr = 'R-' . $docInvoiceFile->c_document_name;
                            $path = $docInvoiceFile->DocInvoice->SupSupplier->SupSupplierConfiguration->c_public_path_cdr;
                            $pathFile = $path . DIRECTORY_SEPARATOR . $nameCdr;

                            $response['status'] = 1;
                            $response['path'] = $responsePath = public_path($pathFile);

                            file_exists($responsePath) ? unlink($responsePath) : '';
                            \File::put($responsePath, $file);
                            chmod($responsePath, 0777);

                            $docInvoiceFile->c_has_cdr = 'yes';
                            $docInvoiceFile->c_cdr_name = $nameCdr;
                            $docInvoiceFile->d_date_cdr_created = date('Y-m-d H:i:s');
                            $docInvoiceFile->save();
                            break;
                        case '98':
                            $errErrorCode = ErrErrorCode::find($response['code']);
                            throw new Exception($errErrorCode->c_description);
                    }
                    break;
                case 'SoapFault':
                    $error = $getStatus->faultcode;
                    $errorExplode = explode('.', $error);
                    $errorCode = end($errorExplode);
                    $errErrorCode = ErrErrorCode::find($errorCode);
                    throw new Exception($errErrorCode->c_description);
            }

            Log::info('Lectura Ticket SUNAT',
                [
                'lgph_id' => 10, 'c_ticket' => $cTicket,
                ]
            );
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();

            Log::error($response['message'],
                [
                'lgph_id' => 10, 'c_ticket' => $cTicket,
                ]
            );
        }
        return $response;
    }

    public static function getStatusCdr($params, $name)
    {
        $response['status'] = true;
        try {
            $client = UtilHelper::newSunatRequest('cdr');
            if (!$client['status']) {
                throw new Exception($client['message']);
            }
            $client = $client['message'];
            $params['rucComprobante'] = Auth::user()->SupSupplier->c_customer_assigned_account_id;
            $params['tipoComprobante'] = $params['c_invoice_type_code'];
            $params['serieComprobante'] = $params['c_serie'];
            $params['numeroComprobante'] = (int) $params['n_correlative'];
            unset($params['_token']);
            unset($params['c_customer_assigned_account_id']);
            unset($params['c_invoice_type_code']);
            unset($params['c_serie']);
            unset($params['n_correlative']);
            $getStatusCdr = $client->__soapCall('getStatusCdr', array($params));
            switch (get_class($getStatusCdr)) {
                case 'stdClass':
                    $file = $getStatusCdr->statusCdr->content;
                    $nameCdr = 'R-' . $name;
                    $pathCdr = 'sunat/cdr/' . $nameCdr;
                    $response['message'] = $responsePath = storage_path($pathCdr);
                    file_exists($responsePath) ? unlink($responsePath) : '';
                    File::put($responsePath, $file);
                    chmod($responsePath, 0777);
                    $cdrPathPublicExplode = explode('.', $nameCdr);
                    $cdrPathPublicName = reset($cdrPathPublicExplode) . '.XML';
                    $cdrPathPublic = $_ENV['DOC_PUBLIC.CDR.PATH_FULL'] . '/' . $cdrPathPublicName;
                    # descomprimir el cdr y mandarlo a un directorio público.
                    file_exists(public_path($cdrPathPublic)) ? unlink(public_path($cdrPathPublic)) : '';
                    Zipper::make($responsePath)
                        ->extractTo(public_path($_ENV['DOC_PUBLIC.CDR.PATH_FULL']));
                    chmod(public_path($cdrPathPublic), 0777);
                    $response['message_xml'] = public_path($cdrPathPublic);
                    break;
                case 'SoapFault':
                    $error = $getStatusCdr->faultcode;
                    $errorExplode = explode('.', $error);
                    $errorCode = end($errorExplode);
                    $errErrorCode = ErrErrorCode::find($errorCode);
                    $response['message'] = $errErrorCode->c_description;
                    $response['code'] = $errorCode;
                    break;
            }
        } catch (Exception $exc) {
            $response['status'] = false;
            $response['message'] = $exc->getMessage();
        }
        return $response;
    }

    /**
     * Consulta del ticket a la sunat, operación pura sin procesos ajenos a la SUNAT.
     * @param string $ticket
     * @return array
     */
    public static function getStatus($cTicket)
    {
        $response['status'] = true;
        $response['code'] = NULL;
        try {
            $client = UtilHelper::newSunatRequest(env(('SUNAT_SERVER')));
            if (!$client['status']) {
                throw new Exception($client['message']);
            }

            $client = $client['client'];
            $params['ticket'] = $cTicket;

            $getStatus = $client->__soapCall('getStatus', array($params));
            file_put_contents('cdn/test_send_ticket.txt', $client->__getLastRequest());

            switch (get_class($getStatus)) {
                case 'stdClass':
                    $docInvoiceTicket = DocInvoiceTicket::where('c_ticket', $cTicket);
                    if ($docInvoiceTicket->count() == 0) {
                        throw new Exception(sprintf('No se encuentra en la Base de Datos el ticket %s.', $cTicket));
                    }
                    $docInvoiceTicket = $docInvoiceTicket->first();
                    $response['code'] = $getStatus->status->statusCode;

                    switch ($response['code']) {
                        case '99':
                        case '0':
                            $response['status'] = true;
                            $docInvoiceFile = $docInvoiceTicket->DocInvoice->DocInvoiceFile;

                            if(env('SYSTEM')=='linux')
                                $nameExplode = explode('/', $docInvoiceFile->c_document_name);
                            elseif(env('SYSTEM')=='windows'){
                                $nameExplode = explode('\\', $docInvoiceFile->c_document_name);
                            }

                            $name = end($nameExplode);
                            $file = $getStatus->status->content;
                            $nameCdr = 'R-' . $name;
                            $pathCdr = 'cdn/cdr-processed/' . $nameCdr;
                            $response['message'] = $responsePath = public_path($pathCdr);
                            //dd($responsePath);
                            file_exists($responsePath) ? unlink($responsePath) : '';
                            \File::put($responsePath, $file);
                            chmod($responsePath, 0777);
                            break;
                        case '98':
                            $errErrorCode = ErrErrorCode::find($response['code']);
                            $response['message'] = $errErrorCode->c_description;
                            $response['code'] = $response['code'];
                            break;
                    }
                    break;
                case 'SoapFault':
                    $error = $getStatus->faultcode;
                    $errorExplode = explode('.', $error);
                    $errorCode = end($errorExplode);
                    $errErrorCode = ErrErrorCode::find($errorCode);
                    $response['message'] = $errErrorCode->c_description;
                    $response['code'] = $errorCode;
                    break;
            }
        } catch (Exception $exc) {
            $response['status'] = false;
            $response['message'] = $exc->getMessage();
        }

        return $response;
    }

    /**
     * Envío de documento a la SUNAT
     * @param string $path
     * @param int $id
     .
     * @return array
     * @throws Exception
     */
    public static function sent($path, $id)
    {
        $response['status'] = 0;
        $response['message'] = '';
        $response['path'] = '';
        $response['ticket'] = '';

        if(env('SYSTEM')=='linux')
            $nameExplode = explode('/', $path);
        elseif(env('SYSTEM')=='windows'){
            $nameExplode = explode('\\', $path);
            //TODO fix var $name
        }

        $name = end($nameExplode);
        $nameExplode = explode('-', $name);
        //dd($name,$nameExplode);
        $docInvoiceFile = DocInvoiceFile::with('DocInvoice.SupSupplier.SupSupplierConfiguration')->find($id);

        try {

            if (
                ($docInvoiceFile->DocInvoice->c_invoice_type_code == '03') &&
                ($docInvoiceFile->DocInvoice->SupSupplier->SupSupplierConfiguration->c_bill_sent_sunat == 'no')
            ) {
                throw new Exception('Por configuración de sistema, esta boleta no se envió individualmente');
            }

            $sunatRequest = UtilHelper::newSunatRequest(env('SUNAT_SERVER'));
            if (!$sunatRequest['status']) {
                throw new Exception($sunatRequest['message']);
            }
            $client = $sunatRequest['client'];
            //dd($name);
            $content = file_get_contents($path);
            $params['contentFile'] = $content;
            $params['fileName'] = $name;

            $docInvoiceFile->c_is_sent = 'yes';
            $docInvoiceFile->save();
            switch ($nameExplode[1]) {
                case '01':
                case '03':
                case '07':
                case '08':
                    $sendBill = $client->__soapCall('sendBill', array($params));
                    file_put_contents('cdn/test.txt', $client->__getLastRequest());

                    $docInvoiceFile->c_has_sunat_response = 'yes';
                    $docInvoiceFile->save();
                    switch (get_class($sendBill)) {
                        case 'stdClass':
                            $file = $sendBill->applicationResponse;
                            $nameCdr = 'R-' . $name;
                            $path = $docInvoiceFile->DocInvoice->SupSupplier->SupSupplierConfiguration->c_public_path_cdr;
                            $pathFile = $path . DIRECTORY_SEPARATOR . $nameCdr;

                            $response['status'] = 1;
                            $response['path'] = $responsePath = public_path($pathFile);

                            file_exists($responsePath) ? unlink($responsePath) : '';
                            \File::put($responsePath, $file);
                            chmod($responsePath, 0777);

                            $docInvoiceFile->c_has_cdr = 'yes';
                            $docInvoiceFile->c_cdr_name = $nameCdr;
                            $docInvoiceFile->d_date_cdr_created = date('Y-m-d H:i:s');
                            $docInvoiceFile->save();
                            break;
                        case 'SoapFault':
                            $error = $sendBill->faultcode;
                            $errorExplode = explode('.', $error);
                            $errorCode = end($errorExplode);
                            $errErrorCode = ErrErrorCode::find($errorCode);
                            throw new Exception($errErrorCode->c_description);
                    }
                    break;
                case 'RC':
                case 'RA':
                    $sendSummary = $client->__soapCall('sendSummary', array($params));
                    file_put_contents('cdn/test.txt', $client->__getLastRequest());                    
                    $docInvoiceFile->c_has_sunat_response = 'yes';
                    $docInvoiceFile->save();
                    switch (get_class($sendSummary)) {
                        case 'stdClass':
                            $docInvoiceTicket = DocInvoiceTicket::find($id);
                            $docInvoiceTicket->c_ticket = $sendSummary->ticket;
                            $docInvoiceTicket->c_has_ticket = 'yes';
                            $docInvoiceTicket->c_ticket_code = 98;
                            $docInvoiceTicket->save();

                            $response['status'] = 1;
                            $response['ticket'] = $sendSummary->ticket;
                            break;
                        case 'SoapFault':
                            $error = $sendSummary->faultcode;
                            $errorExplode = explode('.', $error);
                            $errorCode = end($errorExplode);
                            $errErrorCode = ErrErrorCode::find($errorCode);
                            throw new Exception($errErrorCode->c_description);
                    }
                    break;
            }

            Log::info('Envío a SUNAT',
                [
                'lgph_id' => 5, 'n_id_invoice' => $id, 'c_invoice_type_code' => $nameExplode[1]
                ]
            );
        } catch (Exception $exc) {
            $response['message'] = $exc->getMessage();

            Log::error($response['message'],
                [
                'lgph_id' => 5, 'n_id_invoice' => $id, 'c_invoice_type_code' => $nameExplode[1]
                ]
            );
        }
        return $response;
    }

    public static function readCdr($nIdInvoice, $path, $invoiceTypeCode)
    {
        $response['status'] = 0;
        $response['code'] = '';
        $response['message'] = '';
        $message = '';

        if(env('SYSTEM')=='linux')
            $nameExplode = explode('/', $path);
        elseif(env('SYSTEM')=='windows'){
            $nameExplode = explode('\\', $path);
            //TODO fix var $name
        }

        $name = end($nameExplode);
        $nameCdrExplode = explode('.', $name);
        $nameCdr = reset($nameCdrExplode) . '.XML';

        \Zipper::make($path)->extractTo(storage_path('sunat/tmp'));
        $pathCdr = storage_path('sunat/tmp/' . $nameCdr);

        try {
            $cdr = UtilHelper::cdr($pathCdr);

            # Grabar respuesta (CDR) en la base de Datos.
            $docInvoiceCdr = DocInvoiceCdr::destroy($nIdInvoice);

            $docInvoiceCdr = new DocInvoiceCdr();
            $docInvoiceCdr->n_id_invoice = $nIdInvoice;
            $docInvoiceCdr->c_ubl_version_id = (isset($cdr['UBLVersionID'])) ? $cdr['UBLVersionID'] : null;
            $docInvoiceCdr->c_customization_id = (isset($cdr['CustomizationID'])) ? $cdr['CustomizationID'] : null;
            $docInvoiceCdr->c_id = $cdr['ID'];
            $docInvoiceCdr->d_issue_date = $cdr['IssueDate'];
            $docInvoiceCdr->d_issue_time = $cdr['IssueTime'];
            $docInvoiceCdr->d_response_date = $cdr['ResponseDate'];
            $docInvoiceCdr->d_response_time = $cdr['ResponseTime'];
            $docInvoiceCdr->save();

            $docInvoiceCdrSenderParty = new DocInvoiceCdrSenderParty();
            $docInvoiceCdrSenderParty->n_id_invoice = $nIdInvoice;
            $docInvoiceCdrSenderParty->c_party_identification_id = $cdr['SenderParty']['PartyIdentification']['ID'];
            $docInvoiceCdrSenderParty->save();

            $docInvoiceCdrReceiverParty = new DocInvoiceCdrReceiverParty();
            $docInvoiceCdrReceiverParty->n_id_invoice = $nIdInvoice;
            $docInvoiceCdrReceiverParty->c_party_identification_id = $cdr['ReceiverParty']['PartyIdentification']['ID'];
            $docInvoiceCdrReceiverParty->save();

            $docInvoiceCdrDocumentResponse = new DocInvoiceCdrDocumentResponse();
            $docInvoiceCdrDocumentResponse->n_id_invoice = $nIdInvoice;
            $docInvoiceCdrDocumentResponse->c_response_reference_id = $cdr['DocumentResponse']['Response']['ReferenceID'];
            $docInvoiceCdrDocumentResponse->c_response_response_code = $cdr['DocumentResponse']['Response']['ResponseCode'];
            $docInvoiceCdrDocumentResponse->c_response_description = $cdr['DocumentResponse']['Response']['Description'];
            $docInvoiceCdrDocumentResponse->c_document_reference_id = $cdr['DocumentResponse']['DocumentReference']['ID'];
            $docInvoiceCdrDocumentResponse->c_recipient_party_party_identification_id = $cdr['DocumentResponse']['RecipientParty']['PartyIdentification']['ID'];
            $docInvoiceCdrDocumentResponse->save();

            if (isset($cdr['Note'])) {
                foreach ($cdr['Note'] as $key => $value) {
                    $note = (string) $value;
                    $noteExplode = explode(' - ', $note);
                    $docInvoiceCdrNote = new DocInvoiceCdrNote();
                    $docInvoiceCdrNote->n_id_invoice = $nIdInvoice;
                    $docInvoiceCdrNote->c_note = $note;
                    $docInvoiceCdrNote->c_code = reset($noteExplode);
                    $docInvoiceCdrNote->c_description = end($noteExplode);
                    $docInvoiceCdrNote->save();
                }
            }

            $docInvoiceCdrStatus = DocInvoiceCdrStatus::find($nIdInvoice);
            switch ($cdr['DocumentResponse']['Response']['ResponseCode']) {
                case '0':
                    $response['code'] = $cdr['DocumentResponse']['Response']['ResponseCode'];
                    if (isset($cdr['Note'])) {
                        # Observado
                        foreach ($cdr['Note'] as $key => $value) {
                            $response['message'][$key] = $value;
                        }
                        $message = implode(', ', $response['message']);
                        $docInvoiceCdrStatus->n_id_cdr_status = 3;
                    } else {
                        # Aceptado
                        $docInvoiceCdrStatus->n_id_cdr_status = 1;
                        $response['message'] = $cdr['DocumentResponse']['Response']['Description'];
                        $docInvoiceFile = DocInvoiceFile::find($nIdInvoice);
                        $docInvoiceFile->c_has_sunat_successfully_passed = 'yes';
                        $docInvoiceFile->save();
                        $message = $response['message'];
                    }
                    break;
                default:
                    # Rechazado
                    $response['code'] = $cdr['DocumentResponse']['Response']['ResponseCode'];
                    $response['message'] = $cdr['DocumentResponse']['Response']['Description'];
                    $errErrorCode = ErrErrorCode::find($response['code']);
                    $message = $errErrorCode->c_description;
                    $docInvoiceCdrStatus->n_id_cdr_status = 2;
                    break;
            }
            $docInvoiceCdrStatus->save();

            $response['status'] = 1;

            Log::info($message,
                [
                'lgph_id' => 6, 'n_id_invoice' => $nIdInvoice,
                'c_id_error_code' => $cdr['DocumentResponse']['Response']['ResponseCode'],
                'c_invoice_type_code' => $invoiceTypeCode,
                ]
            );
        } catch (Exception $exc) {
            $response['message'] = $exc->getMessage();

            Log::error($response['message'],
                [
                'lgph_id' => 6, 'n_id_invoice' => $nIdInvoice, 'c_invoice_type_code' => $invoiceTypeCode,
                ]
            );
        }

        unlink($pathCdr);

        return $response;
    }

}
