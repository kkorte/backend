<select  data-url="/admin/dashboard/stats/totals-by-year" class="form-control stats-totals">


    @foreach($years as $year)
    @if($year->year == $selectedYear)
    <option value="{!! $year->year !!}" selected="selected">{!! $year->year !!}</option>
    @else
    <option value="{!! $year->year !!}">{!! $year->year !!}</option>
    @endif
    @endforeach
</select>

@if($revenueChart)
<div id="perf_div3"></div>
@columnchart('totalOrders', 'perf_div3')
@else
<p>geen resultaat</p>
@endif