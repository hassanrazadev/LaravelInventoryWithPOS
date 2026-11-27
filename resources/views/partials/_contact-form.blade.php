<div class="row">
    <div class="col-md-6">
        <div class="form-group required">
            <label for="name">Full / Business Name</label>
            <input type="text" id="name" name="name" maxlength="50" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'name') }}" value="{{ old('name', $contact->name ?? '') }}" placeholder="Enter name" required>
            @include('partials._error', ['field' => 'name'])
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group required">
            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" maxlength="20" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'phone') }}" value="{{ old('phone', $contact->phone ?? '') }}" placeholder="e.g. +1-555-0100" required>
            @include('partials._error', ['field' => 'phone'])
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" maxlength="50" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'email') }}" value="{{ old('email', $contact->email ?? '') }}" placeholder="contact@example.com">
            @include('partials._error', ['field' => 'email'])
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" rows="2" maxlength="500" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'address') }}" placeholder="Street address, city, notes">{{ old('address', $contact->address ?? '') }}</textarea>
            @include('partials._error', ['field' => 'address'])
        </div>
    </div>
</div>
