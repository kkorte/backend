
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />

        <style>

            html { font-family: 'Arial'; font-size:14px;}
            body { font-family: 'Arial'; 
                   font-size:14px; 
                   padding:10px; 
                   background: url('/logo.png'); 
                   background-repeat: no-repeat; background-position: 10px 10px;}
            .contact {text-align:right;  padding-bottom:90px; }
            h1 {font-size:14px; padding:0px; margin:0px; color: #000; padding-bottom:10px;}
            h2 {font-size:14px; padding:0px; margin:0px;  color: #000;}
            h3 {font-size:14px; padding:0px; margin:0px;  color: black;}
            p { padding:0; margin:0;   font-size:14px;  }
            table { font-size: 14px; }
            .products{ text-align:left; width:100%; border-spacing: 0px;}
            .products th { text-align:left; padding:10px;  background-color: #000; color:white; }

            .products td {text-align:left; color:black;  padding:10px;}

            .products .total {text-align:right;}
            .details{ padding-bottom:10px;   width:100%;  text-align:left; }
            .color {  background-color:#EAE9E7; }
            .details .left { text-align:right; padding-right:20px; }
            td { padding-left: 10px;  font-size:14px;}
            a {text-decoration:none;  color: #000;}
            .title a {color:black;}
            .address {text-align:left; width:100%; padding-bottom:10px; }
            .address td {text-align:left; font-size:14px;}
            .address th {text-align:left; font-size:14px; font-weight:bold; color: #000;}
            .footer {position:absolute; bottom:0px;}
            .footer p { font-size:10px;}
            .logo { height:50px; position: absolute; left: 20px; top: 0px;}
        </style>

    </head>
    <body>
        @if($invoice->order->shop->logo_file_name)
        <img src="http://shop.brulo.nl/files/{!! $invoice->order->shop->id !!}/logo/{!! $invoice->order->shop->logo_file_name !!}" class="logo" />
        @endif

	    <div class="contact">
            @if(!$invoice->order->shop->logo_file_name)
	        <p>
	            {!! $invoice->order->shop->url !!} <br/>
	        </p>
            @endif
	        <h1>Factuurnummer: {{ $invoice->generated_custom_invoice_id }}</h1>
	        <h3>Datum: {{ date('d-m-Y', strtotime($invoice->created_at)) }}</h3>
	    </div>
	    <table class="products" cellspacing="0" cellpadding="2">
	        <tr><th width="60%">product</th><th width="10%">aantal</th><th width="15%">stuk prijs</th><th width="15%">totaal prijs</th></tr>
	        @foreach ($invoice->products as $product)
	            <tr>

                    <td class="title">
                        {{ htmlspecialchars($product->title) }}
                        @if($product->productAttribute()->count())
                        <br/><small>{{ htmlspecialchars($product->product_attribute_title) }}</small>
                        @endif
                    </td>

	                <td>{{ $product->amount }}</td>
	                <td>&#0128; {{ $product->price_with_tax }}</td>
	                <td>&#0128; {{ $product->total_price_with_tax }}</td>
	            </tr>
	        @endforeach
	    </table>
	    <table class="details" cellspacing=0 cellpadding="2">
	        <tr>
	            <th colspan="3" width="85%">&nbsp;</th>
	            <th>&nbsp;</th>
	        </tr>

	        <tr>
	            <td colspan="3" class="left"><strong>totaal:</strong></td>
	            <td class="color">&#0128; {{ $invoice->price_with_tax }}</td>
	        </tr>
	        <tr>
	            <td colspan="3" class="left"><strong>btw:</strong></td>
	            <td class="color">&#0128; {{  $invoice->taxTotal() }}</td>
	        </tr>
	        <tr>
	            <td colspan="3" class="left"><strong>btw gesplitst:</strong></td>
	            <td class="color">&nbsp;</td>
	        </tr>

			@foreach ($invoice->taxDetails() as $key => $val)
	        <tr>
	            <td colspan="3" class="left"><strong>{{ round($key) }}%</strong></td>
	            <td class="color">&euro; {{ round($val,2) }}</td>
	        </tr>
	    	@endforeach

	    </table>

        <table class="address" cellspacing=0 cellpadding=0>
            <tr>
                <th>Factuuradres:</th>
                <th>Afleveradres:</th>
            </tr>
            <tr>
                <td>
                    <?php echo $invoice->invoiceBillAddress->firstname; ?> <?php echo $invoice->invoiceBillAddress->suffix; ?> <?php echo $invoice->invoiceBillAddress->lastname; ?>
                </td>
                <td>
                    <?php echo $invoice->invoiceDeliveryAddress->firstname; ?> <?php echo $invoice->invoiceDeliveryAddress->suffix; ?> <?php echo $invoice->invoiceDeliveryAddress->lastname; ?>
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $invoice->invoiceBillAddress->street; ?> <?php echo $invoice->invoiceBillAddress->housenumber . ' ' . $invoice->invoiceBillAddress->housenumber_suffix; ?>
                </td>
                <td>
                    <?php echo $invoice->invoiceDeliveryAddress->street; ?> <?php echo $invoice->invoiceDeliveryAddress->housenumber . ' ' . $invoice->invoiceDeliveryAddress->housenumber_suffix; ?>
                </td>     
            </tr>
            <tr>
                <td><?php echo $invoice->invoiceBillAddress->zipcode; ?> <?php echo $invoice->invoiceBillAddress->city; ?></td>
                <td><?php echo $invoice->invoiceDeliveryAddress->zipcode; ?> <?php echo $invoice->invoiceDeliveryAddress->city; ?></td>
            </tr>
            <tr>
                <td>
                    <?php if ($invoice->invoiceBillAddress->country == 0) : ?>
                        Nederland
                    <?php elseif ($invoice->invoiceBillAddress->country == 1) : ?>
                        België
                    <?php endif; ?>
                </td>
                <td>                         
                    <?php if ($invoice->invoiceDeliveryAddress->country == 0) : ?>
                        Nederland
                    <?php elseif ($invoice->invoiceDeliveryAddress->country == 1) : ?>
                        België
                    <?php endif; ?>
                </td>
            </tr>
        </table>
</body>
</html>