@extends('hideyo_backend::_layouts.default')

@section('main')

<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        <ul class="nav nav-sidebar">
            <li class="active"><a href="{{ URL::route('hideyo.dashboard.index') }}">Dashboard <span class="sr-only">(current)</span></a></li>
            <li><a href="/admin/dashboard/stats"><i class="entypo-folder"></i>Stats</a></li>

        </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
      <ol class="breadcrumb">
          <li><a href=""><i class="entypo-folder"></i>Dashboard</a></li>
      </ol>
      {!! Notification::showAll() !!}

        @if($revenueThisMonth)
        <div class="row">
            <div class="col-md-12">
                <p>Revenue this month: <strong>&euro; {!! number_format($revenueThisMonth[0]->price_with_tax, 2, '.', '') !!}</strong></p>
            </div>
        </div>
        <hr/>
        @endif

        <table id="datatable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th class="col-md-1">{{{ trans('hideyo::table.id') }}}</th>
                    <th class="col-md-1">{{{ trans('hideyo::table.created-at') }}}</th>
                    <th class="col-md-2">{{{ trans('hideyo::table.client') }}}</th>
                    <th class="col-md-2">{{{ trans('hideyo::table.status') }}}</th>
                    <th class="col-md-1">{{{ trans('hideyo::table.payment-method') }}}</th>
                    <th class="col-md-2">{{{ trans('hideyo::table.sending-method') }}}</th>
                    <th class="col-md-1">{{{ trans('hideyo::table.price') }}}</th>
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
                "ajax": "/admin/order",

                columns: [

                {data: 'generated_custom_order_id', name: 'generated_custom_order_id'},
                {data: 'created_at', name: 'created_at', bSearchable: false},
                {data: 'client', name: 'client'},
                {data: 'status', name: 'status', bVisible: true, bSearchable: false}, 
                {data: 'paymentMethod', name: 'paymentMethod', bSearchable: false},
                {data: 'sendingMethod', name: 'sendingMethod', bSearchable: false},
                {data: 'price_with_tax', name: 'price_with_tax', bSearchable: false},                        
                {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                aaSorting: [[1, 'desc']]

            });
        });
        </script>
    </div>
</div>
@stop