<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Validator;
use Exception;

use App\Http\Controllers\Core;
use App\Http\Controllers\Traits\UtilHelper;
use App\Http\Controllers\Traits\SunatHelper;
use App\Http\Controllers\Traits\MailHelper;

class ProcessInputTxtController extends Controller
{

    use UtilHelper;
    use SunatHelper;

    public function upload(Request $request)
    {
    
        try {
            Log::info('PROCESAMIENTO INDIVIDUAL, INICIO');


            # Verifico si el archivo a procesar cumple los requisitos mínimos
            $input = $request->all();
            $validation = array('text_plain' => 'required|mimes:txt');
            $messages = array(
                'text_plain.required' => 'Es necesario el Archivo de Texto',
                'text_plain.mimes' => 'Sólo se permite Archivos con extensión "txt"'
            );
            $validator = Validator::make($input, $validation, $messages);
            if ($validator->fails()) {
                throw new Exception(implode(',', $validator->messages()->all()));
            }

            $fileRaw = $request->file('text_plain');
            $fn = explode('-', $fileRaw->getClientOriginalName());

            //dd($fn);


            # Verifica si es uno de los documentos SUNAT contemplados
            switch ($fn[2]) {
                case '01':
                    $core = new Core\InvoiceCore();
                    break;
                case '03':
                    $core = new Core\BillCore();
                    break;
                case '07':
                    $core = new Core\CreditNoteCore();
                    break;
                case '08':
                    $core = new Core\DebitNoteCore();
                    break;
                case 'RA':
                    $core = new Core\VoidedDocumentsCore();
                    break;
                case 'RC':
                    $core = new Core\SummaryDocumentsCore();
                    break;
                default:
                    throw new Exception("El tipo de documento {$fn[02]} no está contemplado");
            }

            $file = file($fileRaw);

            //die('test2');


            # Verifica documento
            $verifyDocument = UtilHelper::verifyDocument($fileRaw->getClientOriginalName(), 'WEBSITE', $fileRaw);
            if (!$verifyDocument['status']) {
                throw new Exception($verifyDocument['message']);
            }

            //die('test3');

            # Convierte el texto en un array
            $array = $core->getFile($file);
            if (!$array['status']) {
                throw new Exception($array['message']);
            }

            //die('test4');
            //dd($array['document']);

            # Valida el input, si hay un problema genera una excepcion con un mensaje
            $validateFile = $core->validateFile($array['document']);


            # Graba en base el array
            $setDb = $core->setDb($array['document']);

            if (!$setDb['status']) {
                throw new Exception($setDb['message']);
            }

            //die('test5');

            # Genera el XML
            $xml = $core->buildXml($setDb['id']);
            if (!$xml['status']) {
                throw new Exception($xml['message']);
            }

            //die('test6');
            //dd($xml);


            # Graba datos del input en la base
            $saveInvoiceInput = UtilHelper::saveInvoiceInput($setDb['id'], 'WEBSITE', $fileRaw);
            if (!$saveInvoiceInput['status']) {
                throw new Exception($saveInvoiceInput['message']);
            }

            //die('test7');
            
            if (!in_array($fn[2], ['RA', 'RC',])) {
                # Publicar PDF
                $pdf = UtilHelper::pdf($setDb['id']);
                if (!$pdf['status']) {
                    throw new Exception($pdf['message']);
                }
            }

            //die('test8');
            

            
            # Enviar a la SUNAT
            $sent = SunatHelper::sent($xml['path'], $setDb['id']);

            if (!$sent['status']) {
                throw new Exception($sent['message']);
            }


            //die('test9');
            # Si es RD o CB ejecutar este proceso adicional
            
            switch ($fn[2]) {
                case 'RA':
                case 'RC':
                    
                    //TODO FIX
                    dd($sent['ticket']);
                    
                    $sent = SunatHelper::ticket($sent['ticket']);
                    if (!$sent['status']) {
                        throw new Exception($sent['message']);
                    }
                    break;
            }

            # Lee el CDR y lo graba en BD
            $readCdr = SunatHelper::readCdr($setDb['id'], $sent['path'], $fn[2]);
            if (!$readCdr['status']) {
                throw new Exception($readCdr['message']);
            }


            # Enviar por correo emisor/cliente
            /* CONFIGURAR SMTP EN .ENV*/
            //MailHelper::customerFiles($setDb['id']);


        } catch (Exception $exc) {
            Log::info('PROCESAMIENTO INDIVIDUAL, FIN');

            return back()->with('message_error', $exc->getMessage());
        }
        Log::info('PROCESAMIENTO INDIVIDUAL, FIN');

        return back()->with('message_success', 'Operación concluida correctamente');
    

    }


}
