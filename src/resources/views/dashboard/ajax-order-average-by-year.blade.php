<select  data-url="/admin/dashboard/stats/order-average-by-year" class="form-control stats-average-order">


    @foreach($years as $year)
    @if($year->year == $selectedYear)
    <option value="{!! $year->year !!}" selected="selected">{!! $year->year !!}</option>
    @else
    <option value="{!! $year->year !!}">{!! $year->year !!}</option>
    @endif
    @endforeach
</select>

@if($revenueAverageChart)
<div id="perf_div6"></div>
@columnchart('FinancesAverage', 'perf_div6')
@else
<p>geen resultaat</p>
@endif