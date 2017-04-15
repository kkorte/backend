@if($orders)
<table class="table table-striped table-bordered">
<tbody>
@foreach($orders as $order)
<tr>
	<td>
		<input type="checkbox" name="order[]" value="{!! $order['id'] !!}" checked="checked" />
	</td>
	<td>
		{!! $order['created_at'] !!}
	</td>

	<td>
		{!! $order['generated_custom_order_id'] !!}
	</td>
</tr>
@endforeach

</tbody>
</table>
@else
no products

@endif