<!DOCTYPE html>
<html lang="en">

  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ $title ?? "Sugs Lloyd Ltd | Admin Panel" }}</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="{{ asset("vendors/feather/feather.css") }}">
    <link rel="stylesheet" href="{{ asset("vendors/mdi/css/materialdesignicons.min.css") }}">
    <link rel="stylesheet" href="{{ asset("vendors/ti-icons/css/themify-icons.css") }}">
    <link rel="stylesheet" href="{{ asset("vendors/typicons/typicons.css") }}">
    <link rel="stylesheet" href="{{ asset("vendors/simple-line-icons/css/simple-line-icons.css") }}">
    <link rel="stylesheet" href="{{ asset("vendors/css/vendor.bundle.base.css") }}">
    <link rel="stylesheet" href="{{ asset("vendors/select2/select2.min.css") }}">
    <link rel="stylesheet" href="{{ asset("vendors/select2-bootstrap-theme/select2-bootstrap.min.css") }}">

    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">

    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="{{ asset("css/vertical-layout-light/style.css") }}">
    <!-- endinject -->
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
    <!-- End plugin js for this page -->

    <!-- inject:js -->
    <script src="{{ asset("js/off-canvas.js") }}"></script>
    <script src="{{ asset("js/hoverable-collapse.js") }}"></script>
    <script src="{{ asset("js/template.js") }}"></script>

    <!-- endinject -->

    <!-- Custom js for this page -->
    <script src="{{ asset("js/dashboard.js") }}"></script>
    <script src="{{ asset("js/Chart.roundedBarCharts.js") }}"></script>
    <!-- End custom js for this page -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack("scripts") <!-- For page-specific scripts -->

  </body>

</html>
