<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted - Sugs Lloyd Ltd</title>

    {{-- base css --}}
    <link rel="stylesheet" href="{{ asset("css/vertical-layout-light/style.css") }}">
    <link rel="shortcut icon" href="{{ asset("images/favicon.png") }}">
    
    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f0f0;
            color: #202124;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .success-container {
            max-width: 600px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
        }
        
        .success-icon {
            font-size: 80px;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        
        .success-title {
            font-size: 28px;
            font-weight: 500;
            color: #202124;
            margin-bottom: 16px;
        }
        
        .success-message {
            font-size: 16px;
            color: #5f6368;
            margin-bottom: 30px;
        }
        
        .btn {
            font-weight: 500;
            padding: 10px 24px;
            border-radius: 4px;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background-color: #673ab7;
            border-color: #673ab7;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #5e35b1;
            border-color: #5e35b1;
        }
        
        .logo {
            max-height: 80px;
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <div class="success-container">
        <img src="{{ asset('images/logo.png') }}" alt="Sugs Lloyd Ltd Logo" class="logo">

        {{-- Success Icon --}}
        @if(session('error'))
            <div class="error-icon text-danger">
                <i class="fas fa-exclamation-triangle fa-4x"></i>
            </div>
        @else
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
        @endif

        {{-- Title --}}
        <h1 class="success-title">
            @if(session('error'))
                Something Went Wrong
            @else
                Application Submitted Successfully!
            @endif
        </h1>

        {{-- Message --}}
        <p class="success-message">
            @if(session('error'))
                {{ session('error') }}
            @else
                Thank you for completing your employee onboarding application. We have received your information and will review it shortly. You will be contacted for the next steps.
            @endif
        </p>

        {{-- Optional: Back to form link --}}
        @if(session('error'))
            <div class="text-center mt-3">
                <a href="{{ route('hrm.apply') }}" class="btn btn-warning">
                    <i class="fas fa-arrow-left me-1"></i> Go Back to Form
                </a>
            </div>
        @endif
    </div>
</body>

</html>