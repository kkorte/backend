@extends('hideyo_backend::_layouts.default')

@section('main')

<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        <ul class="nav nav-sidebar">
            <li><a href="{{ URL::route('hideyo.product.index') }}">Overview <span class="sr-only">(current)</span></a></li>
            <li><a href="{{ URL::route('hideyo.product.create') }}">Create</a></li>
            <li class="active"><a href="{{ URL::route('hideyo.product.export') }}">Export</a></li>
            <li><a href="{{ URL::route('hideyo.product.rank') }}">Ranking</a></li>
        </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
        <ol class="breadcrumb">
            <li><a href="{{ URL::route('hideyo.dashboard.index') }}">Dashboard</a></li>
            <li><a href="{{ URL::route('hideyo.product.index') }}">Products</a></li>  
            <li class="active">export</li>
        </ol>

    
        <h2>Products <small>export</small></h2>
        <hr/>
        {!! Notification::showAll() !!}

        
        {!! Form::open(array('route' => array('hideyo.product.export'), 'files' => true, 'class' => 'form-horizontal form-groups-bordered validate')) !!}
        <input type="hidden" name="_token" value="{!! Session::token() !!}">
        
        @include('hideyo_backend::_fields.buttons', array('cancelRoute' => 'hideyo.product.index'))
        
        {!! Form::close() !!}
    </div>
</div>   
@stop