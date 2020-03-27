@if ($errors->has($field))
    <span class="invalid-feedback" role="alert">
        <span class="text-danger">{{ $errors->first($field) }}</span>
    </span>
@endif