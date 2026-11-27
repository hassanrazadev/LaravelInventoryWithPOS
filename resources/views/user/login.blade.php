@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <h2 class="login-title">Welcome back</h2>
    <p class="ims-auth-subtitle">Sign in to your {{ config('app.name', 'IMS-POS') }} account</p>

    @if(session('message'))
        <div class="alert {{ session('status') === false ? 'alert-danger' : 'alert-success' }} mb-3">
            {{ session('message') }}
        </div>
    @elseif(is_string(session('status')))
        <div class="alert alert-success mb-3">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-3">
            {{ $errors->first() }}
        </div>
    @endif

    <form id="login-form" action="{{ route('user.doLogin') }}" method="post">
        @csrf
        <div class="form-group">
            <label for="email" class="font-weight-medium text-dark" style="font-size: 13px;">Email address</label>
            <input class="form-control" value="{{ old('email') }}" type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email" autofocus>
        </div>
        <div class="form-group">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="password" class="font-weight-medium text-dark mb-0" style="font-size: 13px;">Password</label>
                <a href="{{ route('password.request') }}" style="font-size: 12.5px;">Forgot password?</a>
            </div>
            <input class="form-control" type="password" value="" id="password" name="password" placeholder="Enter your password" autocomplete="current-password">
        </div>
        <div class="form-group d-flex align-items-center justify-content-between">
            <label class="ui-checkbox ui-checkbox-info mb-0">
                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                <span class="input-span"></span>Remember me
            </label>
        </div>
        <div class="form-group mb-0">
            <button class="btn btn-primary btn-block py-2" type="submit">Sign In</button>
        </div>
    </form>
@endsection

@section('js')
    <script src="{{ asset('assets/js/jquery.validate.min.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        $(function () {
            $('#login-form').validate({
                errorClass: "help-block text-danger",
                rules: {
                    email: {
                        required: true,
                        email: true
                    },
                    password: {
                        required: true
                    }
                },
                highlight: function (e) {
                    $(e).closest(".form-group").addClass("has-error");
                },
                unhighlight: function (e) {
                    $(e).closest(".form-group").removeClass("has-error");
                }
            });
        });
    </script>
@endsection
