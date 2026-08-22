@extends('admin.master')

@section('content')

<div class="card shadow-sm p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-palette me-2 text-primary"></i> Add New Theme
        </h4>

        <a href="{{ route('themechange.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <form action="{{ route('themechange.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label class="form-label fw-semibold">Select Color</label>

            <div class="d-flex align-items-center gap-3">
                <input
                    type="color"
                    id="colorPicker"
                    name="color_code"
                    class="form-control form-control-color shadow-sm"
                    value="#ff0000"
                    style="width: 60px; height: 60px; padding: 0; border-radius: 8px;"
                >

                <input
                    type="text"
                    id="colorValue"
                    class="form-control shadow-sm"
                    value="#ff0000"
                    readonly
                    style="max-width: 150px;"
                >
            </div>
        </div>

        <button class="btn btn-success px-4 mt-2">
            <i class="bi bi-check-circle"></i> Save Theme
        </button>

    </form>
</div>

{{-- Script for Live Color Code Update --}}
<script>
    document.getElementById('colorPicker').addEventListener('input', function () {
        document.getElementById('colorValue').value = this.value;
    });
</script>

@endsection
