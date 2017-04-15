<select  data-url="/admin/dashboard/stats/browser-by-year" class="form-control stats-browser">


    @foreach($years as $year)
    @if($year->year == $selectedYear)
    <option value="{!! $year->year !!}" selected="selected">{!! $year->year !!}</option>
    @else
    <option value="{!! $year->year !!}">{!! $year->year !!}</option>
    @endif
    @endforeach
</select>

@if($revenueChart)
<div id="perf_div2"></div>
@columnchart('BrowserDetect', 'perf_div2')
@else
<p>geen resultaat</p>
@endif