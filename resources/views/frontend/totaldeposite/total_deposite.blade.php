@extends('frontend.master')

@section('content')
<div class="container my-4">
    <div class="option-card text-center shadow-sm p-4 rounded bg-light">
        <div class="icon-circle mb-3">
           <i class="fas fa-wallet fa-2x white-icon"></i>
        </div>
        <div class="option-title h5 mb-2">Deposit Total Balance</div>
        <div class="fw-bold fs-4 text-primary">
            $ {{ round($total_deposite ?? '') }}
        </div>
    </div>
</div>
@endsection
