@extends('layouts.front')

@section('content')
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="login-panel panel panel-default">

                <div class="panel-heading" style="text-align: center;background-color: inherit;border: none;padding: 30px 0 20px 0">
                    <img src="{{ url('/images/logo.png') }}" />
                </div>

                <div class="panel-body">

                    <form role="form" action="{{ route('login') }}" method="POST" onsubmit="disableFormButton()">

                        {{ csrf_field() }}

                        <fieldset>
                            <div class="input-group" style="margin-bottom: 20px">
                            <span class="input-group-addon">
                                <span style="padding: 0 10px" class="fa fa-user" aria-hidden="true"></span>
                                <span style="width: 64px;display: inline-block;text-align: left">{{ __('E-Mail') }}</span>
                            </span>
                                <input class="form-control input-lg" placeholder="{{ __('Please write your e-mail address') }}" name="email" type="email" autocomplete="username" autofocus value="{{ old('email') }}" />
                            </div>

                            <div class="input-group" style="margin-bottom: 20px">
                            <span class="input-group-addon">
                                <span style="padding: 0 10px" class="fa fa-lock" aria-hidden="true"></span>
                                <span style="width: 64px;display: inline-block;text-align: left">{{ __('Password') }}</span>
                            </span>
                                <input class="form-control input-lg" placeholder="{{ __('Please write your password') }}" name="password" type="password" value="" autocomplete="current-password"/>
                            </div>

                            @if( request('error') == 1 )
                                <div class="alert alert-danger">{{ __('Your session has expired. Please log in again.') }}</div>
                            @endif

                            @if ( $errors->has('email') )
                                <div class="alert alert-danger">{{ $errors->first('email') }}</div>

                            @elseif( $errors->has('password') )
                                <div class="alert alert-danger">{{ $errors->first('password') }}</div>
                            @endif

                            <button id="sign-in-button" style="margin-bottom:10px" type="submit" class="btn btn-lg btn-success btn-block"><span class="fa fa-sign-in"></span>&nbsp;&nbsp;LOGIN</button>

                            <script>
                                function disableFormButton() {
                                    btn = document.getElementById('sign-in-button');
                                    btn.disabled = true;
                                    btn.innerText = '{{ __('Checking...') }}'
                                }
                            </script>

                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
