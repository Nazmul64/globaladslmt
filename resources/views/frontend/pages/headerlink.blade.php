<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Global money Ltd</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
   <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    {{-- START.IO SDK - Load First --}}
  <script src="https://s.start.io/js/sdk/v1/start.min.js" async></script>
<script src="https://cdn.start.io/adunit.js"></script>

<div id="startio-banner"></div>

<script>
    @php
        use App\Models\Ad; // ✅ Correct namespace (Models not Model)
        $start_io = App\Models\Ad::first(); // ✅ Missing semicolon
    @endphp

    // ✅ Use the app ID or placement ID dynamically if available
    startio.display('startio-banner', {
        appId: '{{ $start_io->code ?? "default_app_id" }}',
        placementId: '{{ $start_io->code ?? "default_placement_id" }}'
    });
</script>


    <!-- Bootstrap & FontAwesome -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">


   <link rel="stylesheet" href="{{asset('frontend')}}/assets/css/custom.css">
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
