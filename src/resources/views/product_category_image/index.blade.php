@extends('hideyo_backend::_layouts.default')

@section('main')

<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        @include('hideyo_backend::_partials.product-category-tabs', array('productCategoryImages' => true))
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
        <ol class="breadcrumb">
            <li><a href="{{ URL::route('hideyo.dashboard.index') }}">Dashboard</a></li>
            <li><a href="{!! URL::route('hideyo.product-category.index') !!}">Product categories</a></li>  
            <li><a href="{!! URL::route('hideyo.product-category.edit', $productCategory->id) !!}">edit</a></li>
            <li class="active"><a href="{!! URL::route('hideyo.product-category.edit', $productCategory->id) !!}">{!! $productCategory->title !!}</a></li>
            <li class="active">images</li>           
        </ol>

        <a href="{{ URL::route('hideyo.product-category-images.create', $productCategory->id) }}" class="btn btn-success pull-right">upload image<i class="entypo-plus"></i></a>

        <h2>Productcategory <small>images</small></h2>
        <hr/>
        {!! Notification::showAll() !!}
			
        <table id="datatable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th class="col-md-3">{{{ trans('hideyo::table.id') }}}</th>
                    <th class="col-md-3">{{{ trans('hideyo::table.file') }}}</th>
                    <th class="col-md-3">{{{ trans('hideyo::table.actions') }}}</th>
                </tr>
            </thead>
        </table>

        <script type="text/javascript">
            $(document).ready(function() {

                oTable = $('#datatable').DataTable({
                    "processing": true,
                    "serverSide": true,
                   "ajax": "{{ URL::route('hideyo.product-category-images.index', $productCategory->id) }}",

                 columns: [
              {data: 'thumb', name: 'thumb', orderable: false, searchable: false},
                        {data: 'file', name: 'file'},
                        {data: 'action', name: 'action', orderable: false, searchable: false}
                    ]

                });
            });
        </script>
    </div>
</div>
@stop