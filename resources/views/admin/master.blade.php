<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin</title>
  <link rel="icon" type="{{asset('admin')}}/image/png" href="assets/images/favicon.png" sizes="16x16">
  <!-- remix icon font css  -->
  <link rel="stylesheet" href="{{asset('admin')}}/assets/css/remixicon.css">
  <!-- BootStrap css -->
  <link rel="stylesheet" href="{{asset('admin')}}/assets/css/lib/bootstrap.min.css">
  <!-- Apex Chart css -->
  <link rel="stylesheet" href="{{asset('admin')}}/assets/css/lib/apexcharts.css">
  <!-- Data Table css -->
  <link rel="stylesheet" href="{{asset('admin')}}/assets/css/lib/dataTables.min.css">
  <!-- Text Editor css -->
  <link rel="stylesheet" href="{{asset('admin')}}/assets/css/lib/editor-katex.min.css">
  <link rel="stylesheet" href="{{asset('admin')}}/assets/css/lib/editor.atom-one-dark.min.css">
  <link rel="stylesheet" href="{{asset('admin')}}/assets/css/lib/editor.quill.snow.css">
  <!-- Date picker css -->
  <link rel="stylesheet" href="{{asset('admin')}}/assets/css/lib/flatpickr.min.css">
  <!-- Calendar css -->
  <link rel="stylesheet" href="{{asset('admin')}}/assets/css/lib/full-calendar.css">
  <!-- Vector Map css -->
  <link rel="stylesheet" href="{{asset('admin')}}/assets/css/lib/jquery-jvectormap-2.0.5.css">
  <!-- Popup css -->
  <link rel="stylesheet" href="{{asset('admin')}}/assets/css/lib/magnific-popup.css">
  <!-- Slick Slider css -->
  <link rel="stylesheet" href="{{asset('admin')}}/assets/css/lib/slick.css">
  <!-- prism css -->
  <link rel="stylesheet" href="{{asset('admin')}}/assets/css/lib/prism.css">
  <!-- file upload css -->
  <link rel="stylesheet" href="{{asset('admin')}}/assets/css/lib/file-upload.css">

  <link rel="stylesheet" href="{{asset('admin')}}/assets/css/lib/audioplayer.css">
  <!-- main css -->
  <link rel="stylesheet" href="{{asset('admin')}}/assets/css/style.css">
  <link rel="stylesheet" href="{{asset('admin')}}/assets/css/custom-admin.css">
  <!-- Favicon -->


<!-- CSS Libraries -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

<!-- JS Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    $(document).ready(function() {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-right",
            timeOut: 5000
        };

        @if (Session::has('error'))
            toastr.error("{{ Session::get('error') }}");
        @endif

        @if (Session::has('success'))
            toastr.success("{{ Session::get('success') }}");
        @endif

        @if (Session::has('info'))
            toastr.info("{{ Session::get('info') }}");
        @endif

        @if (Session::has('warning'))
            toastr.warning("{{ Session::get('warning') }}");
        @endif

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                toastr.error("{{ $error }}");
            @endforeach
        @endif
    });
</script>




</head>

<body>

  <!-- Theme Customization Structure Start -->
<div class="body-overlay"></div>
<!-- Theme Customization Structure End -->
@include('admin.pages.sidebar')

<main class="dashboard-main">
@include('admin.pages.header')




  <div class="dashboard-main-body">
       @yield('content')
  </div>

@include('admin.pages.footer')
</main>

  <!-- jQuery library js -->
  <script src="{{asset('admin')}}/assets/js/lib/jquery-3.7.1.min.js"></script>
  <!-- Bootstrap js -->
  <script src="{{asset('admin')}}/assets/js/lib/bootstrap.bundle.min.js"></script>
  <!-- Apex Chart js -->
  <script src="{{asset('admin')}}/assets/js/lib/apexcharts.min.js"></script>
  <!-- Data Table js -->
  <script src="{{asset('admin')}}/assets/js/lib/dataTables.min.js"></script>
  <!-- Iconify Font js -->
  <script src="{{asset('admin')}}/assets/js/lib/iconify-icon.min.js"></script>
  <!-- jQuery UI js -->
  <script src="{{asset('admin')}}/assets/js/lib/jquery-ui.min.js"></script>
  <!-- Vector Map js -->
  <script src="{{asset('admin')}}/assets/js/lib/jquery-jvectormap-2.0.5.min.js"></script>
  <script src="{{asset('admin')}}/assets/js/lib/jquery-jvectormap-world-mill-en.js"></script>
  <!-- Popup js -->
  <script src="{{asset('admin')}}/assets/js/lib/magnifc-popup.min.js"></script>
  <!-- Slick Slider js -->
  <script src="{{asset('admin')}}/assets/js/lib/slick.min.js"></script>
  <!-- prism js -->
  <script src="{{asset('admin')}}/assets/js/lib/prism.js"></script>
  <!-- file upload js -->
  <script src="{{asset('admin')}}/assets/js/lib/file-upload.js"></script>
  <!-- audioplayer -->
  <script src="{{asset('admin')}}/assets/js/lib/audioplayer.js"></script>

  <!-- main js -->
  <script src="{{asset('admin')}}/assets/js/app.js"></script>

<script src="{{asset('admin')}}/assets/js/homeOneChart.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<!-- Universal Image Preview & Validation for All Uploads -->
<script>
(function() {
    const validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'avif', 'jfif'];
    const maxSizeBytes = 10 * 1024 * 1024; // 10MB

    document.addEventListener('change', function(e) {
        if (!e.target || e.target.tagName !== 'INPUT' || e.target.type !== 'file') return;
        const input = e.target;
        if (!input.files || input.files.length === 0) return;

        const file = input.files[0];
        const ext = file.name.split('.').pop().toLowerCase();
        const isImage = file.type.startsWith('image/') || validExtensions.includes(ext);

        // If file input is explicitly for JSON or other non-image formats, skip image logic
        if (input.accept && input.accept.includes('.json') && !input.accept.includes('image')) return;

        if (isImage) {
            if (!validExtensions.includes(ext) && !file.type.startsWith('image/')) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Invalid image format! Supported: JPG, PNG, GIF, SVG, WEBP, BMP, AVIF, JFIF.');
                } else {
                    alert('Invalid image format! Supported: JPG, PNG, GIF, SVG, WEBP, BMP, AVIF, JFIF.');
                }
                input.value = '';
                return;
            }

            if (file.size > maxSizeBytes) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('File size (' + (file.size / (1024*1024)).toFixed(2) + ' MB) exceeds 10MB limit! Please choose a smaller file.');
                } else {
                    alert('File size exceeds 10MB limit!');
                }
                input.value = '';
                return;
            }

            // Check if page already has custom dedicated preview container
            const customPreview = document.getElementById('newPreviewContainer') ||
                                  document.getElementById(input.id + 'Preview') ||
                                  document.querySelector('[data-preview-for="' + input.id + '"]');

            if (!customPreview) {
                // Auto attach a clean preview widget below this input
                let previewBox = input.parentNode.querySelector('.auto-universal-preview');
                if (!previewBox) {
                    previewBox = document.createElement('div');
                    previewBox.className = 'auto-universal-preview mt-2 p-2 border rounded bg-white shadow-sm d-flex align-items-center gap-3';
                    previewBox.innerHTML = `
                        <img class="preview-thumb rounded" style="max-height: 80px; max-width: 120px; object-fit: contain; border: 1px solid #dee2e6;" src="" alt="Selected Preview">
                        <div class="small text-muted flex-grow-1">
                            <strong class="text-dark d-block preview-filename"></strong>
                            <span class="preview-filesize badge bg-success text-white"></span>
                            <span class="badge bg-primary text-white">Live Preview</span>
                        </div>
                    `;
                    input.parentNode.appendChild(previewBox);
                }

                const reader = new FileReader();
                reader.onload = function(evt) {
                    previewBox.querySelector('.preview-thumb').src = evt.target.result;
                    previewBox.querySelector('.preview-filename').textContent = file.name;
                    previewBox.querySelector('.preview-filesize').textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                    previewBox.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        }
    });
})();
</script>

</body>
</html>
