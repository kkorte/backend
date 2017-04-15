@extends('hideyo_backend::_layouts.default')

@section('main')

<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        @include('hideyo_backend::_partials.product-tabs', array('productEditSeo' => true))
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

        <ol class="breadcrumb">
            <li><a href="/admin"><i class="entypo-folder"></i>Dashboard</a></li>
            <li><a href="{!! URL::route('hideyo.product.index') !!}">Product</a></li>
            <li><a href="{!! URL::route('hideyo.product.edit', $product->id) !!}">edit</a></li>
            <li><a href="{!! URL::route('hideyo.product.edit', $product->id) !!}">{!! $product->title !!}</a></li>
            <li class="active">seo</li>
        </ol>

        <h2>Product <small>edit seo</small></h2>
        <hr/>
        {!! Notification::showAll() !!}

        {!! Form::model($product, array('method' => 'put', 'route' => array('hideyo.product.update', $product->id), 'files' => true, 'class' => 'form-horizontal', 'data-toggle' => 'validator')) !!}
        <input type="hidden" name="_token" value="{!! Session::token() !!}">     
        {!! Form::hidden('seo', 1) !!}                      
            
        @include('hideyo_backend::_fields.seo-fields')

        @include('hideyo_backend::_fields.buttons', array('cancelRoute' => 'hideyo.product.index'))

        {!! Form::close() !!}

    </div>

</div>


@stop
