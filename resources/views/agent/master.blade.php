<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agent</title>
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
  <!-- Favicon -->


<!-- CSS Libraries -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

<!-- JS Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

@if (Session::has('success') || Session::has('error'))
<script>
    $(document).ready(function() {
        // Toastr options
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-right",
            timeOut: 5000
        };

        // Display error message
        @if (Session::has('error'))
            toastr.error("{{ Session::get('error') }}");
        @endif

        // Display success message
        @if (Session::has('success'))
            toastr.success("{{ Session::get('success') }}");
        @endif
    });
</script>
@endif




</head>

<body>

  <!-- Theme Customization Structure Start -->
<div class="body-overlay"></div>
<!-- Theme Customization Structure End -->
@include('agent.pages.siderbar')

<main class="dashboard-main">
@include('agent.pages.header')




  <div class="dashboard-main-body">
       @yield('content')
  </div>

@include('agent.pages.footer')
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

</body>
</html>
