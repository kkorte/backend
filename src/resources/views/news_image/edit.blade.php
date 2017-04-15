@extends('hideyo_backend::_layouts.default')

@section('main')

<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        @include('hideyo_backend::_partials.news-tabs', array('newsImages' => true))
    </div>

    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
        <ol class="breadcrumb">
          <li><a href="/"><i class="entypo-folder"></i>Dashboard</a></li>
          <li><a href="{!! URL::route('hideyo.news.index') !!}">News</a></li>
            <li><a href="{!! URL::route('hideyo.news.edit', $news->id) !!}">edit</a></li>
            <li><a href="{!! URL::route('hideyo.news.edit', $news->id) !!}">{!! $news->title !!}</a></li>
            <li><a href="{!! URL::route('hideyo.news.{newsId}.images.index', $news->id) !!}">images</a></li>
          <li class="active">edit image</li> 
        </ol>

        <a href="{!! URL::route('hideyo.news.{newsId}.images.index', $news->id) !!}" class="btn btn-green btn-icon icon-left pull-right">back to images<i class="entypo-plus"></i></a>

        <h2>News <small>images edit</small></h2>
        {!! Notification::showAll() !!}
        <hr/>
        {!! Form::model($newsImage, array('method' => 'put', 'route' => array('hideyo.news.{newsId}.images.update', $news->id, $newsImage->id), 'files' => true, 'class' => 'form-horizontal form-groups-bordered validate')) !!}
        <input type="hidden" name="_token" value="{!! Session::token() !!}">


        <div class="form-group">
            {!! Form::label('tag', 'Tag', array('class' => 'col-sm-3 control-label')) !!}
            <div class="col-sm-5">
                {!! Form::select('tag', array('square' => 'square', 'widescreen' => 'widescreen'), null,  array('class' => 'form-control', 'data-validate' => 'required', 'data-message-required' => 'This is custom message for required field.')) !!}
            </div>
        </div>


        <div class="form-group">
            {!! Form::label('rank', 'Rank', array('class' => 'col-sm-3 control-label')) !!}
            <div class="col-sm-5">
                {!! Form::text('rank', null, array('class' => 'form-control', 'data-validate' => 'required,number', 'data-message-required' => 'This is custom message for required field.')) !!}
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-5">
                {!! Form::submit('Save', array('class' => 'btn btn-default')) !!}
                <a href="{!! URL::route('hideyo.news.{newsId}.images.store', $news->id) !!}" class="btn btn-large">Cancel</a>
            </div>
        </div>

        {!! Form::close() !!}
	</div>
</div>      
@stop