@extends('hideyo_backend::_layouts.default')

@section('main')

<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        @include('hideyo_backend::_partials.product-tabs', array('productEditPrice' => true))
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

        <ol class="breadcrumb">
            <li><a href="/admin"><i class="entypo-folder"></i>Dashboard</a></li>
            <li><a href="{!! URL::route('hideyo.product.index') !!}">Product</a></li>
            <li><a href="{!! URL::route('hideyo.product.edit', $product->id) !!}">edit</a></li>
            <li><a href="{!! URL::route('hideyo.product.edit', $product->id) !!}">{!! $product->title !!}</a></li>
            <li class="active">price</li>
        </ol>

        <h2>Product <small>edit price</small></h2>
        <hr/>
        {!! Notification::showAll() !!}

        {!! Form::model($product, array('method' => 'put', 'route' => array('hideyo.product.update', $product->id), 'files' => true, 'class' => 'form-horizontal form-groups-bordered validate')) !!}
        <input type="hidden" name="_token" value="{!! Session::token() !!}">     
        {!! Form::hidden('price', 1) !!}                      
            
            <div class="form-group">
                {!! Form::label('tax_rate_id', 'Tax rate', array('class' => 'col-sm-3 control-label')) !!}
                <div class="col-sm-5">
                    {!! Form::select('tax_rate_id', $taxRates, null, array('class' => 'tax-rate form-control')) !!}
                </div>
            </div>


            <div class="form-group">
                {!! Form::label('price', 'Price without tax', array('class' => 'col-sm-3 control-label')) !!}
                <div class="col-sm-5">
                    {!! Form::text('price', null, array('class' => 'price form-control', 'data-validate' => 'required', 'data-sign' => '&euro;')) !!}
                </div>
            </div>

            @if($this_user->shop->wholesale)
                <div class="form-group">
                    {!! Form::label('commercial_price', 'Commercial price', array('class' => 'col-sm-3 control-label')) !!}
                    <div class="col-sm-5">
                        {!! Form::text('commercial_price', null, array('class' => 'price form-control', 'data-validate' => 'required', 'data-sign' => '&euro;')) !!}
                    </div>
                </div>
            @endif


            <div class="form-group">
                {!! Form::label('price_inc_tax', 'Price with tax', array('class' => 'col-sm-3 control-label')) !!}
                <div class="col-sm-5">
                    {!! Form::text('price_inc_tax', null, array('class' => 'price_inc_tax form-control', 'data-sign' => '&euro;')) !!}
                </div>
            </div>

			<hr/>

            <div class="form-group">
                {!! Form::label('discount_type', 'Discount type', array('class' => 'col-sm-3 control-label')) !!}
                <div class="col-sm-5">
                    {!! Form::select('discount_type', array('amount' => 'Amount','percent' => 'Percent'), null, array('class' => 'tax-rate form-control')) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('discount_value', 'Discount value', array('class' => 'col-sm-3 control-label')) !!}
                <div class="col-sm-5">
                    {!! Form::text('discount_value', null, array('class' => 'form-control', 'data-validate' => 'number', 'data-sign' => '&euro;')) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('discount_start_date', 'Discount start date', array('class' => 'col-sm-3 control-label')) !!}
                <div class="col-sm-5">
                    {!! Form::text('discount_start_date', null, array('class' => 'datepicker form-control', 'data-sign' => '&euro;')) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('discount_end_date', 'Discount end date', array('class' => 'col-sm-3 control-label')) !!}
                <div class="col-sm-5">
                    {!! Form::text('discount_end_date', null, array('class' => 'datepicker form-control', 'data-sign' => '&euro;')) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('discount_promotion', 'Discount promotion', array('class' => 'col-sm-3 control-label')) !!}
                <div class="col-sm-5">
                    {!! Form::select('discount_promotion', array('0' => 'No', '1' => 'Yes'), null, array('class' => 'form-control')) !!}
                </div>
            </div>


            @include('hideyo_backend::_fields.buttons', array('cancelRoute' => 'hideyo.product.index'))

        {!! Form::close() !!}

    </div>

</div>


@stop
