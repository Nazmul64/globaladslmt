@extends('admin.master')

@section('content')
<div class="container">
    <div class="page-header mb-4">
        <h4>Balance Update</h4>
    </div>

    <div class="card p-4 shadow-sm">
        <form action="{{ route('admin.agent.balance.update', $blance_edit->agent_id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group mb-3">
                <label class="form-label">Balance Amount</label>
                <input type="number"
                       class="form-control"
                       name="amount"
                       value="{{ $blance_edit->amount }}"
                       step="0.01"
                       min="0"
                       required>
                @error('amount')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">
                Update Balance
            </button>
        </form>
    </div>
</div>
@endsection
