<div class="page-content fade-in-up">
    <div class="ibox">
        <div class="ibox-head">
            <div class="ibox-title">{{ isset($supplier) ? 'Edit Supplier' : 'Create Supplier' }}</div>
        </div>
        <div class="ibox-body">
            <form action="{{ isset($supplier) ? route('suppliers.update', $supplier->id) : route('suppliers.store') }}" method="POST">
                @csrf
                @if(isset($supplier))
                    @method('PUT')
                @endif

                @include('partials._contact-form', ['contact' => $supplier ?? null])

                <div class="mt-3 d-flex justify-content-end">
                    <a href="{{ route('suppliers.index') }}" class="btn btn-light mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">{{ isset($supplier) ? 'Update Supplier' : 'Create Supplier' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
