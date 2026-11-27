@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
    <h2 class="login-title">Reset your password</h2>
    <p class="ims-auth-subtitle">Enter your account email and we will send a password reset link.</p>

    @if(session('message'))
        <div class="alert {{ session('status') === false ? 'alert-danger' : 'alert-success' }} mb-3">
            {{ session('message') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-3">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('password.email') }}" method="post">
        @csrf
        <div class="form-group">
            <label for="email" class="font-weight-medium text-dark" style="font-size: 13px;">Email address</label>
            <input class="form-control" value="{{ old('email') }}" type="email" id="email" name="email" placeholder="you@example.com" required autofocus>
        </div>
        <div class="form-group mb-3">
            <button class="btn btn-primary btn-block py-2" type="submit">Send Reset Link</button>
        </div>
        <div class="text-center">
            <a href="{{ route('user.login') }}" style="font-size: 13px;"><i class="fa fa-arrow-left mr-1"></i> Back to Login</a>
        </div>
    </form>
@endsection
