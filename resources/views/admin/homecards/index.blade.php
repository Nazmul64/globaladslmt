@extends('admin.master')

@section('content')
<div class="dashboard-main-body">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">App Home Grid Cards Settings</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                    Dashboard
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">Home Cards</li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-24" role="alert">
            <i class="ri-checkbox-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card basic-data-table">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h5 class="card-title mb-0">Customize Mobile App Home Grid Cards</h5>
                <p class="text-sm text-secondary-light mb-0">Upload custom icon images OR paste FontAwesome HTML icon codes/classes & customize card background colors.</p>
            </div>
            <form action="{{ route('admin.homecards.reset') }}" method="POST" onsubmit="return confirm('Are you sure you want to reset all cards to default values?');">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-2">
                    <i class="ri-refresh-line"></i> Reset All Defaults
                </button>
            </form>
        </div>

        <div class="card-body p-24">
            <div class="row g-4">
                @foreach($cards as $card)
                    <div class="col-xl-4 col-md-6">
                        <div class="card border border-neutral-200 h-100 shadow-sm">
                            <div class="card-header bg-neutral-50 py-12 px-16 d-flex align-items-center justify-content-between border-bottom border-neutral-200">
                                <span class="badge bg-primary-100 text-primary-600 fw-semibold text-sm">#{{ $card->sort_order }} - {{ $card->key }}</span>
                                <span class="badge {{ $card->is_active ? 'bg-success-100 text-success-600' : 'bg-danger-100 text-danger-600' }} text-xs">
                                    {{ $card->is_active ? 'Active' : 'Hidden' }}
                                </span>
                            </div>
                            
                            <div class="card-body p-16">
                                <!-- Live Card Preview -->
                                <div class="mb-3 text-center">
                                    <span class="text-xs fw-semibold text-secondary-light mb-2 d-block">App Card Live Preview</span>
                                    <div class="d-inline-flex flex-column align-items-center justify-content-center p-3 rounded-4 shadow-sm"
                                         id="card-preview-{{ $card->id }}"
                                         style="width: 110px; height: 110px; background-color: {{ $card->bg_color }}; transition: all 0.3s ease; border-radius: 16px;">
                                        
                                        <div class="rounded-circle p-2 d-flex align-items-center justify-content-center mb-1"
                                             style="width: 44px; height: 44px; background-color: rgba(255,255,255,0.25);">
                                            
                                            <!-- Image Icon Preview -->
                                            <img id="image-preview-{{ $card->id }}" 
                                                 src="{{ $card->image_url ?? '' }}" 
                                                 alt="icon" 
                                                 style="max-width: 28px; max-height: 28px; object-fit: contain; {{ ($card->icon_type === 'image' && $card->image_url) ? '' : 'display: none;' }}">
                                            
                                            <!-- Font Icon Code Preview -->
                                            <span id="icon-preview-{{ $card->id }}" 
                                                  style="color: {{ $card->icon_color }}; font-size: 24px; {{ ($card->icon_type === 'image' && $card->image_url) ? 'display: none;' : '' }}">
                                                {!! $card->formatted_icon_html !!}
                                            </span>
                                        </div>

                                        <span class="text-xs fw-bold text-truncate w-100" id="title-preview-{{ $card->id }}" style="color: {{ $card->text_color ?? '#FFFFFF' }};">{{ $card->title }}</span>
                                    </div>
                                </div>

                                <!-- Form for Updating -->
                                <form action="{{ route('admin.homecards.update', $card->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label class="form-label text-xs fw-semibold">Card Title</label>
                                        <input type="text" name="title" value="{{ $card->title }}" class="form-control form-control-sm"
                                               oninput="document.getElementById('title-preview-{{ $card->id }}').innerText = this.value" required>
                                    </div>

                                    <!-- Icon Type Selector -->
                                    <div class="mb-3">
                                        <label class="form-label text-xs fw-semibold d-block">Icon Source Mode</label>
                                        <div class="btn-group btn-group-sm w-100" role="group">
                                            <input type="radio" class="btn-check" name="icon_type" id="type_code_{{ $card->id }}" value="code" {{ $card->icon_type === 'code' ? 'checked' : '' }}
                                                   onchange="toggleIconMode({{ $card->id }}, 'code')">
                                            <label class="btn btn-outline-primary" for="type_code_{{ $card->id }}">
                                                <i class="fa fa-code me-1"></i> HTML / Class Code
                                            </label>

                                            <input type="radio" class="btn-check" name="icon_type" id="type_image_{{ $card->id }}" value="image" {{ $card->icon_type === 'image' ? 'checked' : '' }}
                                                   onchange="toggleIconMode({{ $card->id }}, 'image')">
                                            <label class="btn btn-outline-primary" for="type_image_{{ $card->id }}">
                                                <i class="fa fa-image me-1"></i> Image Upload
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Code Input Box -->
                                    <div class="mb-3" id="code-container-{{ $card->id }}" style="{{ $card->icon_type === 'code' ? '' : 'display: none;' }}">
                                        <label class="form-label text-xs fw-semibold">Icon Code / HTML Tag / Class Name</label>
                                        <input type="text" name="icon" value="{{ $card->icon }}" class="form-control form-control-sm"
                                               placeholder='e.g. <i class="fa fa-tasks"></i> or fa-tasks or task_alt'
                                               oninput="updateIconCodePreview({{ $card->id }}, this.value)">
                                        <small class="text-xs text-muted mt-1 d-block">Paste HTML like <code>&lt;i class="fa fa-tasks"&gt;&lt;/i&gt;</code>, FontAwesome class <code>fa-tasks</code>, or Material Icon name <code>task_alt</code>.</small>
                                    </div>

                                    <!-- Image Upload Box -->
                                    <div class="mb-3" id="image-container-{{ $card->id }}" style="{{ $card->icon_type === 'image' ? '' : 'display: none;' }}">
                                        <label class="form-label text-xs fw-semibold">Upload Icon Image (PNG / SVG / JPG)</label>
                                        <input type="file" name="image" class="form-control form-control-sm" accept="image/*"
                                               onchange="previewUploadedImage({{ $card->id }}, this)">
                                        @if($card->image)
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_img_{{ $card->id }}">
                                                <label class="form-check-label text-xs text-danger" for="remove_img_{{ $card->id }}">Remove current uploaded image</label>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Colors Selection -->
                                    <div class="row g-2 mb-3">
                                        <div class="col-4">
                                            <label class="form-label text-xs fw-semibold">Card Bg</label>
                                            <div class="d-flex align-items-center gap-1">
                                                <input type="color" value="{{ $card->bg_color }}" class="form-control form-control-color form-control-sm p-0" style="width: 28px; height: 28px;"
                                                       onchange="document.getElementById('bg_color_input_{{ $card->id }}').value = this.value; document.getElementById('card-preview-{{ $card->id }}').style.backgroundColor = this.value;">
                                                <input type="text" name="bg_color" id="bg_color_input_{{ $card->id }}" value="{{ $card->bg_color }}" class="form-control form-control-sm px-1 text-xs"
                                                       oninput="document.getElementById('card-preview-{{ $card->id }}').style.backgroundColor = this.value" required>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label text-xs fw-semibold">Icon Color</label>
                                            <div class="d-flex align-items-center gap-1">
                                                <input type="color" value="{{ $card->icon_color }}" class="form-control form-control-color form-control-sm p-0" style="width: 28px; height: 28px;"
                                                       onchange="document.getElementById('icon_color_input_{{ $card->id }}').value = this.value; document.getElementById('icon-preview-{{ $card->id }}').style.color = this.value;">
                                                <input type="text" name="icon_color" id="icon_color_input_{{ $card->id }}" value="{{ $card->icon_color }}" class="form-control form-control-sm px-1 text-xs"
                                                       oninput="document.getElementById('icon-preview-{{ $card->id }}').style.color = this.value" required>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label text-xs fw-semibold">Title Color</label>
                                            <div class="d-flex align-items-center gap-1">
                                                <input type="color" value="{{ $card->text_color ?? '#FFFFFF' }}" class="form-control form-control-color form-control-sm p-0" style="width: 28px; height: 28px;"
                                                       onchange="document.getElementById('text_color_input_{{ $card->id }}').value = this.value; document.getElementById('title-preview-{{ $card->id }}').style.color = this.value;">
                                                <input type="text" name="text_color" id="text_color_input_{{ $card->id }}" value="{{ $card->text_color ?? '#FFFFFF' }}" class="form-control form-control-sm px-1 text-xs"
                                                       oninput="document.getElementById('title-preview-{{ $card->id }}').style.color = this.value" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between mt-3 pt-2 border-top">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active_{{ $card->id }}" {{ $card->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label text-xs" for="is_active_{{ $card->id }}">App Visible</label>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm px-3">
                                            <i class="ri-save-line me-1"></i> Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- ✅ Icon CSS Libraries -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<script>
function toggleIconMode(cardId, mode) {
    const codeContainer = document.getElementById('code-container-' + cardId);
    const imageContainer = document.getElementById('image-container-' + cardId);
    const iconPreview = document.getElementById('icon-preview-' + cardId);
    const imagePreview = document.getElementById('image-preview-' + cardId);

    if (mode === 'code') {
        codeContainer.style.display = 'block';
        imageContainer.style.display = 'none';
        iconPreview.style.display = 'inline-block';
        imagePreview.style.display = 'none';
    } else {
        codeContainer.style.display = 'none';
        imageContainer.style.display = 'block';
        if (imagePreview.src && imagePreview.src.length > 5) {
            imagePreview.style.display = 'inline-block';
            iconPreview.style.display = 'none';
        }
    }
}

function updateIconCodePreview(cardId, value) {
    const iconPreview = document.getElementById('icon-preview-' + cardId);
    let val = value.trim();

    if (!val) {
        iconPreview.innerHTML = '<i class="material-icons">help</i>';
        return;
    }

    if (val.includes('<i') || val.includes('<span')) {
        iconPreview.innerHTML = val;
    } else if (val.includes('fa-') || val.includes('fa ') || val.includes('bi-') || val.includes('ri-')) {
        iconPreview.innerHTML = '<i class="' + val + '"></i>';
    } else {
        iconPreview.innerHTML = '<i class="material-icons">' + val + '</i>';
    }
}

function previewUploadedImage(cardId, input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const imagePreview = document.getElementById('image-preview-' + cardId);
            const iconPreview = document.getElementById('icon-preview-' + cardId);
            imagePreview.src = e.target.result;
            imagePreview.style.display = 'inline-block';
            iconPreview.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
