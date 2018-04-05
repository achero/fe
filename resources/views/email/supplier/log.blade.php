<h3>Detalle de incidencia</h3>
<p><strong>Fecha:</strong> {{date('d/m/Y H:i:s', strtotime($logLog['d_date_register']))}}</p>
@if(isset($logLog['log_log_phase']) && isset($logLog['log_log_phase']['log_phase']))
<p><strong>Fase:</strong> {{$logLog['log_log_phase']['log_phase']['c_name']}}</p>
@endif
<p><strong>Mensaje:</strong> {{$logLog['c_message']}}</p>
<hr/>
<h3>Usuario responsable</h3>
<p><strong>Login:</strong> {{$logLog['log_account']['acc_account']['c_user']}}</p>
<p><strong>Apellidos y nombres:</strong> {{$logLog['log_account']['acc_account']['acc_account_user']['c_user_last_name']}} {{$logLog['log_account']['acc_account']['acc_account_user']['c_user_name']}}</p>
@if(isset($logLog['log_queue']) && isset($logLog['log_queue']['que_queue']))
<hr/>
<h3>Detalles de la cola</h3>
<p><strong>Fecha de inicio:</strong> {{$logLog['log_queue']['que_queue']['d_date_register']}}</p>
@if(isset($logLog['log_queue']['que_file']))
<p><strong>Ruta del archivo:</strong> {{$logLog['log_queue']['que_file']['c_name_path']}}</p>
@endif
@endif
@if(isset($logLog['log_invoice']) && isset($logLog['log_invoice']['doc_invoice']))
<hr/>
<h3>Detalles del documento</h3>
<p><strong>Serie:</strong> {{$logLog['log_invoice']['doc_invoice']['c_serie']}}</p>
<p><strong>Correlativo:</strong> {{$logLog['log_invoice']['doc_invoice']['c_correlative']}}</p>
<p><strong>Fecha de emisión:</strong> {{date('d/m/Y', strtotime($logLog['log_invoice']['doc_invoice']['d_issue_date']))}}</p>
@endif