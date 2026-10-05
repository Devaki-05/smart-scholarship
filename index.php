<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Smart Scholarship System</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            background: #f5f7fb;
            color: #1f2937;
        }

        /* Navbar */
        .navbar {
            width: 100%;
            background: #ffffff;
            padding: 18px 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            font-size: 22px;
            font-weight: bold;
            color: #4f46e5;
        }

        .nav-buttons {
            display: flex;
            gap: 12px;
        }

        .nav-buttons a {
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
        }

        .login-btn {
            color: #4f46e5;
            border: 1px solid #4f46e5;
        }

        .register-btn {
            color: white;
            background: #4f46e5;
        }

        .nav-buttons a:hover {
            opacity: 0.85;
        }

        /* Hero */
        .hero {
            min-height: 570px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 70px 8%;
            gap: 50px;
            background: linear-gradient(135deg, #eef2ff, #ffffff);
        }

        .hero-content {
            max-width: 600px;
        }

        .hero-content h1 {
            font-size: 48px;
            line-height: 1.15;
            margin-bottom: 20px;
            color: #111827;
        }

        .hero-content h1 span {
            color: #4f46e5;
        }

        .hero-content p {
            font-size: 18px;
            line-height: 1.7;
            color: #6b7280;
            margin-bottom: 30px;
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .primary-btn,
        .secondary-btn {
            text-decoration: none;
            padding: 14px 25px;
            border-radius: 9px;
            font-weight: bold;
            display: inline-block;
        }

        .primary-btn {
            background: #4f46e5;
            color: white;
        }

        .secondary-btn {
            background: white;
            color: #4f46e5;
            border: 1px solid #4f46e5;
        }

        .primary-btn:hover,
        .secondary-btn:hover {
            transform: translateY(-2px);
        }

        /* Hero illustration */
        .hero-card {
            width: 380px;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.12);
        }

        .hero-icon {
            width: 80px;
            height: 80px;
            border-radius: 18px;
            background: #eef2ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
            margin-bottom: 20px;
        }

        .hero-card h2 {
            margin-bottom: 12px;
            color: #111827;
        }

        .hero-card p {
            color: #6b7280;
            line-height: 1.6;
        }

        .check-list {
            margin-top: 20px;
            list-style: none;
        }

        .check-list li {
            margin-bottom: 12px;
            color: #374151;
        }

        .check-list li::before {
            content: "✓";
            color: #16a34a;
            font-weight: bold;
            margin-right: 10px;
        }

        /* Features */
        .features {
            padding: 70px 8%;
            background: white;
        }

        .section-title {
            text-align: center;
            margin-bottom: 45px;
        }

        .section-title h2 {
            font-size: 34px;
            margin-bottom: 12px;
        }

        .section-title p {
            color: #6b7280;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        .feature-card {
            padding: 30px;
            border-radius: 15px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            transition: 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }

        .feature-icon {
            font-size: 32px;
            margin-bottom: 15px;
        }

        .feature-card h3 {
            margin-bottom: 10px;
        }

        .feature-card p {
            color: #6b7280;
            line-height: 1.6;
        }

        /* How it works */
        .how-section {
            padding: 70px 8%;
            background: #f5f7fb;
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .step {
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            border: 1px solid #e5e7eb;
        }

        .step-number {
            width: 45px;
            height: 45px;
            background: #4f46e5;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-weight: bold;
            font-size: 18px;
        }

        .step h3 {
            margin-bottom: 8px;
        }

        .step p {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.5;
        }

        /* Footer */
        footer {
            background: #111827;
            color: white;
            text-align: center;
            padding: 25px;
        }

        footer p {
            color: #d1d5db;
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 900px) {

            .hero {
                flex-direction: column;
                text-align: center;
            }

            .hero-buttons {
                justify-content: center;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }

            .steps {
                grid-template-columns: 1fr 1fr;
            }

            .hero-card {
                width: 100%;
                max-width: 400px;
            }
        }

        @media (max-width: 600px) {

            .navbar {
                padding: 15px 5%;
            }

            .nav-buttons a {
                padding: 8px 12px;
                font-size: 12px;
            }

            .hero {
                padding: 50px 5%;
            }

            .hero-content h1 {
                font-size: 36px;
            }

            .steps {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<!-- Navbar -->
<nav class="navbar">

    <div class="logo">
        🎓 Smart Scholarship
    </div>

    <div class="nav-buttons">
        <a href="login.php" class="login-btn">
            Login
        </a>

        <a href="register.php" class="register-btn">
            Register
        </a>
    </div>

</nav>


<!-- Hero Section -->
<section class="hero">

    <div class="hero-content">

        <h1>
            Find the Right
            <span>Scholarship</span>
            for You
        </h1>

        <p>
            A smart scholarship eligibility and recommendation
            system that analyzes your academic and personal
            information and helps you identify scholarships
            you may be eligible for.
        </p>

        <div class="hero-buttons">

            <a href="register.php" class="primary-btn">
                Get Started →
            </a>

            <a href="login.php" class="secondary-btn">
                Student Login
            </a>

        </div>

    </div>


    <div class="hero-card">

        <div class="hero-icon">
            🎓
        </div>

        <h2>
            Smart Recommendation
        </h2>

        <p>
            Enter your profile details and let the system
            compare them with available scholarship criteria.
        </p>

        <ul class="check-list">

            <li>Academic eligibility analysis</li>

            <li>Income-based verification</li>

            <li>Community &amp; course matching</li>

            <li>Scholarship recommendations</li>

        </ul>

    </div>

</section>


<!-- Features -->
<section class="features">

    <div class="section-title">

        <h2>
            Key Features
        </h2>

        <p>
            Everything you need to discover suitable scholarships
        </p>

    </div>


    <div class="feature-grid">

        <div class="feature-card">

            <div class="feature-icon">
                🔍
            </div>

            <h3>
                Eligibility Checking
            </h3>

            <p>
                Automatically compare your profile with
                scholarship eligibility requirements.
            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">
                🎯
            </div>

            <h3>
                Smart Recommendations
            </h3>

            <p>
                Identify scholarships that best match
                your academic and personal information.
            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">
                📋
            </div>

            <h3>
                Application Tracking
            </h3>

            <p>
                Apply for scholarships and track your
                application status from your dashboard.
            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">
                🗂️
            </div>

            <h3>
                XML-Based Data
            </h3>

            <p>
                Scholarship criteria are maintained using
                structured XML data for flexible management.
            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">
                👨‍💼
            </div>

            <h3>
                Admin Management
            </h3>

            <p>
                Administrators can manage students,
                applications and application statuses.
            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">
                🔐
            </div>

            <h3>
                Secure Login
            </h3>

            <p>
                Separate student and administrator login
                systems protect application information.
            </p>

        </div>

    </div>

</section>


<!-- How it Works -->
<section class="how-section">

    <div class="section-title">

        <h2>
            How It Works
        </h2>

        <p>
            Simple steps to find and apply for scholarships
        </p>

    </div>


   