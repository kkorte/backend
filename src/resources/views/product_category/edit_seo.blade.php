@extends('hideyo_backend::_layouts.default')

@section('main')
<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        @include('hideyo_backend::_partials.product-category-tabs', array('productCategoryEditSeo' => true))
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
        <ol class="breadcrumb">
            <li><a href="{{ URL::route('hideyo.dashboard.index') }}">Dashboard</a></li>
            <li><a href="{!! URL::route('hideyo.product-category.index') !!}">Product categories</a></li> 
            <li><a href="{!! URL::route('hideyo.product-category.edit', $productCategory->id) !!}">edit</a></li>
            <li class="active"><a href="{!! URL::route('hideyo.product-category.edit', $productCategory->id) !!}">{!! $productCategory->title !!}</a></li>
            <li class="active">seo</li>
            
        </ol>
        <h2>Productcategory <small>edit seo</small></h2>
        <hr/>
        {!! Notification::showAll() !!}
        <div class="row">
            <div class="col-md-12">
                {!! Form::model($productCategory, array('method' => 'put', 'route' => array('hideyo.product-category.update', $productCategory->id), 'files' => true, 'class' => 'form-horizontal', 'data-toggle' => 'validator')) !!}
               
                {!! Form::hidden('seo', 1) !!}
                {!! Form::hidden('parent_id', null, array('class' => 'parent_id form-control')) !!}
                
                @include('hideyo_backend::_fields.seo-fields')

                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-5">
                        {!! Form::submit('Save', array('class' => 'btn btn-default')) !!}
                        <a href="{!! URL::route('hideyo.product-category.index') !!}" class="btn btn-large">Cancel</a>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@stop