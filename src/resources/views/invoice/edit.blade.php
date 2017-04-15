@extends('_layouts.default')

@section('main')

<ol class="breadcrumb">
    <li><a href="{{ URL::route('index') }}"><i class="entypo-folder"></i>Dashboard</a></li>
    <li><a href="{{ URL::route('order.index') }}">Order</a></li>  
    <li class="active">show</li>
</ol>

<a href="{{ URL::route('order.index') }}" class="btn btn-green btn-icon icon-left pull-right">Back to overview<i class="entypo-back"></i></a>

<h2>Order <small>edit</small></h2>
<hr/>
@include('_partials.notifications')

<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs bordered"><!-- available classes "right-aligned" -->
            
            <li class="active">
                <a href="#">
                    <span class="visible-xs"><i class="entypo-gauge"></i></span>
                    <span class="hidden-xs">Show</span>
                </a>
            </li> 

        </ul>
        
        <div class="panel panel-primary tab-content">

            <div class="panel-body">    

                <div class="col-md-6">
                
                    <div class="jumbotron">

                            <table class="table"> 
                                <tr>
                                    <td>Id</td>
                                    <td scope="row">{{ $order->id }}</td>
                                </tr>

                                <tr>
                                    <td>Client email</td>
                                    <td scope="row">{{ $order->client->email }}</td>
                                </tr>
                                <tr>
                                    <td>Created at</td>
                                    <td scope="row">{{ $order->created_at }}</td>
                                </tr> 
                                <tr>
                                    <td>Price with tax</td>
                                    <td scope="row">&euro; {{ $order->price_with_tax }}</td>
                                </tr>
                                <tr>
                                    <td>Price without tax</td>
                                    <td scope="row">&euro; {{ $order->price_without_tax }}</td>
                                </tr> 
                                <tr>
                                    <td>Tax</td>
                                    <td scope="row">&euro; {{  $order->taxTotal() }}<br/>
                                        @foreach ($order->taxDetails() as $key => $val)
                                        <small>{{ round($key) }}%: &euro; {{ round($val,2) }}</small><br/>
                                        @endforeach
                                    </td>
                                </tr> 
                            </table>

                     
                        
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h3>Delivery address</h3>
                            <table class="table"> 
                                <tr>
                                    <td>Gender</td>
                                    <td scope="row">{{ $order->orderDeliveryAddress->gender }}</td>
                                </tr> 

                                <tr>
                                    <td>Name</td>
                                    <td scope="row">{{ $order->orderDeliveryAddress->firstname }} {{ $order->orderDeliveryAddress->lastname }}</td>
                                </tr>                                                   
                                <tr>
                                    <td>Street</td>
                                    <td scope="row">{{ $order->orderDeliveryAddress->street }} {{ $order->orderDeliveryAddress->housenumber }}</td>
                                </tr> 
                                <tr>
                                    <td>Zipcode</td>
                                    <td scope="row">{{ $order->orderDeliveryAddress->zipcode }} {{ $order->orderDeliveryAddress->city }}</td>
                                </tr>  
                                <tr>
                                    <td>Country</td>
                                    <td scope="row">{{ $order->orderDeliveryAddress->country }}</td>
                                </tr> 
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h3>Bill address</h3>
                            <table class="table">  
                                <tr>
                                    <td>Gender</td>
                                    <td scope="row">{{ $order->orderBillAddress->gender }}</td>
                                </tr> 
                                <tr>
                                    <td>Name</td>
                                    <td scope="row">{{ $order->orderBillAddress->firstname }} {{ $order->orderBillAddress->lastname }}</td>
                                </tr>                                                  
                                <tr>
                                    <td>Street</td>
                                    <td scope="row">{{ $order->orderBillAddress->street }} {{ $order->orderBillAddress->housenumber }}</td>
                                </tr>
                                <tr>
                                    <td>Zipcode</td>
                                    <td scope="row">{{ $order->orderBillAddress->zipcode }} {{ $order->orderBillAddress->city }}</td>
                                </tr> 
                                <tr>
                                    <td>Country</td>
                                    <td scope="row">{{ $order->orderBillAddress->country }}</td>
                                </tr>                                                                                 
                            </table>
                        </div> 
           
              
                        <div class="col-md-12 table-responsive">
                            <h3>Sending Method</h3>
                            <table class="table">
                                <tbody>
                        
                                    <tr>
                                        <td>Title:</td><td>{{ $order->orderSendingMethod->title }}</td>
                                        
                                    </tr>
                                    <tr>
                                        <td>Price with tax:</td><td>&euro; {{ $order->orderSendingMethod->price_with_tax }}</td>                                        
                                    </tr>
                                    <tr>
                                        <td>Price without tax:</td><td>&euro; {{ $order->orderSendingMethod->price_without_tax }}</td>                                        
                                    </tr> 

                                    <tr>
                                        <td>Tax rate:</td><td>{{ $order->orderSendingMethod->tax_rate }}</td>                                        
                                    </tr> 

                                </tbody>
                            </table>
                        </div>
               
                
             
                        <div class="col-md-12 table-responsive">
                                <h3>Payment Method</h3>
                            <table class="table">

                                <tbody>
                        
                                    <tr>
                                        <td>Title:</td><td>{{ $order->orderPaymentMethod->title }}</td>
                                        
                                    </tr>
                                    <tr>
                                        <td>Price with tax:</td><td>&euro; {{ $order->orderPaymentMethod->price_with_tax }}</td>                                        
                                    </tr>
                                    <tr>
                                        <td>Price without tax:</td><td>&euro; {{ $order->orderPaymentMethod->price_without_tax }}</td>                                        
                                    </tr>    

                                    <tr>
                                        <td>Tax rate:</td><td>{{ $order->orderPaymentMethod->tax_rate }}</td>                                        
                                    </tr>

                                                             
                                </tbody>
                            </table>
                        </div>
                                      
                    </div>

                </div>
                <div class="col-md-6">
                    <h3>Products</h3>
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table">
                                <thead>
                                  <tr>
                                    <th>Title</th>
                                    <th>Amount</th>
                                    <th>Price (tax)</th>
                                    <th>Price (notax)</th>
                                    <th>Tax</th>
                                    <th>Total price (tax)</th>
                                    <th>Total price (notax)</th>
                                  </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->products as $product)
                                   <tr>

                                        <td>{{ $product->title }}</td>
                                        <td>{{ $product->amount }}</td>
                                        <td>&euro; {{ $product->price_with_tax }}</td>
                                        <td>&euro; {{ $product->price_without_tax }}</td>
                                        <td>{{ $product->tax_rate }}</td>
                                        <td>&euro; {{ $product->total_price_with_tax }}</td>
                                        <td>&euro; {{ $product->total_price_without_tax }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                                    
                </div>            
            </div>

        </div>

    </div>

</div>
@stop