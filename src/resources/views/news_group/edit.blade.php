@extends('hideyo_backend::_layouts.default')

@section('main')

<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        @include('hideyo_backend::_partials.news-group-tabs', array('newsGroupEdit' => true))
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

        <ol class="breadcrumb">
            <li><a href="/"><i class="entypo-folder"></i>Dashboard</a></li>
            <li><a href="{!! URL::route('hideyo.news-group.index') !!}">NewsGroup</a></li>
            <li><a href="{!! URL::route('hideyo.news-group.edit', $newsGroup->id) !!}">edit</a></li>
            <li><a href="{!! URL::route('hideyo.news-group.edit', $newsGroup->id) !!}">{!! $newsGroup->title !!}</a></li>
            <li class="active">general</li>
        </ol>

        <h2>NewsGroup <small>edit</small></h2>
        <hr/>
        {!! Notification::showAll() !!}


        {!! Form::model($newsGroup, array('method' => 'put', 'route' => array('hideyo.news-group.update', $newsGroup->id), 'files' => true, 'class' => 'form-horizontal form-groups-bordered validate')) !!}
            <input type="hidden" name="_token" value="{!! Session::token() !!}">


            <div class="form-group">
                {!! Form::label('active', 'Active', array('class' => 'col-sm-3 control-label')) !!}
                <div class="col-sm-5">
                    {!! Form::select('active', array('0' => 'No', '1' => 'Yes'), null, array('class' => 'form-control')) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('title', 'Title', array('class' => 'col-sm-3 control-label')) !!}
                <div class="col-sm-5">
                    {!! Form::text('title', null, array('class' => 'form-control', 'data-validate' => 'required', 'data-message-required' => 'This is custom message for required field.')) !!}
                </div>
            </div>

            @include('hideyo_backend::_fields.buttons', array('cancelRoute' => 'hideyo.news-group.index'))           
    </div>
</div>
@stop
