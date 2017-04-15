<div class="form-group">
    {!! Form::label('meta_title', 'Meta title', array('class' => 'col-sm-3 control-label')) !!}

    <div class="col-sm-5">
        {!! Form::text('meta_title', null, array('class' => 'form-control counter', 'maxlength' => 50, 'required' => 'required')) !!}
        <div class="help-block with-errors"></div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('meta_description', 'Meta description', array('class' => 'col-sm-3 control-label')) !!}

    <div class="col-sm-5">
        {!! Form::text('meta_description', null, array('class' => 'form-control counter', 'maxlength' => 150)) !!}
 
    </div>
</div> 

<div class="form-group">
    {!! Form::label('meta_keywords', 'Meta keywords', array('class' => 'col-sm-3 control-label')) !!}

    <div class="col-sm-5">
        {!! Form::text('meta_keywords', null, array('class' => 'form-control')) !!}
    </div>
</div> 