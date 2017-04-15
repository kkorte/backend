@extends('hideyo_backend::_layouts.default')

@section('main')

<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        <ul class="nav nav-sidebar">
            <li class="active"><a href="{{ URL::route('hideyo.redirect.index') }}">Overview <span class="sr-only">(current)</span></a></li>
            <li><a href="{{ URL::route('hideyo.redirect.create') }}">Create</a></li>
            <li><a href="{{ URL::route('hideyo.redirect.export') }}">Export</a></li>
            <li><a href="{{ URL::route('hideyo.redirect.import') }}">Import</a></li>
        </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

        <ol class="breadcrumb">
            <li><a href="{{ URL::route('hideyo.dashboard.index') }}">Dashboard</a></li>
            <li><a href="{{ URL::route('hideyo.redirect.index') }}">Redirect</a></li>  
            <li class="active">overview</li>
        </ol>
        <div class="btn-group pull-right">
            <a href="{{ URL::route('hideyo.redirect.create') }}" class="btn btn-default btn-success btn-icon icon-left">Create redirect<i class="entypo-plus"></i></a> 
            <a href="{{ URL::route('hideyo.redirect.export') }}" class="btn btn-default btn-info btn-icon icon-left">Export redirects<i class="entypo-plus"></i></a>
        </div>

        <h2>Redirect <small>overview</small></h2>
        <br/>
        {!! Notification::showAll() !!}

        <table id="datatable" class="table table-striped table-bordered">
            <thead>
                <tr>  
                    <th class="col-md-2">{{{ trans('hideyo::table.active') }}}</th> 
                    <th class="col-md-3">{{{ trans('hideyo::table.url') }}}</th>
                    <th class="col-md-3">{{{ trans('hideyo::table.redirect_url') }}}</th>
                    <th class="col-md-1">{{{ trans('hideyo::table.clicks') }}}</th>
                    <th class="col-md-3">{{{ trans('hideyo::table.actions') }}}</th>
                </tr>
            </thead>
        </table>

        <script type="text/javascript">
        $(document).ready(function() {

            oTable = $('#datatable').DataTable({
                "processing": true,
                "serverSide": true,
                "stateSave": true,
                 "ajax": "{{ URL::route('hideyo.redirect.index') }}",

                columns: [   
                {data: 'active', name: 'active'},              
                {data: 'url', name: 'url'},
                {data: 'redirect_url', name: 'redirect_url'},  
                {data: 'clicks', name: 'clicks'},               
                {data: 'action', name: 'action', orderable: false, searchable: false}
                ]

            });
        });
        </script>
    </div>
</div>

@stop
