<?php
namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Log;

use App\DocInvoice;


trait MailHelper
{

    /**
     * PDF y XML del documento electrónico emitido al cliente.
     * @param type $invoiceId
     */
    public static function customerFiles($invoiceId)
    {
        $response['status'] = 0;
        $response['message'] = '';

        try {
            $query = function ($q) {
                $q->whereIn('n_id_cdr_status', array(1, 2, 3));
            };
            $docInvoice = DocInvoice::whereHas('DocInvoiceCdrStatus', $query)->has('DocInvoiceFile')
                    ->has('DocInvoiceExtraData')->has('DocInvoiceCustomer')->with(
                        'DocInvoiceFile', 'DocInvoiceExtraData', 'DocInvoiceCustomer',
                        'DocInvoiceCdr.DocInvoiceCdrDocumentResponse.ErrErrorCode', 'DocInvoiceSupplier',
                        'SupSupplier.SupSupplierConfiguration'
                    )
                    ->with(['DocInvoiceCdrStatus' => $query])->where('n_id_invoice', $invoiceId);

            if ($docInvoice->count() == 0) {
                throw new Exception('El documento no cuenta con un CDR.');
            }

            $docInvoice = $docInvoice->first();

            $sentCustomerEmail = $docInvoice->SupSupplier->SupSupplierConfiguration->c_email_sent_customer;
            $sentSupplierEmail = $docInvoice->SupSupplier->SupSupplierConfiguration->c_email_sent_supplier;
            if (($sentCustomerEmail == 'no') && ($sentSupplierEmail == 'no')) {
                return $response;
            }

            if (
                empty($docInvoice->DocInvoiceExtraData->c_customer_email) ||
                is_null($docInvoice->DocInvoiceExtraData->c_customer_email)
            ) {
                Log::info('El documento no cuenta con correo del cliente registrado',
                    ['lgph_id' => 9, 'n_id_invoice' => $invoiceId, 'c_invoice_type_code' => $docInvoice->c_invoice_type_code]);
            }

            if (
                ($docInvoice->DocInvoiceCdr->DocInvoiceCdrDocumentResponse->ErrErrorCode->count() > 0) &&
                (
                $docInvoice->DocInvoiceCdr->DocInvoiceCdrDocumentResponse->ErrErrorCode->c_is_forwardable == 1
                )
            ) {
                throw new Exception('El documento no puede ser enviado por correo ya que presenta un error de tipo excepción "re-enviable"');
            }

            $validate['c_customer_email'] = $docInvoice->DocInvoiceExtraData->c_customer_email;

            # Verificar si se puede enviar correos.
            $data = [];
            $paths = [];
            $customerEmail = $validate['c_customer_email'];
            $supplierEmail = $docInvoice->SupSupplier->c_email;


            # Verifica si el cliente tuviese un correo valido.
            $rules['c_customer_email'] = 'email';
            $validator = Validator::make($validate, $rules);
            if ($validator->fails()) {
                $response['message'] = 'Correo del cliente "' . $validate['c_customer_email'] . '" erroneo.';
                Log::error($response['message'],
                    ['lgph_id' => 9, 'n_id_invoice' => $invoiceId, 'c_invoice_type_code' => $docInvoice->c_invoice_type_code]);
                # Si tambien no se va a enviar correo al emisor se retorna.
                if ($sentSupplierEmail == 'no') {
                    return $response;
                }
                $customerEmail = null;
            }

            switch ($docInvoice->DocInvoiceCdrStatus->n_id_cdr_status) {
                case 1:
                    $cInvoiceTypeCode = $docInvoice->c_invoice_type_code;
                    $paths['pdf'] = public_path(
                        $docInvoice->SupSupplier->SupSupplierConfiguration->c_public_path_pdf . DIRECTORY_SEPARATOR . $docInvoice->DocInvoiceFile->c_pdf_name
                    );
                    $paths['xml'] = public_path(
                        $docInvoice->SupSupplier->SupSupplierConfiguration->c_public_path_document . DIRECTORY_SEPARATOR . $docInvoice->DocInvoiceFile->c_document_name
                    );
                    Mail::queue('email.customer.success', $data,
                        function($message) use ($paths, $customerEmail, $supplierEmail, $sentCustomerEmail, $sentSupplierEmail, $cInvoiceTypeCode) {
                        if (!empty($customerEmail) && ($sentCustomerEmail == 'yes')) {
                            $message->to($customerEmail);
                        } else if ($sentSupplierEmail == 'yes') {
                            $message->to($supplierEmail);
                        }
                        if (!empty($customerEmail) && ($sentCustomerEmail == 'yes') && ($sentSupplierEmail == 'yes')) {
                            $message->cc($supplierEmail);
                        }
                        $message->subject('Documento electrónico aceptado por la SUNAT');
                        if (!in_array($cInvoiceTypeCode, ['RA', 'RC',])) {
                            $message->attach($paths['pdf']);
                        }
                        $message->attach($paths['xml']);
                    });
                    $docInvoice->DocInvoiceExtraData->c_email_was_sent = 'yes';
                    $docInvoice->DocInvoiceExtraData->save();
                    break;
                case 2:
                    $data['cliente'] = $docInvoice->DocInvoiceCustomer->c_party_party_legal_entity_registration_name;
                    $data['documento'] = $docInvoice->c_id;
                    Mail::queue('email.customer.observed', $data,
                        function($message) use ($customerEmail, $supplierEmail, $sentCustomerEmail, $sentSupplierEmail) {
                        if (!empty($customerEmail) && ($sentCustomerEmail == 'yes')) {
                            $message->to($customerEmail);
                        } else if ($sentSupplierEmail == 'yes') {
                            $message->to($supplierEmail);
                        }
                        if (!empty($customerEmail) && ($sentCustomerEmail == 'yes') && ($sentSupplierEmail == 'yes')) {
                            $message->cc($supplierEmail);
                        }
                        $message->subject('Documento electrónico observado por la SUNAT');
                    });
                    $docInvoice->DocInvoiceExtraData->c_email_was_sent = 'yes';
                    $docInvoice->DocInvoiceExtraData->save();
                    break;
                case 3:
                    $data['cliente'] = $docInvoice->DocInvoiceCustomer->c_party_party_legal_entity_registration_name;
                    $data['documento'] = $docInvoice->c_id;
                    Mail::queue('email.customer.rejected', $data,
                        function($message) use ($customerEmail, $supplierEmail, $sentCustomerEmail, $sentSupplierEmail) {
                        if (!empty($customerEmail) && ($sentCustomerEmail == 'yes')) {
                            $message->to($customerEmail);
                        } else if ($sentSupplierEmail == 'yes') {
                            $message->to($supplierEmail);
                        }
                        if (!empty($customerEmail) && ($sentCustomerEmail == 'yes') && ($sentSupplierEmail == 'yes')) {
                            $message->cc($supplierEmail);
                        }
                        $message->subject('Documento electrónico rechazado por la SUNAT');
                    });
                    $docInvoice->DocInvoiceExtraData->c_email_was_sent = 'yes';
                    $docInvoice->DocInvoiceExtraData->save();
                    break;
            }
            $response['status'] = 1;

            Log::info('Envío e-mail',
                ['lgph_id' => 9, 'n_id_invoice' => $invoiceId, 'c_invoice_type_code' => $docInvoice->c_invoice_type_code]);
        } catch (Exception $exc) {
            $response['message'] = $exc->getMessage();

            Log::error($response['message'], ['lgph_id' => 9, 'n_id_invoice' => $invoiceId,]);
        }
        return $response;
    }

}
