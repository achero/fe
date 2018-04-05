@extends('layout.default')
@section('content')
<table class="body-wrap">
	<tr>
		<td></td>
		<td class="container" bgcolor="#FFFFFF">
			<div class="content">
			<table>
				<tr>
					<td>
                        <h2>La cola que inició a las {{date('H:i d-m-Y', $dateStart)}} presenta los siguientes documentos con errores:</h2>
                        @foreach($documents as $k => $v)
                        <hr/>
                        <p><strong>{{$k+1}}.</strong> El documento {{$v['serie']}} presenta los siguientes errores:</p>
                        <ul>
                            @foreach($v['items'] as $value)
                            <li>{{$value}}</li>
                            @endforeach
                        </ul>
                        @endforeach
					</td>
				</tr>
			</table>
			</div>
		</td>
		<td></td>
	</tr>
</table>
@stop