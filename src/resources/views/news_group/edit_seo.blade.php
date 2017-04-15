@extends('hideyo_backend::_layouts.default')

@section('main')

<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        @include('hideyo_backend::_partials.news-group-tabs', array('newsGroupEditSeo' => true))
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

        <ol class="breadcrumb">
            <li><a href="/"><i class="entypo-folder"></i>Dashboard</a></li>
            <li><a href="{!! URL::route('hideyo.news-group.index') !!}">Newsgroup</a></li>
            <li><a href="{!! URL::route('hideyo.news-group.edit', $newsGroup->id) !!}">edit</a></li>
            <li><a href="{!! URL::route('hideyo.news-group.edit', $newsGroup->id) !!}">{!! $newsGroup->title !!}</a></li>
            <li class="active">seo</li>
        </ol>

        <h2>Newsgroup <small>edit seo</small></h2>
        <hr/>
        {!! Notification::showAll() !!}

        {!! Form::model($newsGroup, array('method' => 'put', 'route' => array('hideyo.news-group.update', $newsGroup->id), 'files' => true, 'class' => 'form-horizontal form-groups-bordered validate')) !!}
        <input type="hidden" name="_token" value="{!! Session::token() !!}">     
        {!! Form::hidden('seo', 1) !!}                      
            
        <div class="form-group">
            {!! Form::label('meta_title', 'Meta title', array('class' => 'col-sm-3 control-label')) !!}

            <div class="col-sm-5">
                {!! Form::text('meta_title', null, array('class' => 'form-control', 'data-validate' => 'required,minlength[4],maxlength[60]', 'data-message-required' => 'This field is required')) !!}
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('meta_description', 'Meta description', array('class' => 'col-sm-3 control-label')) !!}

            <div class="col-sm-5">
                {!! Form::text('meta_description', null, array('class' => 'form-control', 'data-validate' => 'required,minlength[4],maxlength[160]', 'data-message-required' => 'This field is required.')) !!}
            </div>
        </div> 

        <div class="form-group">
            {!! Form::label('meta_keywords', 'Meta keywords', array('class' => 'col-sm-3 control-label')) !!}

            <div class="col-sm-5">
                {!! Form::text('meta_keywords', null, array('class' => 'form-control', 'data-validate' => 'required', 'data-message-required' => 'This field is required.')) !!}
            </div>
        </div> 

        @include('hideyo_backend::_fields.buttons', array('cancelRoute' => 'hideyo.news-group.index'))

        {!! Form::close() !!}

    </div>

</div>


@stop
