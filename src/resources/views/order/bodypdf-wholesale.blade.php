
	<div class="logo">
		<img src="{!! public_path() !!}/images/logopdf.jpg" width="300" />
	</div>

	<div class="contact">
        @if($order->present_message)
        <h1>LET OP INPAKKEN</h1>
        @endif
        @if($order->orderSendingMethod->title == 'Afhalen in Rotterdam')
        <h1>LET OP AFHALEN</h1>
        @endif
		FOODELICIOUS Food&Gifts <br/>
		Mariniersweg 47 <br/>
		3011 ND Rotterdam <br/>
		Tel:  010-41 30 111<br/>
		Dinsdag tot zaterdag van 10:00 tot 18:00<br/>
		<br/>
		Orderbevestiging: {{ $order->generated_custom_order_id }} ({{ $order->client->orders->count() }})
		<br/>Datum: {{ date('d-m-Y', strtotime($order->created_at)) }}
		<br/>Groothandel bestelling
	</div>
	<table class="products" cellspacing="0" cellpadding="2">
		<tr>
			<th width="20%">nummer</th>
			<th width="50%">product</th>			
			<th width="10%">aantal</th>
			<th width="15%">prijs</th>
			<th width="15%">totaal</th>
		</tr>
		

		@foreach ($order->products as $product)
		<tr>
			<td>{{ $product->reference_code }}</td>						
			<td class="title">
				{{ htmlspecialchars($product->title) }}
				@if($product->productAttribute()->count())
				<br/><small>{{ htmlspecialchars($product->product_attribute_title) }}</small>
				@elseif($product->product AND $product->product->weight AND $product->product->weight_title)
				<br/><small>{{ htmlspecialchars($product->product->weight) }} {{ htmlspecialchars($product->product->weight_title) }}</small>
				@endif
			</td>
			<td>{{ $product->amount }}</td>	
		

            <td>
                @if($product->original_price_without_tax != $product->price_without_tax AND $product->original_price_without_tax != 0)
                &#0128; {{ $product->getPriceWithoutTaxNumberFormat() }}
                <br/><small>korting
                {{ number_format((($product->original_price_without_tax - $product->price_without_tax) / $product->original_price_without_tax) * 100)  }}%
                <br/>orginele prijs: &#0128; {{ $product->getOriginalPriceWithoutTaxNumberFormat() }}
                </small>
                @else
                &#0128; {{ $product->getPriceWithoutTaxNumberFormat() }}
                @endif                                          
            </td>
			<td>&#0128; {{ $product->getTotalPriceWithoutTaxNumberFormat() }}</td>
		</tr>
		@endforeach
	</table>

	@if($order->products->count() > 15)
	<div class="page-break"></div>
	<style>
	body {
		background: none!important;
	}
	</style>
	@endif
	<table class="details" cellspacing=0 cellpadding="2">

		<tr>
			<th colspan="3" width="85%">&nbsp;</th>
			<th>&nbsp;</th>
		</tr>

		@if($order->total_discount != 0)
		<tr>
			<td colspan="3" class="left"><strong>gekregen korting op producten:</strong></td>
			<td class="color">- &#0128; {{ $order->getTotalDiscountNumberFormat() }}</td>
		</tr>
		@endif

		<tr>
			<td colspan="3" class="left"><strong>kosten verzendmethode {{ $order->orderSendingMethod->title }}:</strong></td>
			<td class="color">&#0128; {{ $order->orderSendingMethod->getPriceWithoutTaxNumberFormat() }}</td>
		</tr>

		<tr>
			<td colspan="3" class="left"><strong>totaal:</strong></td>
			<td class="color">&#0128; {{ $order->getPriceWithoutTaxNumberFormat() }}</td>
		</tr>

		@if($order->orderBillAddress->country == 'be')

		<tr>
			<td colspan="3" class="left"><strong>btw:</strong></td>
			<td class="color">geen</td>
		</tr>

		@else

		<tr>
			<td colspan="3" class="left"><strong>btw:</strong></td>
			<td class="color">&#0128; {{  $order->taxTotal() }}</td>
		</tr>
		<tr>
			<td colspan="3" class="left"><strong>btw gesplitst:</strong></td>
			<td class="color">&nbsp;</td>
		</tr>

		@foreach ($order->taxDetails() as $key => $val)
		<tr>
			<td colspan="3" class="left"><strong>{{ round($key) }}%</strong></td>
			<td class="color">&#0128; {{ number_format($val, 2, '.', '') }}</td>
		</tr>
		@endforeach
		@endif

		@if($order->coupon_code )
		<tr>
			<td colspan="3" class="left"><strong>kortingscode:</strong></td>
			<td class="color">{{ $order->coupon_code }}</td>
		</tr>
		@endif


		@if($order->coupon_group_title )
		<tr>
			<td colspan="3" class="left"><strong>kortingsgroep:</strong></td>
			<td class="color">{{ $order->coupon_group_title }}</td>
		</tr>
		@endif
	</table>
	<table cellspacing=0 cellpadding=0>
			<tr>
				<td>
					<strong>E-mail:</strong>
				</td>
			<td>
				<?php echo $order->client->email; ?>
			</td>


		</tr>

	</table>

	<table class="address" cellspacing=0 cellpadding=0>
		<tr><th>afleveradres:</th><th>factuuradres:</th></tr>
		@if($order->orderBillAddress->company OR $order->orderDeliveryAddress->company)

		<tr>
			<td>
				<?php echo $order->orderDeliveryAddress->company; ?> 
			</td>
			<td>
				<?php echo $order->orderBillAddress->company; ?>
			</td>
		</tr>

		@endif

		<tr>
			<td>
				<?php echo $order->orderDeliveryAddress->firstname; ?> <?php echo $order->orderDeliveryAddress->suffix; ?> <?php echo $order->orderDeliveryAddress->lastname; ?>
			</td>
			<td>
				<?php echo $order->orderBillAddress->firstname; ?> <?php echo $order->orderBillAddress->suffix; ?> <?php echo $order->orderBillAddress->lastname; ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo $order->orderDeliveryAddress->street; ?> <?php echo $order->orderDeliveryAddress->housenumber . ' <strong>' . $order->orderDeliveryAddress->housenumber_suffix; ?></strong>
			</td> 			
			<td>
				<?php echo $order->orderBillAddress->street; ?> <?php echo $order->orderBillAddress->housenumber . ' <strong>' . $order->orderBillAddress->housenumber_suffix; ?></strong>
			</td>    
		</tr>
		<tr>
			<td><?php echo $order->orderDeliveryAddress->zipcode; ?> <?php echo $order->orderDeliveryAddress->city; ?></td>
			<td><?php echo $order->orderBillAddress->zipcode; ?> <?php echo $order->orderBillAddress->city; ?></td>
		</tr>

		<tr>
			<td>                         
				<?php if ($order->orderDeliveryAddress->country == 'nl') : ?>
				Nederland
				<?php elseif ($order->orderDeliveryAddress->country == 'be') : ?>
				België
				<?php endif; ?>
			</td>
			<td>
				<?php if ($order->orderBillAddress->country == 'nl') : ?>
				Nederland
				<?php elseif ($order->orderBillAddress->country == 'be') : ?>
				België
				<?php endif; ?>
			</td>
		</tr>

		<tr>
			<td><?php echo $order->orderDeliveryAddress->phone; ?></td>
			<td><?php echo $order->orderBillAddress->phone; ?></td>
		</tr>

</table>

@if($order->comments)
<table class="address" cellspacing=0 cellpadding=0>
	<tr><th>Commentaar</th></tr>
	<tr>
		<td>
			{!! $order->comments !!}
		</td>
	</tr>
</table>
@endif



@if($order->present_message)

<table class="address" cellspacing=0 cellpadding=0>
	<tr><th>Cadeauservice</th></tr>
	<tr>
		<td>
			Geslacht: 
			@if($order->present_gender == 'male')
			Man
			@else
			Vrouw
			@endif

		</td>

	</tr>

	<tr>
		<td>
			Gelegenheid: 
			{!! $order->present_occassion !!}
		</td>

	</tr>

	<tr>
		<td>
			Bericht:
			<br/>
			{!! $order->present_message !!}
		</td>

	</tr>


</table>

@endif 

{!! $pdfText !!}