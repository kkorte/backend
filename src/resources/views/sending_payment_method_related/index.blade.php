@extends('hideyo_backend::_layouts.default')

@section('main')

<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        <ul class="nav nav-sidebar">
            <li class="active"><a href="{{ URL::route('hideyo.sending-payment-method-related.index') }}">Overview <span class="sr-only">(current)</span></a></li>
        </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
        <ol class="breadcrumb">
            <li><a href="{{ URL::route('hideyo.dashboard.index') }}">Dashboard</a></li>
            <li><a href="{{ URL::route('hideyo.sending-payment-method-related.index') }}">Order templates</a></li>  
            <li class="active">overview</li>
        </ol>

        <h2>Order templates <small>overview</small></h2>
        <hr/>
        {!! Notification::showAll() !!}

        <table id="datatable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th class="col-md-3">{{{ trans('hideyo::table.payment-method') }}}</th>
                    <th class="col-md-3">{{{ trans('hideyo::table.sending-method') }}}</th>
                    <th class="col-md-3">{{{ trans('hideyo::table.pdf-text') }}}</th>
                    <th class="col-md-3">{{{ trans('hideyo::table.payment-text') }}}</th>
                    <th class="col-md-3">{{{ trans('hideyo::table.payment-confirmed-text') }}}</th>
                    <th class="col-md-3">{{{ trans('hideyo::table.actions') }}}</th>
                </tr>
            </thead>
        </table>

        <script type="text/javascript">
            $(document).ready(function() {

                oTable = $('#datatable').DataTable({
                    "processing": true,
                    "serverSide": true,
                 "ajax": "{{ URL::route('hideyo.sending-payment-method-related.index') }}",

                 columns: [          
                        {data: 'payment_method', name: 'payment_method', orderable: false, searchable: false},
                        {data: 'sending_method', name: 'sending_method', orderable: false, searchable: false},
                        {data: 'pdf_text', name: 'pdf_text'},
                        {data: 'payment_text', name: 'payment_text'},
                        {data: 'payment_confirmed_text', name: 'payment_confirmed_text'},
                        {data: 'action', name: 'action', orderable: false, searchable: false}
                    ]
                });
            });
        </script>
     
    </div>
</div>   
@stop