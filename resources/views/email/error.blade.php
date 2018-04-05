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
						<p>Se encontró los siguientes errores en el documento: {{ $serie }}.</p>
						<ul>
						@foreach($items as $item)
							<li>{{ $item }}</li>
						@endforeach
						</ul>
					</td>
				</tr>
			</table>
			</div>
		</td>
		<td></td>
	</tr>
</table>
@stop