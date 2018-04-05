@extends('layouts.admin')

@section('content')

    <div class="row">

        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Adjuntar archivo plano .TXT</h4>
                        
                        <form method="POST" action="{{ url('process-txt') }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">

                            <div class="form-group {{ $errors->has('text_plain') ? 'has-error' : ''}}">
                                <label for="text_plain" class="col-md-4 control-label"></label>
                                <div class="col-md-6">
                                    <input class="form-control" name="text_plain" type="file" id="text_plain" >
                                    {!! $errors->first('text_plain', '<p class="help-block">:message</p>') !!}
                                </div>
                            </div>

                            {{ csrf_field() }}

                            <div class="form-group">
                                <div class="col-md-offset-4 col-md-4">
                                    <input class="btn btn-primary" type="submit" value="Procesar Archivo">
                                </div>
                            </div>                            

                        </form>
                        
                        @if (Session::has('message_error'))
                        <div class="alert alert-danger alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <div>
                                Error: {{Session::get('message_error')}}
                            </div>
                        </div>
                        @endif
                        
                        @if (Session::has('message_success'))
                        <div class="alert alert-success alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <div>{{Session::get('message_success')}}</div>
                        </div>
                        @endif


                </div>
            </div>
        </div>
    </div>

@endsection
