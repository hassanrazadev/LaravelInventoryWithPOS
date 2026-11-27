@extends('layouts.master')

@section('title', 'My Profile')

@section('content')
    <div class="page-heading">
        <div>
            <h1 class="page-title">My Profile</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a>
                </li>
                <li class="breadcrumb-item">My Profile</li>
            </ol>
        </div>
    </div>

    <div class="page-content fade-in-up">
        <div class="row">
            <div class="col-lg-6">
                <div class="ibox">
                    <div class="ibox-head">
                        <div class="ibox-title">Account Information</div>
                    </div>
                    <div class="ibox-body">
                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group required">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'name') }}" value="{{ old('name', $user->name) }}" required>
                                @include('partials._error', ['field' => 'name'])
                            </div>
                            <div class="form-group required">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'email') }}" value="{{ old('email', $user->email) }}" required>
                                @include('partials._error', ['field' => 'email'])
                            </div>
                            <div class="form-group">
                                <label>Assigned Roles</label>
                                <div>
                                    @forelse($user->roles as $role)
                                        <span class="badge badge-soft-primary text-capitalize mr-1">{{ $role->name }}</span>
                                    @empty
                                        <span class="badge badge-soft-secondary">No roles assigned</span>
                                    @endforelse
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Profile</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="ibox">
                    <div class="ibox-head">
                        <div class="ibox-title">Change Password</div>
                    </div>
                    <div class="ibox-body">
                        <form action="{{ route('profile.password') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group required">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'current_password') }}" required>
                                @include('partials._error', ['field' => 'current_password'])
                            </div>
                            <div class="form-group required">
                                <label for="password">New Password</label>
                                <input type="password" id="password" name="password" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'password') }}" required>
                                @include('partials._error', ['field' => 'password'])
                            </div>
                            <div class="form-group required">
                                <label for="password_confirmation">Confirm New Password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
