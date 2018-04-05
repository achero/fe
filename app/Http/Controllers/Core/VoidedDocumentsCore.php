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

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Comunicacion de baja
 */
class VoidedDocumentsCore
{

    public static function getFile($file)
    {
        $output['status'] = false;
        $output['message'] = '';
        $output['document'] = '';
        $response = [];
        $count = [];
        try {
            $c['VoidedDocuments']['sac:VoidedDocumentsLine'] = 0;
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
                                    $response['VoidedDocuments']['cac:AccountingSupplierParty']['cac:Party']['cac:PartyLegalEntity']['cbc:RegistrationName'] = $v;
                                    break;
                                case 1:
                                    $explode = explode('!', $v);
                                    foreach ($explode as $ke => $va) {
                                        switch ($ke) {
                                            case 0:
                                                $response['VoidedDocuments']['cac:AccountingSupplierParty']['cbc:CustomerAssignedAccountID'] = $va;
                                                break;
                                            case 1:
                                                $response['VoidedDocuments']['cac:AccountingSupplierParty']['cbc:AdditionalAccountID'] = $va;
                                                break;
                                        }
                                    }
                                    break;
                                case 2:
                                    $response['VoidedDocuments']['cbc:ReferenceDate'] = $v;
                                    break;
                                case 3:
                                    $response['VoidedDocuments']['cbc:ID'] = $v;
                                    break;
                                case 4:
                                    $response['VoidedDocuments']['cbc:IssueDate'] = $v;
                                    break;
                                case 5:
                                    # Firma Digital
                                    break;
                                case 6:
                                    $response['VoidedDocuments']['cbc:UBLVersionID'] = $v;
                                    break;
                                case 7:
                                    $response['VoidedDocuments']['cbc:CustomizationID'] = $v;
                                    break;
                            }
                        }
                        break;
                    default:
                        foreach ($line as $k => $v) {
                            switch ($k) {
                                case 0:
                                    $response['VoidedDocuments']['sac:VoidedDocumentsLine']
                                        [$c['VoidedDocuments']['sac:VoidedDocumentsLine']]['cbc:DocumentTypeCode'] = $v;
                                    break;
                                case 1:
                                    $response['VoidedDocuments']['sac:VoidedDocumentsLine']
                                        [$c['VoidedDocuments']['sac:VoidedDocumentsLine']]['sac:DocumentSerialID'] = $v;
                                    break;
                                case 2:
                                    $response['VoidedDocuments']['sac:VoidedDocumentsLine']
                                        [$c['VoidedDocuments']['sac:VoidedDocumentsLine']]['sac:DocumentNumberID'] = $v;
                                    break;
                                case 3:
                                    $response['VoidedDocuments']['sac:VoidedDocumentsLine']
                                        [$c['VoidedDocuments']['sac:VoidedDocumentsLine']]['sac:VoidReasonDescription'] = $v;
                                    break;
                                case 4:
                                    $response['VoidedDocuments']['sac:VoidedDocumentsLine']
                                        [$c['VoidedDocuments']['sac:VoidedDocumentsLine']]['cbc:LineID'] = $v;
                                    break;
                            }
                        }
                        $c['VoidedDocuments']['sac:VoidedDocumentsLine'] ++;
                        break;
                }
            }
            $output['status'] = true;
            $output['document'] = $response;

            Log::info('Generación de array',
                [
                'lgph_id' => 1, 'c_id' => $response['VoidedDocuments']['cbc:ID'],
                'c_invoice_type_code' => 'RA',
                ]
            );
        } catch (Exception $exc) {
            Log::error($exc->getMessage(), ['lgph_id' => 1]);
            $output['message'] = array($exc->getMessage());
        }
        return $output;
    }

    public static function validateFile($array)
    {
        $validation = array(
            'VoidedDocuments.cbc:ReferenceDate' => 'required|max:10',
            'VoidedDocuments.cbc:ID' => 'required|max:17',
            'VoidedDocuments.cbc:IssueDate' => 'required|max:10',
            'VoidedDocuments.cbc:UBLVersionID' => 'required|max:10',
            'VoidedDocuments.cbc:CustomizationID' => 'required|max:10',
            'VoidedDocuments.cac:AccountingSupplierParty.cac:Party.cac:PartyLegalEntity.cbc:RegistrationName' => 'required|max:100',
            'VoidedDocuments.cac:AccountingSupplierParty.cbc:CustomerAssignedAccountID' => 'required|size:11',
            'VoidedDocuments.cac:AccountingSupplierParty.cbc:AdditionalAccountID' => 'required|size:1',
        );

        $messages = array(
            'VoidedDocuments.cbc:ReferenceDate.required' => 'Fecha de generación del documento dado de baja es requerido.',
            'VoidedDocuments.cbc:ReferenceDate.max' => 'Fecha de generación del documento dado de baja excedió la cantidad de caracteres.',
            'VoidedDocuments.cbc:ID.required' => 'Identificador de la comunicación es requerido.',
            'VoidedDocuments.cbc:ID.max' => 'Identificador de la comunicación excedió la cantidad de caracteres.',
            'VoidedDocuments.cbc:IssueDate.required' => 'Fecha de generación de la comunicación es requerido.',
            'VoidedDocuments.cbc:IssueDate.max' => 'Fecha de generación de la comunicación excedió la cantidad de caracteres.',
            'VoidedDocuments.cbc:UBLVersionID.required' => 'Versión del UBL utilizado para establecer el formato XML es requerido.',
            'VoidedDocuments.cbc:UBLVersionID.max' => 'Versión del UBL utilizado para establecer el formato XML excedió la cantidad de caracteres.',
            'VoidedDocuments.cbc:CustomizationID.required' => 'Versión de la estructura del documento es requerido.',
            'VoidedDocuments.cbc:CustomizationID.max' => 'Versión de la estructura del documento excedió la cantidad de caracteres.',
            'VoidedDocuments.cac:AccountingSupplierParty.cac:Party.cac:PartyLegalEntity.cbc:RegistrationName.required' => 'Apellidos y nombres o denominación o razón social es requerido.',
            'VoidedDocuments.cac:AccountingSupplierParty.cac:Party.cac:PartyLegalEntity.cbc:RegistrationName.max' => 'Apellidos y nombres o denominación o razón social excedió los 100 caracteres.',
            'VoidedDocuments.cac:AccountingSupplierParty.cbc:CustomerAssignedAccountID.required' => 'Número de RUC es requerido.',
            'VoidedDocuments.cac:AccountingSupplierParty.cbc:CustomerAssignedAccountID.size' => 'Número de RUC no tiene 11 caracteres.',
            'VoidedDocuments.cac:AccountingSupplierParty.cbc:AdditionalAccountID.required' => 'Número de RUC - Tipo de Documento es requerido.',
            'VoidedDocuments.cac:AccountingSupplierParty.cbc:AdditionalAccountID.size' => 'Número de RUC - Tipo de Documento no tiene 1 caracter.',
        );

        $validator = Validator::make($array, $validation, $messages);

        if ($validator->fails()) {
            Log::error('Error en cabecera: ' . implode(',', $validator->messages()->all()),
                ['lgph_id' => 2, 'c_id' => $array['VoidedDocuments']['cbc:ID'], 'c_invoice_type_code' => 'RA',]);
            throw new Exception(implode(',', $validator->messages()->all()));
        }

        $voidedDocumentsLine = 1;
        foreach ($array['VoidedDocuments']['sac:VoidedDocumentsLine'] as $key => $value) {
            $itemIndex = $key + 1;
            $validation = array(
                'cbc:DocumentTypeCode' => 'required|size:2',
                'sac:DocumentSerialID' => 'required|size:4',
                'sac:DocumentNumberID' => 'required|max:8',
                'sac:VoidReasonDescription' => 'required|max:100',
                'cbc:LineID' => 'required|max:5',
            );

            $messages = array(
                'cbc:DocumentTypeCode.required' => sprintf('Ítem %s | Tipo de Documento es requerido.',
                    $voidedDocumentsLine),
                'cbc:DocumentTypeCode.size' => sprintf('Ítem %s | Tipo de Documento no tiene 2 caracteres.',
                    $voidedDocumentsLine),
                'sac:DocumentSerialID.required' => sprintf('Ítem %s | Serie del documento dado de baja es requerido.',
                    $voidedDocumentsLine),
                'sac:DocumentSerialID.size' => sprintf('Ítem %s | Serie del documento dado de baja no tiene 4 caracteres.',
                    $voidedDocumentsLine),
                'sac:DocumentNumberID.required' => sprintf('Ítem %s | Número correlativo del documento dado de baja es requerido.',
                    $voidedDocumentsLine),
                'sac:DocumentNumberID.max' => sprintf('Ítem %s | Número correlativo del documento dado de baja excedió los 8 caracteres.',
                    $voidedDocumentsLine),
                'sac:VoidReasonDescription.required' => sprintf('Ítem %s | Motivo de baja es requerido.',
                    $voidedDocumentsLine),
                'sac:VoidReasonDescription.max' => sprintf('Ítem %s | Motivo de baja excedió los 100 caracteres.',
                    $voidedDocumentsLine),
                'cbc:LineID.required' => sprintf('Ítem %s | Número de ítem es requerido.', $voidedDocumentsLine),
                'cbc:LineID.max' => sprintf('Ítem %s | Número de ítem excedió los 5 caracteres.', $voidedDocumentsLine),
            );
            $validator = Validator::make($value, $validation, $messages);
            if ($validator->fails()) {
                Log::error("Error en detalle, Ítem #{$itemIndex}: " . implode(',', $validator->messages()->all()),
                    ['lgph_id' => 2, 'c_id' => $array['VoidedDocuments']['cbc:ID'], 'c_invoice_type_code' => 'RA',]);
                throw new Exception(implode(',', $validator->messages()->all()));
            }
            $voidedDocumentsLine++;
        }

        Log::info('Validación de array',
            [
            'lgph_id' => 2, 'c_id' => $array['VoidedDocuments']['cbc:ID'],
            'c_invoice_type_code' => 'RA',
            ]
        );
    }

    public static function setDb($array)
    {
        $response['status'] = 0;
        $response['id'] = 0;
        $response['message'] = '';

        try {
            $inSupplier = $array['VoidedDocuments']['cac:AccountingSupplierParty'];
            $supplier = SupSupplier::where('c_customer_assigned_account_id',
                    $inSupplier['cbc:CustomerAssignedAccountID'])
                ->where('c_additional_account_id', $inSupplier['cbc:AdditionalAccountID'])
                ->where('c_status_supplier', 'visible');

            if ($supplier->count() == 0) {
                throw new Exception('Emisor no registrado');
            }

            $supplier = $supplier->first();

            $supplier->c_party_party_legal_entity_registration_name = $inSupplier['cac:Party']['cac:PartyLegalEntity']
                ['cbc:RegistrationName'];
            $supplier->save();
            $supplierId = $supplier->n_id_supplier;

            # DocInvoice
            $inVoidedDocuments = $array['VoidedDocuments'];
            $voidedDocuments = DocInvoice::where('c_id', $inVoidedDocuments['cbc:ID'])->where('n_id_supplier',
                        $supplierId)
                    ->where('c_invoice_type_code', 'RA')->whereIn('c_status_invoice', array('visible', 'hidden'));

            if ($voidedDocuments->count()) {
                $voidedDocuments->update(array('c_status_invoice' => 'deleted'));
            }

            $voidedDocuments = new DocInvoice();
            $voidedDocuments->n_id_supplier = $supplierId;
            $voidedDocuments->d_issue_date = $inVoidedDocuments['cbc:IssueDate'];
            $voidedDocuments->c_invoice_type_code = 'RA'; # Comunicacion de Baja
            $voidedDocuments->c_id = $inVoidedDocuments['cbc:ID'];
            $cIdExplode = explode('-', $inVoidedDocuments['cbc:ID']);
            $voidedDocuments->c_correlative = end($cIdExplode);
            $voidedDocuments->n_correlative = (int) end($cIdExplode);
            $voidedDocuments->c_ubl_version_id = $inVoidedDocuments['cbc:UBLVersionID'];
            $voidedDocuments->c_customization_id = $inVoidedDocuments['cbc:CustomizationID'];
            $voidedDocuments->d_reference_date = $inVoidedDocuments['cbc:ReferenceDate'];
            $voidedDocuments->c_status_invoice = 'visible';
            $voidedDocuments->save();
            $voidedDocumentsId = $voidedDocuments->n_id_invoice;

            $docInvoiceCdrStatus = new DocInvoiceCdrStatus();
            $docInvoiceCdrStatus->n_id_invoice = $voidedDocumentsId;
            $docInvoiceCdrStatus->n_id_cdr_status = 4;
            $docInvoiceCdrStatus->save();

            # DocInvoiceSupplier
            $voidedDocumentsSupplier = new DocInvoiceSupplier();
            $voidedDocumentsSupplier->n_id_invoice = $voidedDocumentsId;
            $voidedDocumentsSupplier->n_id_supplier = $supplierId;
            $voidedDocumentsSupplier->c_customer_assigned_account_id = $inSupplier['cbc:CustomerAssignedAccountID'];
            $voidedDocumentsSupplier->c_additional_account_id = $inSupplier['cbc:AdditionalAccountID'];
            $voidedDocumentsSupplier->c_party_party_legal_entity_registration_name = $inSupplier['cac:Party']['cac:PartyLegalEntity']
                ['cbc:RegistrationName'];
            $voidedDocumentsSupplier->save();

            # DocInvoiceItem
            $inVoidedDocumentsLine = $inVoidedDocuments['sac:VoidedDocumentsLine'];
            foreach ($inVoidedDocumentsLine as $value) {
                $voidedDocumentsItem = new DocInvoiceItem();
                $voidedDocumentsItem->n_id_invoice = $voidedDocumentsId;
                $voidedDocumentsItem->c_document_type_code = $value['cbc:DocumentTypeCode'];
                $voidedDocumentsItem->c_document_serial_id = $value['sac:DocumentSerialID'];
                $voidedDocumentsItem->c_document_number_id = $value['sac:DocumentNumberID'];
                $voidedDocumentsItem->c_void_reason_description = $value['sac:VoidReasonDescription'];
                $voidedDocumentsItem->n_id = $value['cbc:LineID'];
                $voidedDocumentsItem->save();
            }

            # Ticket
            $docInvoiceTicket = new DocInvoiceTicket();
            $docInvoiceTicket->n_id_invoice = $voidedDocumentsId;
            $docInvoiceTicket->c_has_ticket = 'no';
            $docInvoiceTicket->save();

            $response['id'] = $voidedDocumentsId;
            $response['status'] = 1;

            Log::info('Grabado en BD',
                [
                'lgph_id' => 3, 'c_id' => $array['VoidedDocuments']['cbc:ID'], 'c_invoice_type_code' => 'RA',
                'n_id_invoice' => $voidedDocumentsId,
                ]
            );
        } catch (Exception $exc) {
            Log::error($exc->getMessage(),
                [
                'lgph_id' => 3, 'c_id' => $array['VoidedDocuments']['cbc:ID'], 'c_invoice_type_code' => 'RA',
                ]
            );
            $response['message'] = $exc->getMessage();
        }

        return $response;
    }

    public static function buildXml($voidedDocumentsId)
    {
        $response['status'] = 0;
        $response['message'] = '';
        $response['path'] = '';

        //try {
            $voidedDocumentsData = DocInvoice::with('SupSupplier.SupSupplierConfiguration')->where('n_id_invoice',
                $voidedDocumentsId);

            if ($voidedDocumentsData->count() == 0) {
                throw new Exception('No se encuentra en BD');
            }

            $voidedDocumentsData = $voidedDocumentsData->first();
            $signatureId = 'VD' . $voidedDocumentsData->c_id;
            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->xmlStandalone = false;
            $dom->formatOutput = true;

            $voidedDocuments = $dom->createElement('VoidedDocuments');
            $newNode = $dom->appendChild($voidedDocuments);
            $newNode->setAttribute('xmlns', 'urn:sunat:names:specification:ubl:peru:schema:xsd:VoidedDocuments-1');
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
            $voidedDocuments->appendChild($ublExtensions);

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
            $ublVersionID = $dom->createElement('cbc:UBLVersionID', $voidedDocumentsData->c_ubl_version_id);
            $voidedDocuments->appendChild($ublVersionID);
            $customizationID = $dom->createElement('cbc:CustomizationID', $voidedDocumentsData->c_customization_id);
            $voidedDocuments->appendChild($customizationID);
            $id = $dom->createElement('cbc:ID', $voidedDocumentsData->c_id);
            $voidedDocuments->appendChild($id);
            $referenceDate = $dom->createElement('cbc:ReferenceDate', $voidedDocumentsData->d_reference_date);
            $voidedDocuments->appendChild($referenceDate);
            $issueDate = $dom->createElement('cbc:IssueDate', $voidedDocumentsData->d_issue_date);
            $voidedDocuments->appendChild($issueDate);

            # Firma
            $signature = $dom->createElement('cac:Signature');
            $voidedDocuments->appendChild($signature);
            $id = $dom->createElement('cbc:ID', $signatureId);
            $signature->appendChild($id);
            $signatoryParty = $dom->createElement('cac:SignatoryParty');
            $signature->appendChild($signatoryParty);
            $partyIdentification = $dom->createElement('cac:PartyIdentification');
            $signatoryParty->appendChild($partyIdentification);
            $id = $dom->createElement('cbc:ID', $voidedDocumentsData->DocInvoiceSupplier->c_customer_assigned_account_id);
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
            $invoiceSupplierData = $voidedDocumentsData->SupSupplier;
            $accountingSupplierParty = $dom->createElement('cac:AccountingSupplierParty');
            $voidedDocuments->appendChild($accountingSupplierParty);
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

            $partyLegalEntity = $dom->createElement('cac:PartyLegalEntity');
            $party->appendChild($partyLegalEntity);
            $registrationName = $dom->createElement('cbc:RegistrationName');
            $registrationName->appendChild($dom->createCDATASection(
                    $invoiceSupplierData->c_party_party_legal_entity_registration_name));
            $partyLegalEntity->appendChild($registrationName);

            # DocInvoiceItem
            $invoiceItemData = $voidedDocumentsData->DocInvoiceItem;
            foreach ($invoiceItemData as $value) {
                $voidedDocumentsLine = $dom->createElement('sac:VoidedDocumentsLine');
                $voidedDocuments->appendChild($voidedDocumentsLine);

                $lineID = $dom->createElement('cbc:LineID', $value->n_id);
                $voidedDocumentsLine->appendChild($lineID);
                $documentTypeCode = $dom->createElement('cbc:DocumentTypeCode', $value->c_document_type_code);
                $voidedDocumentsLine->appendChild($documentTypeCode);
                $documentSerialID = $dom->createElement('sac:DocumentSerialID', $value->c_document_serial_id);
                $voidedDocumentsLine->appendChild($documentSerialID);
                $documentNumberID = $dom->createElement('sac:DocumentNumberID', $value->c_document_number_id);
                $voidedDocumentsLine->appendChild($documentNumberID);
                $voidReasonDescription = $dom->createElement('sac:VoidReasonDescription');
                $voidReasonDescription->appendChild($dom->createCDATASection($value->c_void_reason_description));
                $voidedDocumentsLine->appendChild($voidReasonDescription);
            }
            # Procesamiento de Documento
            $xmlName = sprintf('%s-%s', $invoiceSupplierData->c_customer_assigned_account_id, $voidedDocumentsData->c_id);
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

            $zipPath = $voidedDocumentsData->SupSupplier->SupSupplierConfiguration->c_public_path_document . DIRECTORY_SEPARATOR . $xmlName . '.ZIP';
            $zipFullPath = public_path($zipPath);

            # DocInvoiceFile
            $docInvoiceFile = new DocInvoiceFile();
            $docInvoiceFile->n_id_invoice = $voidedDocumentsId;
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
                'lgph_id' => 4, 'n_id_invoice' => $voidedDocumentsId, 'c_invoice_type_code' => 'RA'
                ]
            );
        /*} catch (Exception $exc) {
            Log::error($exc->getMessage(),
                [
                'lgph_id' => 4, 'n_id_invoice' => $voidedDocumentsId, 'c_invoice_type_code' => 'RA'
                ]
            );
            $response['message'] = $exc->getMessage();
        }*/
        return $response;
    }

}
