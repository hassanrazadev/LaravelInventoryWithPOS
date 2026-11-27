<div class="page-content fade-in-up">
    <div class="ibox">
        <div class="ibox-head">
            <div class="ibox-title">{{ isset($role) ? 'Edit Role' : 'Create Role' }}</div>
        </div>
        <div class="ibox-body">
            <form action="{{ isset($role) ? route('roles.update', $role->id) : route('roles.store') }}" method="POST">
                @csrf
                @if(isset($role))
                    @method('PUT')
                @endif

                <div class="row mb-4 align-items-end">
                    <div class="col-md-6">
                        <div class="form-group required mb-0">
                            <label for="name">Role Name</label>
                            <input type="text" id="name" name="name" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'name') }}" value="{{ old('name', $role->name ?? '') }}" placeholder="e.g. supervisor" {{ isset($role) && $role->name === 'admin' ? 'readonly' : 'required' }}>
                            @include('partials._error', ['field' => 'name'])
                            @if(isset($role) && $role->name === 'admin')
                                <small class="text-muted">The admin role name is protected and bypasses permission checks via system policy.</small>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 d-flex justify-content-md-end mt-3 mt-md-0">
                        <label class="ui-checkbox ui-checkbox-primary mb-2">
                            <input type="checkbox" id="toggle-all-permissions">
                            <span class="input-span"></span><strong>Select / Deselect All Permissions</strong>
                        </label>
                    </div>
                </div>

                @php
                    $selectedPermissions = old('permissions', $assignedPermissions ?? []);
                @endphp

                <div class="row">
                    @foreach($permissionGroups as $group => $permissions)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="permission-group-card">
                                <div class="permission-group-header">
                                    <h6 class="permission-group-title">{{ ucfirst($group) }}</h6>
                                    <label class="ui-checkbox ui-checkbox-primary mb-0" style="font-size: 12px;">
                                        <input type="checkbox" class="toggle-group-permissions" data-group="{{ $group }}">
                                        <span class="input-span"></span>All
                                    </label>
                                </div>
                                <div class="permission-group-body">
                                    @foreach($permissions as $permission)
                                        <div class="mb-2">
                                            <label class="ui-checkbox ui-checkbox-info mb-0" style="font-size: 13px;">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="permission-checkbox" data-group="{{ $group }}" {{ in_array($permission->name, $selectedPermissions, true) ? 'checked' : '' }}>
                                                <span class="input-span"></span>{{ ucwords($permission->name) }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 d-flex justify-content-end">
                    <a href="{{ route('roles.index') }}" class="btn btn-light mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">{{ isset($role) ? 'Update Role' : 'Create Role' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
