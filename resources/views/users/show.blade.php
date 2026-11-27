@extends('layouts.master')

@section('title', $user->name)

@section('content')
    <div class="page-heading ims-page-header">
        <div>
            <h1 class="page-title">User Details</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
                <li class="breadcrumb-item">{{ $user->name }}</li>
            </ol>
        </div>
        @can('update user')
            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">
                <i class="fa fa-pencil mr-1"></i> Edit User
            </a>
        @endcan
    </div>

    <div class="page-content fade-in-up">
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title d-flex align-items-center">
                    <span class="ims-avatar ims-avatar-sm mr-2">{{ \App\Utils\AppUtils::initials($user->name) }}</span>
                    <span>{{ $user->name }}</span>
                </div>
            </div>
            <div class="ibox-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Email</div>
                        <div>{{ $user->email }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Verification Status</div>
                        <div>
                            @if($user->email_verified_at)
                                <span class="badge badge-soft-success">Verified ({{ $user->email_verified_at }})</span>
                            @else
                                <span class="badge badge-soft-warning">Unverified</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Assigned Roles</div>
                        <div class="mt-1">
                            @forelse($user->roles as $role)
                                <span class="badge badge-soft-primary text-capitalize mr-1">{{ $role->name }}</span>
                            @empty
                                <span class="text-muted">-</span>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Account Status</div>
                        <div>
                            @if($user->trashed())
                                <span class="badge badge-soft-danger">Inactive (Soft Deleted)</span>
                            @else
                                <span class="badge badge-soft-success">Active</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Created By</div>
                        <div>{{ $user->createdBy ? $user->createdBy->name : '-' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Updated By</div>
                        <div>{{ $user->updatedBy ? $user->updatedBy->name : '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
