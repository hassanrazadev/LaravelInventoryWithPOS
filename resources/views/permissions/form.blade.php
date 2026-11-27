<div class="page-content fade-in-up">
    <div class="ibox">
        <div class="ibox-head">
            <div class="ibox-title">{{ isset($permission) ? 'Edit Permission' : 'Create Permission' }}</div>
        </div>
        <div class="ibox-body">
            <form action="{{ isset($permission) ? route('permissions.update', $permission->id) : route('permissions.store') }}" method="POST">
                @csrf
                @if(isset($permission))
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group required">
                            <label for="name">Permission Name</label>
                            <input type="text" id="name" name="name" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'name') }}" value="{{ old('name', $permission->name ?? '') }}" placeholder="e.g. export report" required>
                            @include('partials._error', ['field' => 'name'])
                            <small class="text-muted">Convention: <code>action module</code> (e.g., <code>read report</code>).</small>
                        </div>
                    </div>
                </div>

                <div class="mt-2">
                    <a href="{{ route('permissions.index') }}" class="btn btn-light mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">{{ isset($permission) ? 'Update Permission' : 'Create Permission' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
