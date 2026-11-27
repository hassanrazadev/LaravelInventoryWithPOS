@extends(auth()->check() ? 'layouts.master' : 'layouts.auth')

@section('title', 'Access Denied')

@section('content')
    @if(auth()->check())
        <div class="page-content fade-in-up">
            <div class="ibox">
                <div class="ibox-body text-center py-5">
                    <div class="mb-3">
                        <span class="badge badge-soft-danger" style="font-size: 14px; padding: 8px 16px;">403 • Access Denied</span>
                    </div>
                    <h2 class="font-weight-bold mb-2">You don't have permission to access this resource.</h2>
                    <p class="text-muted mb-4">Please contact your administrator if you believe you should have access.</p>
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">
                        <i class="fa fa-arrow-left mr-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-3">
            <span class="badge badge-soft-danger mb-3">403 • Access Denied</span>
            <h2 class="login-title text-center">Access Denied</h2>
            <p class="ims-auth-subtitle text-center">You don't have permission to view this page.</p>
            <a href="{{ route('user.login') }}" class="btn btn-primary btn-block">Go to Login</a>
        </div>
    @endif
@endsection
