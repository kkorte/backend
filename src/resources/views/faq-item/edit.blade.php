@extends('hideyo_backend::_layouts.default')

@section('main')

<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">

    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

        <ol class="breadcrumb">
            <li><a href="/"><i class="entypo-folder"></i>Dashboard</a></li>
            <li><a href="{!! URL::route('hideyo.faq.index') !!}">Faq item</a></li>
            <li><a href="{!! URL::route('hideyo.faq.edit', $faq->id) !!}">edit</a></li>
            <li><a href="{!! URL::route('hideyo.faq.edit', $faq->id) !!}">{!! $faq->title !!}</a></li>
            <li class="active">general</li>
        </ol>

        <h2>Faq <small>edit</small></h2>
        <hr/>
        {!! Notification::showAll() !!}


        {!! Form::model($faq, array('method' => 'put', 'route' => array('hideyo.faq.update', $faq->id), 'files' => true, 'class' => 'form-horizontal', 'data-toggle' => 'validator')) !!}
            <input type="hidden" name="_token" value="{!! Session::token() !!}">


            <div class="form-group">
                {!! Form::label('faq_item_group_id', 'Group', array('class' => 'col-sm-3 control-label')) !!}
                <div class="col-sm-5">
                    {!! Form::select('faq_item_group_id', $groups, null, array('class' => 'form-control')) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('question', 'Question', array('class' => 'col-sm-3 control-label')) !!}
                <div class="col-sm-5">
                    {!! Form::text('question', null, 
                    array(
                        'class' => 'form-control', 
                        'minlength' => 4, 
                        'maxlength' => 65, 
                        'data-error' => trans('validation.between.numeric', ['attribute' => 'question', 'min' => 4, 'max' => 65]), 
                        'required' => 'required'
                    )) !!}
                    <div class="help-block with-errors"></div>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('answer', 'Answer', array('class' => 'col-sm-3 control-label')) !!}
                <div class="col-sm-9">
                    {!! Form::textarea('answer', null, array('class' => 'form-control ckeditor')) !!}
                </div>
            </div>

            @include('hideyo_backend::_fields.buttons', array('cancelRoute' => 'hideyo.faq.index')) 
          
    </div>
</div>
@stop
