@extends('hideyo_backend::_layouts.default')

@section('main')

<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        <ul class="nav nav-sidebar">
            <li><a href="{!! URL::route('hideyo.attribute-group.index') !!}">Overview <span class="sr-only">(current)</span></a></li>
            <li><a href="{!! URL::route('hideyo.attribute-group.edit', $attributeGroup->id) !!}">Edit</a></li>
            <li class="active"><a href="{!! URL::route('hideyo.attribute.index', $attributeGroup->id) !!}">Attributes</a></li>

        </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
        <ol class="breadcrumb">
            <li><a href="{{ URL::route('hideyo.dashboard.index') }}">Dashboard</a></li>
            <li><a href="{!! URL::route('hideyo.attribute-group.index') !!}">Attribute groups</a></li>  
            <li><a href="{!! URL::route('hideyo.attribute-group.edit', $attributeGroup->id) !!}">edit</a></li>
            <li class="active"><a href="{!! URL::route('hideyo.attribute.index', $attributeGroup->id) !!}">{!! $attributeGroup->title !!}</a></li>
            <li class="active">attributes</li>  
        </ol>

        <a href="{{ URL::route('hideyo.attribute.create', $attributeGroup->id) }}" class="btn btn-success pull-right" aria-label="Left Align"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Create</a>

        <h2>Attributes <small>overview</small></h2>
        <hr/>
        {!! Notification::showAll() !!}

        <table id="datatable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th class="col-md-3">{{{ trans('hideyo::table.id') }}}</th>
                    <th class="col-md-3">{{{ trans('hideyo::table.value') }}}</th>
                    <th class="col-md-3">{{{ trans('hideyo::table.actions') }}}</th>
                </tr>
            </thead>
        </table>

        <script type="text/javascript">
            $(document).ready(function() {

                oTable = $('#datatable').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "ajax": "{{ URL::route('hideyo.attribute.index', $attributeGroup->id) }}",


                 columns: [
                        {data: 'id', name: 'id'},
                        {data: 'value', name: 'value'},
                        {data: 'action', name: 'action', orderable: false, searchable: false}
                    ]

                });
            });
        </script>
     
    </div>
</div>   
@stop