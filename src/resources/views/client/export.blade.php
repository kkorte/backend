@extends('hideyo_backend::_layouts.default')

@section('main')

<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        <ul class="nav nav-sidebar">
            <li><a href="{{ URL::route('hideyo.client.index') }}">Overview <span class="sr-only">(current)</span></a></li>
            <li><a href="{{ URL::route('hideyo.client.create') }}">Create</a></li>
            <li class="active"><a href="{{ URL::route('hideyo.client.export') }}">Export</a></li>
        </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
        <ol class="breadcrumb">
            <li><a href="{{ URL::route('hideyo.dashboard.index') }}">Dashboard</a></li>
            <li><a href="{{ URL::route('hideyo.client.index') }}">Clients</a></li>  
            <li class="active">export</li>
        </ol>

    
        <h2>Clients <small>export</small></h2>
        <hr/>
        {!! Notification::showAll() !!}

        
        {!! Form::open(array('route' => array('hideyo.client.export'), 'files' => true, 'class' => 'form-horizontal form-groups-bordered validate')) !!}
        <input type="hidden" name="_token" value="{!! Session::token() !!}">
        
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-5">
                {!! Form::submit('Export xls', array('class' => 'btn btn-default')) !!}
                <a href="{!! URL::route('hideyo.client.index') !!}" class="btn btn-large">Cancel</a>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
</div>   
@stop