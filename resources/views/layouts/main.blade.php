<!DOCTYPE html>
<html lang="en">

  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ $title ?? "Sugs Lloyd Ltd | Admin Panel" }}</title>

    <!-- plugins: icon css -->
    <link rel="stylesheet" href="{{ asset("vendors/mdi/css/materialdesignicons.min.css") }}">
    <link rel="stylesheet" href="{{ asset("vendors/ti-icons/css/themify-icons.css") }}">
    <link rel="stylesheet" href="{{ asset("vendors/simple-line-icons/css/simple-line-icons.css") }}">

    {{-- datatables css --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

    {{-- base css --}}
    <link rel="stylesheet" href="{{ asset("vendors/css/vendor.bundle.base.css") }}">
    <link rel="stylesheet" href="{{ asset("css/vertical-layout-light/style.css") }}">

    {{-- select2 css --}}
    <link rel="stylesheet" href="{{ asset("vendors/select2/select2.min.css") }}">
    <link rel="stylesheet" href="{{ asset("vendors/select2-bootstrap-theme/select2-bootstrap.min.css") }}">

    <link rel="shortcut icon" href="{{ asset("images/favicon.png") }}">
    @stack("styles") <!-- For page-specific styles -->
  </head>

  <body>
    <div class="container-scroller">
      @include("partials.header")
      <div class="container-fluid page-body-wrapper">
        @include("partials.sidebar")
        <div class="main-panel">
          @yield("content")
        </div>
      </div>
      @include("partials.footer")
    </div>
    <!-- plugins:js -->
    <script src="{{ asset("vendors/js/vendor.bundle.base.js") }}"></script>
    <!-- endinject -->

    <!-- Plugin js for this page -->
    <script src="{{ asset("vendors/chart.js/Chart.min.js") }}"></script>
    <script src="{{ asset("vendors/bootstrap-datepicker/bootstrap-datepicker.min.js") }}"></script>
    <script src="{{ asset("vendors/progressbar.js/progressbar.min.js") }}"></script>
    <script src="{{ asset("vendors/select2/select2.min.js") }}"></script>
    <script src="{{ asset("js/off-canvas.js") }}"></script>
    <script src="{{ asset("js/hoverable-collapse.js") }}"></script>
    <script src="{{ asset("js/template.js") }}"></script>
    {{-- <script src="{{ asset("js/select2.js") }}"></script> --}}

    <!-- endinject -->

    <!-- Custom js for this page -->
    <script src="{{ asset("js/dashboard.js") }}"></script>
    <script src="{{ asset("js/Chart.roundedBarCharts.js") }}"></script>
    <!-- End custom js for this page -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.6.3/js/dataTables.select.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack("scripts") <!-- For page-specific scripts -->

  </body>

</html>
