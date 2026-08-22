@extends('admin.master')

@section('content')

<div class="card shadow-sm p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-brush-fill text-warning me-2"></i> Edit Theme
        </h4>

        <a href="{{ route('themechange.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <form action="{{ route('themechange.update', $theme->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="form-label fw-semibold">Select Color</label>

            <div class="d-flex align-items-center gap-3">
                <!-- Color Picker -->
                <input
                    type="color"
                    id="colorPicker"
                    name="color_code"
                    class="form-control form-control-color shadow-sm"
                    value="{{ $theme->color_code }}"
                    style="width: 60px; height: 60px; padding: 0; border-radius: 8px;"
                >

                <!-- Color Code Display -->
                <input
                    type="text"
                    id="colorValue"
                    class="form-control shadow-sm"
                    value="{{ $theme->color_code }}"
                    readonly
                    style="max-width: 150px;"
                >
            </div>
        </div>

        <button class="btn btn-primary px-4 mt-2">
            <i class="bi bi-check-circle"></i> Update Theme
        </button>
    </form>
</div>

{{-- Live Update Script --}}
<script>
    document.getElementById('colorPicker').addEventListener('input', function () {
        document.getElementById('colorValue').value = this.value;
    });
</script>

@endsection
