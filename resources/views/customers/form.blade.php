<div class="page-content fade-in-up">
    <div class="ibox">
        <div class="ibox-head">
            <div class="ibox-title">{{ isset($customer) ? 'Edit Customer' : 'Create Customer' }}</div>
        </div>
        <div class="ibox-body">
            <form action="{{ isset($customer) ? route('customers.update', $customer->id) : route('customers.store') }}" method="POST">
                @csrf
                @if(isset($customer))
                    @method('PUT')
                @endif

                @include('partials._contact-form', ['contact' => $customer ?? null])

                <div class="mt-3 d-flex justify-content-end">
                    <a href="{{ route('customers.index') }}" class="btn btn-light mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">{{ isset($customer) ? 'Update Customer' : 'Create Customer' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
