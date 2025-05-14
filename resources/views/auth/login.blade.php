<!DOCTYPE html>
<html lang="en">

  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Sugs Lloyd Ltd | Admin Panel</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="{{ asset("vendors/mdi/css/materialdesignicons.min.css") }}">
    <link rel="stylesheet" href="{{ asset("vendors/ti-icons/css/themify-icons.css") }}">
    <link rel="stylesheet" href="{{ asset("vendors/css/vendor.bundle.base.css") }}">
    <!-- inject:css -->
    <link rel="stylesheet" href="{{ asset("css/vertical-layout-light/style.css") }}">
    <!-- endinject -->
    <link rel="shortcut icon" href="{{ asset("images/favicon.png") }}" />
  </head>

  <body>
    <div class="container-scroller">
      <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper d-flex align-items-center auth px-0">
          <div class="row w-100 mx-0">
            <div class="col-lg-4 mx-auto">
              <div class="auth-form-light px-sm-5 px-4 py-5 text-left">
                <div class="brand-logo">
                  <img src="https://sugs-assets.s3.ap-south-1.amazonaws.com/logo.png" alt="logo">
                </div>
                <h4>Hello! Let's get started</h4>
                <h6 class="fw-light">Sign in to continue.</h6>
                <form class="pt-3" method="POST" action="{{ route("login") }}">
                  @csrf
                  <div class="form-group">
                    <input id="email" type="email"
                      class="form-control form-control-lg @error("email") is-invalid @enderror" name="email"
                      value="{{ old("email") }}" placeholder=admin@example.com required autocomplete="email" autofocus>
                    @error("email")
                      <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                      </span>
                    @enderror
                  </div>
                  <div class="form-group position-relative">
                    <input type="password" name="password" class="form-control form-control-lg" id="password"
                      placeholder="Password" autocomplete="current-password" required>
                    <span class="position-absolute top-50 translate-middle-y end-0 me-3" style="cursor: pointer;"
                      onclick="togglePasswordVisibility()">
                      <i id="password-toggle-icon" class="mdi mdi-eye"></i>
                    </span>
                    @error("password")
                      <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                      </span>
                    @enderror
                  </div>

                  {{-- <div class="form-group ml-4"> --}}
                  <div class="form-check mx-sm-1">

                    <label class="form-check-label" for="remember">
                      <input type="checkbox" name="remember" class="form-check-input" id="remember"
                        {{ old("remember") ? "checked" : "" }}>
                      Keep me signed in
                    </label>

                  </div>

                  {{-- </div> --}}
                  <div class="mt-3">
                    <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">
                      SIGN IN
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <!-- content-wrapper ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="{{ asset("vendors/js/vendor.bundle.base.js") }}"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="{{ asset("vendors/bootstrap-datepicker/bootstrap-datepicker.min.js") }}"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="{{ asset("js/off-canvas.js") }}"></script>
    <script src="{{ asset("js/hoverable-collapse.js") }}"></script>
    <script src="{{ asset("js/template.js") }}"></script>
    <script src="{{ asset("js/settings.js") }}"></script>
    <script src="{{ asset("js/todolist.js") }}"></script>
    <script>
      function togglePasswordVisibility() {
        const passwordField = document.getElementById('password');
        const toggleIcon = document.getElementById('password-toggle-icon');

        if (passwordField.type === 'password') {
          passwordField.type = 'text';
          toggleIcon.classList.remove('mdi-eye');
          toggleIcon.classList.add('mdi-eye-off');
        } else {
          passwordField.type = 'password';
          toggleIcon.classList.remove('mdi-eye-off');
          toggleIcon.classList.add('mdi-eye');
        }
      }
    </script>

    <!-- endinject -->
  </body>

</html>
