<div class="page-content fade-in-up">
    <div class="ibox">
        <div class="ibox-head">
            <div class="ibox-title">{{ isset($user) ? 'Edit User' : 'Create User' }}</div>
        </div>
        <div class="ibox-body">
            <form action="{{ isset($user) ? route('users.update', $user->id) : route('users.store') }}" method="POST">
                @csrf
                @if(isset($user))
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group required">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'name') }}" value="{{ old('name', $user->name ?? '') }}" placeholder="Full Name" required>
                            @include('partials._error', ['field' => 'name'])
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group required">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'email') }}" value="{{ old('email', $user->email ?? '') }}" placeholder="user@example.com" required>
                            @include('partials._error', ['field' => 'email'])
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group {{ isset($user) ? '' : 'required' }}">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'password') }}" placeholder="{{ isset($user) ? 'Leave blank to keep current password' : 'Minimum 6 characters' }}" {{ isset($user) ? '' : 'required' }}>
                            @include('partials._error', ['field' => 'password'])
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group {{ isset($user) ? '' : 'required' }}">
                            <label for="password_confirmation">Confirm Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Re-enter password" {{ isset($user) ? '' : 'required' }}>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group required">
                            <label for="roles">Roles</label>
                            @php
                                $selectedRoles = old('roles', isset($user) ? $user->roles->pluck('name')->all() : []);
                            @endphp
                            <select name="roles[]" id="roles" class="form-control select2_roles {{ \App\Utils\AppUtils::inputFieldError($errors, 'roles') }}" multiple required>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ in_array($role->name, $selectedRoles, true) ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                                @endforeach
                            </select>
                            @include('partials._error', ['field' => 'roles'])
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="form-group mt-3 mb-0">
                            <input type="hidden" name="email_verified" value="0">
                            @php
                                $isVerified = old('email_verified', isset($user) ? (int) ($user->email_verified_at !== null) : 1);
                            @endphp
                            <label class="ui-checkbox ui-checkbox-primary mb-0">
                                <input type="checkbox" name="email_verified" value="1" {{ (int) $isVerified === 1 ? 'checked' : '' }}>
                                <span class="input-span"></span>Email Verified (allows immediate sign-in)
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-3 d-flex justify-content-end">
                    <a href="{{ route('users.index') }}" class="btn btn-light mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">{{ isset($user) ? 'Update User' : 'Create User' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
