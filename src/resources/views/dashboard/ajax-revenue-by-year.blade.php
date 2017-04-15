<select  data-url="/admin/dashboard/stats/revenue-by-year" class="form-control stats-revenue">


    @foreach($years as $year)
    @if($year->year == $selectedYear)
    <option value="{!! $year->year !!}" selected="selected">{!! $year->year !!}</option>
    @else
    <option value="{!! $year->year !!}">{!! $year->year !!}</option>
    @endif
    @endforeach
</select>

@if($revenueChart)
<div id="perf_div"></div>
@columnchart('Finances', 'perf_div')
@else
<p>geen resultaat</p>
@endif