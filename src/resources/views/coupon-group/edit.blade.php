@extends('hideyo_backend::_layouts.default')

@section('main')

<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        @include('hideyo_backend::_partials.coupon-group-tabs', array('couponGroupEdit' => true))
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

        <ol class="breadcrumb">
            <li><a href="/"><i class="entypo-folder"></i>Dashboard</a></li>
            <li><a href="{!! URL::route('hideyo.coupon-group.index') !!}">couponGroup</a></li>
            <li><a href="{!! URL::route('hideyo.coupon-group.edit', $couponGroup->id) !!}">edit</a></li>
            <li><a href="{!! URL::route('hideyo.coupon-group.edit', $couponGroup->id) !!}">{!! $couponGroup->title !!}</a></li>
            <li class="active">general</li>
        </ol>

        <h2>couponGroup <small>edit</small></h2>
        <hr/>
        {!! Notification::showAll() !!}


        {!! Form::model($couponGroup, array('method' => 'put', 'route' => array('hideyo.coupon-group.update', $couponGroup->id), 'files' => true, 'class' => 'form-horizontal form-groups-bordered validate')) !!}
            <input type="hidden" name="_token" value="{!! Session::token() !!}">

            <div class="form-group">
                {!! Form::label('title', 'Title', array('class' => 'col-sm-3 control-label')) !!}
                <div class="col-sm-5">
                    {!! Form::text('title', null, array('class' => 'form-control', 'data-validate' => 'required', 'data-message-required' => 'This is custom message for required field.')) !!}
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-5">
                    {!! Form::submit('Save', array('class' => 'btn btn-default')) !!}
                    <a href="{!! URL::route('hideyo.coupon-group.index') !!}" class="btn btn-large">Cancel</a>
                </div>
            </div>            
    </div>
</div>
@stop
