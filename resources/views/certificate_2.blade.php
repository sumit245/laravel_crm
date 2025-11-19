<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Search - Magnitude Management Services Pvt. Ltd.</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
        }

        /* Top Welcome Bar */
        .top-bar {
            background: linear-gradient(90deg, #4a9fd8 0%, #5eb3e4 100%);
            color: white;
            padding: 12px 0;
            text-align: center;
            font-size: 15px;
            font-style: italic;
        }

        /* Header Styles */
        .header {
            background-color: white;
            padding: 20px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-text {
            font-size: 48px;
            font-weight: 700;
            color: #3e5c76;
            font-family: Arial, sans-serif;
        }

        .logo-text .mms {
            color: #f39c12;
        }

        .accreditation-badge {
            width: 80px;
            height: auto;
        }

        /* Navigation Menu */
        .navigation {
            background-color: #3e5c76;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .nav-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-menu li {
            position: relative;
        }

        .nav-menu li a {
            display: block;
            color: white;
            padding: 18px 20px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            transition: background-color 0.3s ease;
        }

        .nav-menu li a:hover {
            background-color: #2d4458;
        }

        .get-quote-btn {
            background-color: #f7b924;
            color: #333;
            padding: 18px 30px;
            text-decoration: none;
            font-weight: 700;
            text-transform: uppercase;
            transition: all 0.3s ease;
            display: inline-block;
            font-size: 14px;
        }

        .get-quote-btn:hover {
            background-color: #f39c12;
        }

        /* Hero Banner */
        .hero-banner {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                url('https://mmscertification.com/images/slide2-2.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }

        .hero-content h1 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
            text-transform: capitalize;
        }

        .breadcrumb-hero {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            font-size: 16px;
        }

        .breadcrumb-hero a {
            color: #f7b924;
            text-decoration: none;
        }

        .breadcrumb-hero span {
            color: white;
        }

        .breadcrumb-hero .separator {
            color: #f7b924;
        }

        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .certificate-card {
            background: white;
            border: 1px solid #e0e0e0;
            overflow: hidden;
            margin-bottom: 30px;
        }

        .certificate-table {
            width: 100%;
            border-collapse: collapse;
        }

        .certificate-table tr {
            border-bottom: 1px solid #e0e0e0;
        }

        .certificate-table tr:last-child {
            border-bottom: none;
        }

        .certificate-table td {
            padding: 20px 30px;
            vertical-align: top;
            text-align: left;
        }

        .certificate-table td:first-child {
            font-weight: 700;
            color: #000;
            width: 30%;
            background-color: #f5f5f5;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        .certificate-table td:last-child {
            width: 70%;
            font-size: 15px;
            color: #000;
            background-color: white;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(90deg, #4a9fd8 0%, #5eb3e4 100%);
            color: white;
            padding: 40px 0;
        }

        .cta-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cta-section h3 {
            font-size: 24px;
            line-height: 34px;
            margin: 0;
            font-style: italic;
            font-weight: 400;
            color: #ffffff;
            font-family: 'Open Sans', sans-serif;
        }

        .cta-button {
            background-color: #f7b924;
            color: #333;
            padding: 15px 40px;
            text-decoration: none;
            font-weight: 700;
            display: inline-block;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-size: 16px;
        }

        .cta-button:hover {
            background-color: #f39c12;
        }

        /* Footer */
        .footer {
            background-color: #2d3436;
            color: #b2bec3;
            padding: 50px 0 0;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr;
            gap: 60px;
            margin-bottom: 40px;
        }

        .footer-logo-section {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .footer-logo-container {
            background-color: white;
            padding: 20px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            width: fit-content;
        }

        .footer-logo-text {
            font-size: 36px;
            font-weight: 700;
            color: #3e5c76;
        }

        .footer-logo-text .mms {
            color: #f39c12;
        }

        .footer-description {
            line-height: 1.8;
            color: #b2bec3;
            font-size: 14px;
        }

        .footer-section h4 {
            color: white;
            margin-bottom: 25px;
            font-size: 20px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-section h4:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 2px;
            background-color: #4a9fd8;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-section ul li {
            margin-bottom: 12px;
            padding-left: 20px;
            position: relative;
            color: #b2bec3;
            font-size: 14px;
        }

        .footer-section ul li:before {
            content: "❯";
            position: absolute;
            left: 0;
            color: #4a9fd8;
            font-weight: bold;
        }

        .contact-info-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 20px;
        }

        .contact-icon {
            color: #4a9fd8;
            font-size: 18px;
            min-width: 20px;
        }

        .contact-text {
            color: #b2bec3;
            font-size: 14px;
            line-height: 1.6;
        }

        .footer-bottom {
            text-align: center;
            padding: 25px 0;
            border-top: 1px solid #404447;
            color: #888;
            font-size: 14px;
            margin-top: 20px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }

            .nav-menu {
                flex-direction: column;
                width: 100%;
            }

            .nav-content {
                flex-direction: column;
            }

            .hero-content h1 {
                font-size: 32px;
            }

            .certificate-table td {
                display: block;
                width: 100% !important;
                padding: 15px 20px;
            }

            .certificate-table td:first-child {
                background-color: #667eea;
                color: white;
                border-radius: 5px 5px 0 0;
            }

            .cta-content {
                flex-direction: column;
                padding: 30px 20px;
                gap: 20px;
            }

            .cta-content h3 {
                font-size: 24px;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .certificate-card {
            animation: fadeIn 0.6s ease-out;
        }
    </style>
</head>

<body>
    <!-- Top Welcome Bar -->
    <div class="top-bar">
        Welcome to Magnitude Management Services Pvt. Ltd.
    </div>

    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="logo-container">
                <img src="https://mmscertification.com/images/logo.png" alt="MMS Certification Logo"
                    style="height: 80px; width: auto;">
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <div class="navigation">
        <div class="nav-content">
            <ul class="nav-menu">
                <li><a href="#">HOME</a></li>
                <li><a href="#">ABOUT US</a></li>
                <li><a href="#">SERVICES</a></li>
                <li><a href="#">APPLICATION</a></li>
                <li><a href="#">CLIENTS</a></li>
                <li><a href="#">FALSE CLAIMS OF ACCREDITATION</a></li>
                <li><a href="#">CONTACT US</a></li>
            </ul>
            <a href="#" class="get-quote-btn">GET A QUOTE</a>
        </div>
    </div>

    <!-- Hero Banner -->
    <div class="hero-banner">
        <div class="hero-content">
            <h1>Client Search</h1>
            <div class="breadcrumb-hero">
                <a href="#">Home</a>
                <span class="separator">/</span>
                <a href="#">Client</a>
                <span class="separator">/</span>
                <span>Client Search</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Certificate Card -->
        <div class="certificate-card">
            <table class="certificate-table">
                <tr>
                    <td>Company Name</td>
                    <td>VYNA ELECTRIC PRIVATE LIMITED</td>
                </tr>
                <tr>
                    <td>Address</td>
                    <td>PLOT NO. -C92,1ST FLOOR , SECTOR-4 NOIDA,GAUTAM BUDDHA
                        N</td>
                </tr>
                <tr>
                    <td>Certificate Number</td>
                    <td>25MEQUW11</td>
                </tr>
                <tr>
                    <td>Standard</td>
                    <td>ISO 9001:2015</td>
                </tr>
                <tr>
                    <td>Scope</td>
                    <td>PROVISION OF INFORMATION SECURITY SYSTEM FOR DESIGN, MARKETING AND SALES, INSTALLATION, TESTING
                        AND COMMISSIONING OPERATION AND MAINTENANCE OF LT/HT ELECTRICAL EQUIPMENT, TRANSFORMER, POLES,
                        CABLE, ENERGY METER, FOR 33KV, 11KV POWER DISTRIBUTION NETWORK, AUTORECLOSER, FAULT PASSAGE
                        INDICATOR, BATTERY SWAPPING STATIONS, SOLAR PV PROJECTS, CIVIL CONSTRUCTION PROJECTS, WATER /
                        IRRIGATION-PROJECTS, LED LIGHT PRODUCT, ERECTION AND INSTALLATION OF LED HIGH MAST LIGHT, LED
                        MINI MAST LIGHT AND LED SOLAR STREET LIGHT.</td>
                </tr>
                <tr>
                    <td>Date of Registration</td>
                    <td>11/08/2025</td>
                </tr>
                <tr>
                    <td>Date of Issue</td>
                    <td>11/08/2025</td>
                </tr>
                <tr>
                    <td>Date of Expiry</td>
                    <td>10/08/2028</td>
                </tr>
                <tr>
                    <td>Date of First Surveillance</td>
                    <td>11/07/2026</td>
                </tr>
                <tr>
                    <td>Date of Second Surveillance</td>
                    <td>11/07/2027</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="cta-section">
        <div class="cta-content">
            <h3>Get A Certificate Now</h3>
            <a href="#" class="cta-button">GET A QUOTE</a>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-content">
            <div class="footer-logo-section">
                <div class="footer-logo-container">
                    <img src="https://mmscertification.com/images/logo.png" alt="MMS Certification Logo"
                        style="height: 60px; width: auto;">
                </div>
                <p class="footer-description">
                    Magnitude Management Services is the service provider in the field of ISO certification. MMS
                    provides
                    the Certification activities in most independent, Impartial and without any pressure to its client
                    with the value addition.
                </p>
            </div>
            <div class="footer-section">
                <h4>Our Services</h4>
                <ul>
                    <li>ISO 9001:2015</li>
                    <li>ISO 14001:2015</li>
                    <li>ISO 45001:2018</li>
                    <li>ISO 27001:2022</li>
                    <li>ISO 22000:2018</li>
                    <li>ISO 20000-1:2018</li>
                    <li>ISO 50001:2018</li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Contact Info</h4>
                <div class="contact-info-item">
                    <span class="contact-icon">📍</span>
                    <span class="contact-text">A-60, 3rd Floor (T3), Sector 02, Noida, Gautam Buddha Nagar, Uttar
                        Pradesh-201301</span>
                </div>
                <div class="contact-info-item">
                    <span class="contact-icon">📞</span>
                    <span class="contact-text">+91 981 050 2643</span>
                </div>
                <div class="contact-info-item">
                    <span class="contact-icon">✉️</span>
                    <span class="contact-text">info@mmscertification.com</span>
                </div>
                <div class="contact-info-item">
                    <span class="contact-icon">🌐</span>
                    <span class="contact-text">www.mmscertification.com</span>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2017-2024 All Rights Reserved</p>
        </div>
    </div>
</body>

</html>
