
<div class="form-group">
    <div class="col-sm-offset-3 col-sm-5">
        
        {!! Form::submit('Save', array('class' => 'btn btn-default')) !!}
        @if(isset($cancelRouteParameters))
        <a href="{!! URL::route($cancelRoute, $cancelRouteParameters) !!}" class="btn btn-large">Cancel</a>
        @else
        <a href="{!! URL::route($cancelRoute) !!}" class="btn btn-large">Cancel</a>       
        @endif
    </div>
</div>