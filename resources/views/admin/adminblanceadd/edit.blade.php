@extends('admin.master')

@section('content')

<div class="container">
    <div class="page-header floating mb-4">
        <h1>💳 Balance Update</h1>
    </div>

    <div class="card p-4 shadow-sm">
        <form id="balanceForm" action="{{ route('admin.balance.update', $blance_edit->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Amount -->
            <div class="form-group mb-3">
                <label class="form-label">💰 Balance Amount</label>
                <input type="number" class="form-control" name="amount" id="amount"
                       value="{{ $blance_edit->balance ?? ''}}"
                       placeholder="Enter amount (e.g., 100.00)" step="0.01" min="0" >
                @error('amount')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">💾 Update Balance</button>
        </form>
    </div>
</div>

@endsection
