<div class="page-content fade-in-up">
    <div class="ibox">
        <div class="ibox-head">
            <div class="ibox-title">{{ isset($expense) ? 'Edit Expense' : 'Record Expense' }}</div>
        </div>
        <div class="ibox-body">
            <form action="{{ isset($expense) ? route('expenses.update', $expense->id) : route('expenses.store') }}" method="POST">
                @csrf
                @if(isset($expense))
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group required">
                            <label for="expense_name">Expense Title</label>
                            <input type="text" id="expense_name" name="expense_name" maxlength="50" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'expense_name') }}" value="{{ old('expense_name', $expense->expense_name ?? '') }}" placeholder="e.g. Store Rent" required>
                            @include('partials._error', ['field' => 'expense_name'])
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="category">Category</label>
                            @php
                                $selectedCategory = old('category', $expense->category ?? '');
                            @endphp
                            <select id="category" name="category" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'category') }}">
                                <option value="">Select category (optional)</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ $selectedCategory === $category ? 'selected' : '' }}>{{ $category }}</option>
                                @endforeach
                            </select>
                            @include('partials._error', ['field' => 'category'])
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group required">
                            <label for="expense_amount">Amount ($)</label>
                            <input type="number" step="0.01" min="0.01" id="expense_amount" name="expense_amount" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'expense_amount') }}" value="{{ old('expense_amount', $expense->expense_amount ?? '') }}" placeholder="0.00" required>
                            @include('partials._error', ['field' => 'expense_amount'])
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="expense_date">Expense Date</label>
                            <input type="date" id="expense_date" name="expense_date" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'expense_date') }}" value="{{ old('expense_date', isset($expense) && $expense->expense_date ? $expense->expense_date->format('Y-m-d') : now()->format('Y-m-d')) }}">
                            @include('partials._error', ['field' => 'expense_date'])
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea id="notes" name="notes" rows="2" maxlength="500" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'notes') }}" placeholder="Optional payment details, invoice or vendor reference">{{ old('notes', $expense->notes ?? '') }}</textarea>
                            @include('partials._error', ['field' => 'notes'])
                        </div>
                    </div>
                </div>

                <div class="mt-3 d-flex justify-content-end">
                    <a href="{{ route('expenses.index') }}" class="btn btn-light mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">{{ isset($expense) ? 'Update Expense' : 'Save Expense' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
