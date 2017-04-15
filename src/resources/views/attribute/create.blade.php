@extends('hideyo_backend::_layouts.default')

@section('main')
<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        <ul class="nav nav-sidebar">
            <li><a href="{!! URL::route('hideyo.attribute-group.index') !!}">Overview <span class="sr-only">(current)</span></a></li>
            <li><a href="{!! URL::route('hideyo.attribute-group.edit', $attributeGroup->id) !!}">Edit</a></li>
            <li class="active"><a href="{!! URL::route('hideyo.attribute.index', $attributeGroup->id) !!}">Values</a></li>

        </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
        <ol class="breadcrumb">
            <li><a href="{{ URL::route('hideyo.dashboard.index') }}">Dashboard</a></li>
            <li><a href="{{ URL::route('hideyo.attribute-group.index') }}">Extra field </a></li>  
            <li class="active">create</li>
        </ol>

        <h2>Attribute <small>create</small></h2>
        <hr/>
        {!! Notification::showAll() !!}

        {!! Form::open(array('route' => array('hideyo.attribute.store', $attributeGroup->id), 'files' => true, 'class' => 'form-horizontal form-groups-bordered validate')) !!}
 
            <div class="form-group">   
                {!! Form::label('value', 'Value', array('class' => 'col-sm-3 control-label')) !!}

                <div class="col-sm-5">
                    {!! Form::text('value', null, array('class' => 'form-control', 'data-validate' => 'required', 'data-message-required' => 'This is custom message for required field.', 'placeholder' => 'type a value')) !!}
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-5">
                    {!! Form::submit('Save', array('class' => 'btn btn-default')) !!}
                    <a href="{!! URL::route('hideyo.attribute.index', $attributeGroup->id) !!}" class="btn btn-large">Cancel</a>
                </div>
            </div>

        {!! Form::close() !!}
    </div>
</div>
@stop
