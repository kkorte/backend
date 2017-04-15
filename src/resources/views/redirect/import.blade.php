@extends('hideyo_backend::_layouts.default')

@section('main')

<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        <ul class="nav nav-sidebar">
            <li><a href="{{ URL::route('hideyo.redirect.index') }}">Overview <span class="sr-only">(current)</span></a></li>
            <li class="active"><a href="{{ URL::route('hideyo.redirect.import') }}">Create</a></li>
        </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

        <ol class="breadcrumb">
            <li><a href="/"><i class="entypo-folder"></i>Dashboard</a></li>
            <li><a href="{!! URL::route('hideyo.redirect.index') !!}">redirect</a></li>  
            <li class="active">import</li>
        </ol>

        <a href="{!! URL::route('hideyo.redirect.index') !!}" class="btn btn-danger pull-right">Back to overview<i class="entypo-back"></i></a>

        <h2>Redirect <small>import</small></h2>
        <br/>
        {!! Notification::showAll() !!}

        {!! Form::open(array('route' => array('hideyo.redirect.import'), 'files' => true, 'class' => 'form-horizontal  validate')) !!}
        

	        <div class="form-group">
	            {!! Form::label('file', 'File', array('class' => 'col-sm-3 control-label')) !!}

	            <div class="col-sm-5">
	                {!! Form::file('file', array(), array('class' => 'form-control', 'data-message-required' => 'This field is required.')) !!}
	            </div>
	        </div>


            
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-5">
                    {!! Form::submit('Save', array('class' => 'btn btn-default')) !!}
                    <a href="{!! URL::route('hideyo.redirect.index') !!}" class="btn btn-large">Cancel</a>
                </div>
            </div>

        {!! Form::close() !!}
    </div>
</div>
@stop