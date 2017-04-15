@extends('hideyo_backend::_layouts.default')

@section('main')
<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        <ul class="nav nav-sidebar">
            <li><a href="{{ URL::route('hideyo.product-tag-group.index') }}">Overview <span class="sr-only">(current)</span></a></li>
            <li class="active"><a href="{{ URL::route('hideyo.product-tag-group.create') }}">Edit</a></li>
        </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
        <ol class="breadcrumb">
            <li><a href="{{ URL::route('hideyo.dashboard.index') }}">Dashboard</a></li>
            <li><a href="{{ URL::route('hideyo.product-tag-group.index') }}">Product tag group</a></li>  
            <li class="active">edit</li>
        </ol>

        <h2>Product tag group<small>edit</small></h2>
        <hr/>
        {!! Notification::showAll() !!}

        {!! Form::model($productTagGroup, array('method' => 'put', 'route' => array('hideyo.product-tag-group.update', $productTagGroup->id), 'files' => true, 'class' => 'form-horizontal form-groups-bordered validate')) !!}

            <div class="form-group">
                {!! Form::label('active', 'Active', array('class' => 'col-sm-3 control-label')) !!}
                <div class="col-sm-5">
                    {!! Form::select('active', array('0' => 'No', '1' => 'Yes'), null, array('class' => 'form-control')) !!}
                </div>
            </div>

            <div class="form-group">   
                {!! Form::label('tag', 'Tag', array('class' => 'col-sm-3 control-label')) !!}

                <div class="col-sm-5">
                    {!! Form::text('tag', null, array('class' => 'form-control', 'data-validate' => 'required')) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('products', 'Products', array('class' => 'col-sm-3 control-label')) !!}
                <div class="col-sm-5">
                    {!! Form::multiselect2('products[]', $products->toArray(), $productTagGroup->relatedProducts->pluck('id')->toArray()) !!}
                </div>
            </div>

                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-5">
                        {!! Form::submit('Save', array('class' => 'btn btn-default')) !!}
                        <a href="{!! URL::route('hideyo.product-tag-group.index') !!}" class="btn btn-large">Cancel</a>
                    </div>
                </div>


            {!! Form::close() !!}
    </div>
</div>
@stop


