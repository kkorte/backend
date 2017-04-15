@extends('hideyo_backend::_layouts.default')

@section('main')
<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        <ul class="nav nav-sidebar">
            <li><a href="{{ URL::route('hideyo.shop.index') }}">Overview <span class="sr-only">(current)</span></a></li>
            <li class="active"><a href="#">Edit</a></li>
        </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
        <ol class="breadcrumb">
            <li><a href="{{ URL::route('hideyo.dashboard.index') }}">Dashboard</a></li>
            <li><a href="{{ URL::route('hideyo.shop.index') }}">Shops</a></li>  
            <li class="active">edit</li>
        </ol>

        <h2>Shop <small>edit</small></h2>
        <hr/>
        {!! Notification::showAll() !!}

    {!! Form::model($shop, array('method' => 'put', 'route' => array('hideyo.shop.update', $shop->id), 'files' => true, 'class' => 'form-horizontal form-groups-bordered validate')) !!}
		<input type="hidden" name="_token" value="{!! Session::token() !!}">
        <div class="form-group">
            {!! Form::label('title', 'Title', array('class' => 'col-sm-3 control-label')) !!}

            <div class="col-sm-5">
                {!! Form::text('title', null, array('class' => 'form-control', 'data-validate' => 'required', 'data-message-required' => 'This is custom message for required field.')) !!}
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('url', 'Url', array('class' => 'col-sm-3 control-label')) !!}

            <div class="col-sm-5">
                {!! Form::text('url', null, array('class' => 'form-control', 'data-validate' => 'required', 'data-message-required' => 'This is custom message for required field.')) !!}
            </div>
        </div>

        <div class="form-group">   
            {!! Form::label('email', 'Email', array('class' => 'col-sm-3 control-label')) !!}

            <div class="col-sm-5">
                {!! Form::email('email', null, array('class' => 'form-control', 'data-validate' => 'required')) !!}
            </div>
        </div>
    

        <div class="form-group">
            {!! Form::label('description', 'Description', array('class' => 'col-sm-3 control-label')) !!}

            <div class="col-sm-5">
                {!! Form::textarea('description', null, array('class' => 'form-control', 'data-validate' => 'required', 'data-message-required' => 'This is custom message for required field.')) !!}
            </div>
        </div>

	    <div class="form-group">
	        {!! Form::label('active', 'Active', array('class' => 'col-sm-3 control-label')) !!}
	        <div class="col-sm-5">
	       {!! Form::select('active', array(0 => 'No', 1 => 'Yes'), null, array('class' => 'form-control')) !!}
	        </div>
	    </div>

        <div class="form-group">
            {!! Form::label('currency_code', 'Currency', array('class' => 'col-sm-3 control-label')) !!}
            <div class="col-sm-5">
           {!! Form::select('currency_code', array('EUR' => 'EURO', 'USD' => 'US Dollar', 'GBP' => 'British Pound'), null, array('class' => 'form-control')) !!}
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('square_thumbnail_sizes', 'Square thumbnail sizes', array('class' => 'col-sm-3 control-label')) !!}

            <div class="col-sm-5">
                {!! Form::text('square_thumbnail_sizes', null, array('class' => 'form-control', 'data-validate' => 'required', 'data-message-required' => 'This is custom message for required field.')) !!}
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('widescreen_thumbnail_sizes', 'Widescreen thumbnail sizes', array('class' => 'col-sm-3 control-label')) !!}

            <div class="col-sm-5">
                {!! Form::text('widescreen_thumbnail_sizes', null, array('class' => 'form-control', 'data-validate' => 'required', 'data-message-required' => 'This is custom message for required field.')) !!}
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('email', 'Email', array('class' => 'col-sm-3 control-label')) !!}

            <div class="col-sm-5">
                {!! Form::text('email', null, array('class' => 'form-control', 'data-validate' => 'required,minlength[4],maxlength[60]', 'data-message-required' => 'This field is required')) !!}
            </div>
        </div>

        @include('hideyo_backend::_fields.seo-fields')

        <div class="form-group">
            {!! Form::label('logo', 'Logo', array('class' => 'col-sm-3 control-label')) !!}

            <div class="col-sm-5">
                {!! Form::file('logo', null, array('class' => 'form-control', 'data-message-required' => 'This field is required.')) !!}
            </div>
        </div>

        @include('hideyo_backend::_fields.buttons', array('cancelRoute' => 'hideyo.shop.index'))


    {!! Form::close() !!}

	</div>
</div>
@stop