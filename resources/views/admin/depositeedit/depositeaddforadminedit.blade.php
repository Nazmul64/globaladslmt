@extends('admin.master')

@section('content')
<div class="container">
    <div class="page-header floating mb-4">
        <h1>💳 Deposite Balance Update</h1>
    </div>

    <div class="card p-4 shadow-sm">
        <form action="{{ route('admin.deposite.update', $balance_edit->user_id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group mb-3">
                <label class="form-label">👤 Select User</label>
                <select class="form-control" name="user_id" required>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $balance_edit->user_id == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">💰 Balance Amount</label>
                <input type="number" class="form-control" name="amount" value="{{ old('amount', round($balance_edit->amount,2)) }}" step="0.01" min="0">
            </div>

            <button type="submit" class="btn btn-primary">💾 Update Balance</button>
        </form>
    </div>
</div>
@endsection
