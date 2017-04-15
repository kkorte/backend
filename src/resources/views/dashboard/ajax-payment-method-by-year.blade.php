
<select  data-url="/admin/dashboard/stats/payment-method-by-year" class="form-control stats-payment-method">


    @foreach($years as $year)
    @if($year->year == $selectedYear)
    <option value="{!! $year->year !!}" selected="selected">{!! $year->year !!}</option>
    @else
    <option value="{!! $year->year !!}">{!! $year->year !!}</option>
    @endif
    @endforeach
</select>

@if($revenueChartPaymentMethod)
<div id="perf_div5"></div>
@columnchart('PaymentMethodDetect', 'perf_div5')
@else
<p>geen resultaat</p>
@endif