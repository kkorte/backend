@extends('hideyo_backend::_layouts.default')

@section('main')

<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        <ul class="nav nav-sidebar">
            <li class="active"><a href="{{ URL::route('hideyo.brand.index') }}">Overview <span class="sr-only">(current)</span></a></li>
            <li><a href="{{ URL::route('hideyo.brand.create') }}">Create</a></li>
        </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
        <ol class="breadcrumb">
            <li><a href="{{ URL::route('hideyo.dashboard.index') }}">Dashboard</a></li>
            <li><a href="{{ URL::route('hideyo.brand.index') }}">Brands</a></li>  
            <li class="active">overview</li>
        </ol>

        <a href="{{ URL::route('hideyo.brand.create') }}" class="btn btn-success pull-right" aria-label="Left Align"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Create</a>

        <h2>Brands <small>overview</small></h2>
        <hr/>
        {!! Notification::showAll() !!}

        <table id="datatable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th class="col-md-3">{{{ trans('hideyo::table.id') }}}</th>
                    <th class="col-md-3">{{{ trans('hideyo::table.rank') }}}</th>
                    <th class="col-md-3">{{{ trans('hideyo::table.title') }}}</th>
                    <th class="col-md-3">{{{ trans('hideyo::table.actions') }}}</th>
                </tr>
            </thead>
        </table>

        <script type="text/javascript">
            $(document).ready(function() {

                oTable = $('#datatable').DataTable({
                    "processing": true,
                    "serverSide": true,
                   "ajax": "{{ URL::route('hideyo.brand.index') }}",

                 columns: [
                        {data: 'id', name: 'id'},
                        {data: 'rank', name: 'rank'},
                        {data: 'title', name: 'title'},
                        {data: 'action', name: 'action', orderable: false, searchable: false}
                    ]

                });
            });
        </script>
     
    </div>
</div>   
@stop
