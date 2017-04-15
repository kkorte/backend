@extends('hideyo_backend::_layouts.default')

@section('main')

<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        <ul class="nav nav-sidebar">
            <li class="active"><a href="{{ URL::route('hideyo.product-category.index') }}">Overview <span class="sr-only">(current)</span></a></li>
            <li><a href="{{ URL::route('hideyo.product-category.tree') }}">Tree</a></li>
        </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
        <ol class="breadcrumb">
            <li><a href="{{ URL::route('hideyo.dashboard.index') }}">Dashboard</a></li>
            <li><a href="{{ URL::route('hideyo.product-category.index') }}">Product categories</a></li>  
            <li class="active">overview</li>
        </ol>

        <a href="{{ URL::route('hideyo.product-category.create') }}" class="btn btn-success pull-right" aria-label="Left Align"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Create</a>

        <h2>Product categories <small>overview</small></h2>
        <hr/>
        {!! Notification::showAll() !!}

        <table id="datatable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th class="col-md-1">{{{ trans('hideyo::table.active') }}}</th>
                    <th class="col-md-1">{{{ trans('hideyo::table.image') }}}</th>
                    <th class="col-md-1">{{{ trans('hideyo::table.products') }}}</th>
                    <th class="col-md-3">{{{ trans('hideyo::table.parent') }}}</th>                    
                    <th class="col-md-3">{{{ trans('hideyo::table.redirect') }}}</th>
                    <th class="col-md-3">{{{ trans('hideyo::table.title') }}}</th>
                    <th class="col-md-2">{{{ trans('hideyo::table.actions') }}}</th>
                </tr>
            </thead>
        </table>

        <script type="text/javascript">
        $(document).ready(function() {

            oTable = $('#datatable').DataTable({
                "processing": true,
                "serverSide": true,
                "stateSave": true,


                "ajax": "{{ URL::route('hideyo.product-category.index') }}",

                columns: [
                {data: 'active', name: 'active'},
                {data: 'image', name: 'image', orderable: false, bVisible: true, bSearchable: false},
                {data: 'products', name: 'products', orderable: false, bVisible: true, bSearchable: false},
                {data: 'parent', orderable: false, name: 'parent', bVisible: true, bSearchable: false},
                {data: 'redirect_product_category_id', name: 'redirect_product_category_id', bSearchable: false},
                
                {data: 'title', name: 'title'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
                ]

            });
        });
        </script>

    </div>
</div>   
@stop

