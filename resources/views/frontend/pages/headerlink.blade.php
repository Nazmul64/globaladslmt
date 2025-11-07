<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Global Money Ltd</title>

    <!-- ✅ CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/custom.css') }}">

    <!-- ✅ START.IO SDK (Load First) -->
    <script src="https://s.start.io/js/sdk/v1/start.min.js" async></script>
    <script src="https://cdn.start.io/adunit.js"></script>
    <script type="text/javascript" src="https://cdn.start.io/sdk/v1/start.min.js"></script>
</head>

<body>

    <!-- ✅ START.IO Banner -->
    <div id="startio-banner"></div>

    @php
        use App\Models\Ad;
        $start_io = Ad::first();
    @endphp

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // ✅ Start.io banner initialization
            startio.display('startio-banner', {
                appId: '{{ $start_io->code ?? "default_app_id" }}',
                placementId: '{{ $start_io->code ?? "default_placement_id" }}'
            });
        });
    </script>

    <!-- ✅ JS Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- ✅ Toastr Notifications -->
    @if (Session::has('success') || Session::has('error'))
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
            });
        </script>
    @endif

</body>
</html>
