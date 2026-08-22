@extends('admin.master')

@section('content')

<!-- Page Header -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Dashboard</h6>
    <ul class="d-flex align-items-center gap-2">
        <li class="fw-medium">
            <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-1 hover-text-primary">
                <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                Dashboard
            </a>
        </li>
        <li>-</li>
        <li class="fw-medium">AI</li>
    </ul>
</div>

<!-- Top Overview Widgets -->
<div class="row row-cols-xxxl-5 row-cols-lg-3 row-cols-sm-2 row-cols-1 gy-4">

    <!-- Total Users -->
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-1 h-100">
            <div class="card-body p-20">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <a href="{{ route('admin.userlist') }}" class="fw-medium text-primary-light mb-1">Total Users</a>
                        <h6 class="mb-0">{{ $total_user_count }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-cyan rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="gridicons:multiple-users" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Agent Deposit -->
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-2 h-100">
            <div class="card-body p-20">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Total Agent Deposit</p>
                        <h6 class="mb-0">{{ $agent_total_deposite }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-purple rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="fa-solid:award" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Deposit -->
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-3 h-100">
            <div class="card-body p-20">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Total Deposit</p>
                        <h6 class="mb-0">{{ $total_deposit }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-info rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="fluent:people-20-filled" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Withdraw -->
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-4 h-100">
            <div class="card-body p-20">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Total Withdraw</p>
                        <h6 class="mb-0">{{ $user_total_withdraw }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-success-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:wallet-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- User Table -->
<div class="row gy-4 mt-1">
    <div class="col-xxl-12">
        <div class="card h-100">
            <div class="card-body p-24">

                <div class="table-responsive scroll-sm">
                    <table class="table bordered-table sm-table mb-0">
                        <thead>
                            <tr>
                                <th>Users Name</th>
                                <th>Registered On</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                        @foreach ($user_details as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @php
                                        $userPhoto = (!empty($item->photo) && file_exists(public_path('uploads/profile/' . $item->photo)))
                                            ? asset('uploads/profile/' . $item->photo)
                                            : asset('uploads/profile/avator.jpg');
                                    @endphp
                                    <img src="{{ $userPhoto }}"
                                         onerror="this.onerror=null;this.src='{{ asset('uploads/profile/avator.jpg') }}';"
                                         alt="{{ $item->name }}"
                                         class="w-40-px h-40-px rounded-circle me-12"
                                         style="object-fit: cover;">

                                    <div>
                                        <h6 class="text-md mb-0 fw-medium">{{ $item->name }}</h6>
                                        <span class="text-sm text-secondary-light">{{ $item->email }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Null Safe Format -->
                            <td>{{ optional($item->created_at)->format('d M, Y') ?? 'N/A' }}</td>

                            <td class="text-center">
                                <form action="{{ route('admin.block.user') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $item->id }}">

                                    <select name="action" class="form-select" onchange="submitAction(this)">
                                        <option value="" hidden>Choose Action</option>
                                        <option value="block">Block</option>
                                        <option value="unblock">Unblock</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>

                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function submitAction(select) {
    if (select.value) {
        select.form.submit();
    }
}
</script>

@endsection
