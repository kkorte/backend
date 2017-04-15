@extends('_layouts.login')

@section('main')

<div class="login-container">

    <div class="login-header login-caret">

        <div class="login-content">

            <a href="#" class="logo">
                <h1 style="color:#949494;">
                    Forgot password
                </h1>
            </a>
            <p class="description">enter your email</p>
        </div>

    </div>

    <div class="login-progressbar">
        <div></div>
    </div>

    <div class="login-form">

        <div class="login-content">
    @if (Session::get('error'))
        <div class="alert alert-error alert-danger">{{{ Session::get('error') }}}</div>
    @endif

    @if (Session::get('notice'))
        <div class="alert">{{{ Session::get('notice') }}}</div>
    @endif
<form method="POST" action="{{{ URL::to('/security/reset_password') }}}" accept-charset="UTF-8">
    <input type="hidden" name="token" value="{{{ $token }}}">
    <input type="hidden" name="_token" value="{!! Session::token() !!}">




    <div class="form-group">


                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="entypo-key"></i>
                    </div>
                     {{ Form::password('password', array('class' =>'form-control', 'placeholder'=>'Password')) }}
                 </div>

   </div>

    <div class="form-group">


                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="entypo-key"></i>
                    </div>
                     {{ Form::password('password_confirmation', array('class' =>'form-control', 'placeholder'=>'Confirm password')) }}
                 </div>
    	
   </div>




    @if (Session::get('error'))
        <div class="alert alert-error alert-danger">{{{ Session::get('error') }}}</div>
    @endif

    @if (Session::get('notice'))
        <div class="alert">{{{ Session::get('notice') }}}</div>
    @endif

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block btn-login">
                                Send
                                <i class="entypo-right-open-mini"></i>
                            </button>
                        </div>
</form>


            <div class="link">    
                <a href="{{ URL::route('hideyo.login') }}" class="link"><i class="entypo-back"></i> back to login</a>               
            </div>

</div>
</div>
</div>
@stop








