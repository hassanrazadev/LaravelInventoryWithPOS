@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
    <h2 class="login-title">Choose a new password</h2>
    <p class="ims-auth-subtitle">Enter your email and confirm your new password below.</p>

    @if ($errors->any())
        <div class="alert alert-danger mb-3">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('password.update') }}" method="post">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="form-group">
            <label for="email" class="font-weight-medium text-dark" style="font-size: 13px;">Email address</label>
            <input class="form-control" value="{{ old('email', $email) }}" type="email" id="email" name="email" required autofocus>
        </div>
        <div class="form-group">
            <label for="password" class="font-weight-medium text-dark" style="font-size: 13px;">New Password</label>
            <input class="form-control" type="password" id="password" name="password" placeholder="Minimum 6 characters" required>
        </div>
        <div class="form-group">
            <label for="password_confirmation" class="font-weight-medium text-dark" style="font-size: 13px;">Confirm Password</label>
            <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" placeholder="Re-enter password" required>
        </div>
        <div class="form-group mb-0">
            <button class="btn btn-primary btn-block py-2" type="submit">Reset Password</button>
        </div>
    </form>
@endsection
