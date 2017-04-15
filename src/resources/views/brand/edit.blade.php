@extends('hideyo_backend::_layouts.default')


@section('main')
<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        @include('hideyo_backend::_partials.brand-tabs', array('brandEdit' => true))
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
        <ol class="breadcrumb">
            <li><a href="{{ URL::route('hideyo.dashboard.index') }}">Dashboard</a></li>
            <li><a href="{{ URL::route('hideyo.brand.index') }}">Brands</a></li>  
            <li class="active">edit</li>
        </ol>

        <h2>Brands <small>edit</small></h2>
        <hr/>
        {!! Notification::showAll() !!}

        {!! Form::model($brand, array('method' => 'put', 'route' => array('hideyo.brand.update', $brand->id), 'files' => true, 'class' => 'form-horizontal form-groups-bordered validate')) !!}
            

            <div class="form-group">
                {!! Form::label('active', 'Active', array('class' => 'col-sm-3 control-label')) !!}
                <div class="col-sm-5">
                    {!! Form::select('active', array('0' => 'No', '1' => 'Yes'), null, array('class' => 'form-control')) !!}
                </div>
            </div>

            <div class="form-group">   
                {!! Form::label('title', 'Title', array('class' => 'col-sm-3 control-label')) !!}

                <div class="col-sm-5">
                    {!! Form::text('title', null, array('class' => 'form-control', 'data-validate' => 'required', 'data-message-required' => 'This is custom message for required field.', 'placeholder' => 'type a name')) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('short_description', 'Short Description', array('class' => 'col-sm-3 control-label')) !!}
                <div class="col-sm-5">
                    {!! Form::textarea('short_description', null, array('class' => 'form-control', 'data-validate' => 'required', 'data-message-required' => 'This is custom message for required field.')) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('description', 'Description', array('class' => 'col-sm-3 control-label')) !!}
                <div class="col-sm-5">
                    {!! Form::textarea('description', null, array('class' => 'form-control ckeditor', 'data-validate' => 'required', 'data-message-required' => 'This is custom message for required field.')) !!}
                </div>
            </div>            


            <div class="form-group">
                {!! Form::label('rank', 'Rank', array('class' => 'col-sm-3 control-label')) !!}
                <div class="col-sm-5">
                    {!! Form::text('rank', null, array('class' => 'form-control')) !!}
                </div>
            </div>

            @include('hideyo_backend::_fields.buttons', array('cancelRoute' => 'hideyo.brand.index'))
            
        {!! Form::close() !!}
    </div>
</div>
@stop






