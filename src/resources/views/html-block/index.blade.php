@extends('hideyo_backend::_layouts.default')

@section('main')

<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        <ul class="nav nav-sidebar">
            <li class="active"><a href="{{ URL::route('hideyo.html-block.index') }}">Overview <span class="sr-only">(current)</span></a></li>
            <li><a href="{{ URL::route('hideyo.html-block.create') }}">Create</a></li>
        </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
        <ol class="breadcrumb">
            <li><a href="{{ URL::route('hideyo.dashboard.index') }}">Dashboard</a></li>
            <li><a href="{{ URL::route('hideyo.html-block.index') }}">Html Block</a></li>  
            <li class="active">overview</li>
        </ol>

        <a href="{{ URL::route('hideyo.html-block.create') }}" class="btn btn-success pull-right" aria-label="Left Align"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Create</a>

        <h2>Html Block <small>overview</small></h2>
        <hr/>
        {!! Notification::showAll() !!}

        <table id="datatable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th class="col-md-1">{{{ trans('hideyo::table.active') }}}</th>
                    <th class="col-md-3">{{{ trans('hideyo::table.image') }}}</th>
                    <th class="col-md-3">{{{ trans('hideyo::table.title') }}}</th>
                    <th class="col-md-3">{{{ trans('hideyo::table.position') }}}</th>
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
               "ajax": "{{ URL::route('hideyo.html-block.index') }}",

                 columns: [
                        {data: 'active', name: 'active'},
                       {data: 'image', name: 'image', bVisible: true, bSearchable: false, bSortable: false},
                        {data: 'title', name: 'title'},
                        {data: 'position', name: 'position'},
                        {data: 'action', name: 'action', orderable: false, searchable: false}
                    ]

                });
            });
        </script>
     
    </div>
</div>   
@stop