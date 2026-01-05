<?php
require_once __DIR__ . '/../app/config.php';

// 1. Redirect if already logged in
if (session_status() === PHP_SESSION_NONE)
    session_start();
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] === 'manager') {
            header('Location: ../admin/manager-dashboard.php');
        } elseif ($_SESSION['role'] === 'tutor') {
            header('Location: ../tutor/dashboard.php');
        } else {
            header('Location: student-dashboard.php');
        }
        exit;
    } else {
        session_destroy();
        session_start();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IEC Platform - English for Informatics</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/landing.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <div class="logo-box"><i class="fa-solid fa-graduation-cap"></i></div>
                <span class="fw-bold text-dark">IEC Platform</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item ms-lg-2 mt-3 mt-lg-0">
                        <a href="sign-in.php" class="btn btn-primary px-4 py-2 fw-bold"
                            style="background:var(--primary-blue); border:none;">Sign In</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <span class="hero-badge">For Informatics Students at UII</span>
                    <h1 class="hero-title">Learn English for Informatics Students</h1>
                    <p class="hero-text">
                        A one-year English learning program designed specifically for informatics students. Study 30
                        minutes daily online and join weekly fun speaking sessions.
                    </p>
                    
                    <div class="d-flex flex-column flex-sm-row gap-3 mb-5 hero-btns-container">
                        <a href="sign-in.php" class="btn-custom-primary">
                            <i class="fa-solid fa-user-graduate"></i> Log In as Student
                        </a>
                        <a href="sign-in.php" class="btn-custom-outline">
                            <i class="fa-solid fa-chalkboard-user"></i> Log In as Tutor
                        </a>
                    </div>
                    
                    <div class="d-flex gap-4 text-secondary fw-bold hero-stats-row">
                        <div><i class="fa-regular fa-clock text-primary me-2"></i>30 min/day</div>
                        <div><i class="fa-solid fa-calendar-check text-primary me-2"></i>Weekly meetings</div>
                        <div><i class="fa-solid fa-chart-line text-primary me-2"></i>A0 → A2</div>
                    </div>
                </div>

                <div class="col-lg-6 d-none d-lg-block text-center">
                    <img src="https://storage.googleapis.com/uxpilot-auth.appspot.com/5E9Aa2qeC7WAUtzEDByTiazFYNi1/div-92682516-0cce-4c12-9052-5113c18d832b.svg"
                        class="img-fluid" alt="Classroom Illustration">
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="py-5 my-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold display-5 text-dark">The Flipped Classroom Model</h2>
                <p class="lead text-secondary">Learn at home with IEC Platform, practice speaking in meetings</p>
            </div>

            <div class="row g-5 align-items-center">
                <div class="col-lg-6 about-text-col">
                    <div class="d-flex mb-4">
                        <div class="icon-box bg-blue-light"><i class="fa-solid fa-laptop"></i></div>
                        <div>
                            <h4 class="fw-bold text-dark">Online Session</h4>
                            <p class="text-secondary">Complete engaging online lessons and exercises at your own pace
                                for 30 minutes daily.</p>
                        </div>
                    </div>
                    <div class="d-flex mb-4">
                        <div class="icon-box bg-green-light"><i class="fa-solid fa-users"></i></div>
                        <div>
                            <h4 class="fw-bold text-dark">Offline Session</h4>
                            <p class="text-secondary">Join weekly in-person meetings where you practice speaking in a
                                fun, supportive environment.</p>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="icon-box bg-purple-light"><i class="fa-solid fa-trophy"></i></div>
                        <div>
                            <h4 class="fw-bold text-dark">Track Progress</h4>
                            <p class="text-secondary">Monitor your improvement with regular assessments and feedback
                                from A0 to A2.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="steps-card">
                        <div class="step-item">
                            <div class="step-number">1</div>
                            <div class="step-line"></div>
                            <h5 class="fw-bold">Study online lessons</h5>
                            <p class="mb-0 text-light opacity-75">Complete daily exercises and activities</p>
                        </div>
                        <div class="step-item">
                            <div class="step-number">2</div>
                            <div class="step-line"></div>
                            <h5 class="fw-bold">Prepare for meetings</h5>
                            <p class="mb-0 text-light opacity-75">Review vocabulary and practice pronunciation</p>
                        </div>
                        <div class="step-item">
                            <div class="step-number">3</div>
                            <h5 class="fw-bold">Attend weekly sessions</h5>
                            <p class="mb-0 text-light opacity-75">Practice speaking with peers and instructors</p>
                        </div>
                        <div class="d-flex align-items-center mt-4">
                            <div class="step-number position-relative me-3 bg-white text-primary"><i
                                    class="fa-solid fa-check"></i></div>
                            <h5 class="fw-bold mb-0">Achieve A2 level</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold display-5 text-dark">Platform Features</h2>
                <p class="lead text-secondary">Everything you need to improve your English skills</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="icon-box bg-blue-light mb-4"><i class="fa-solid fa-list-check"></i></div>
                        <h4 class="fw-bold mb-3">Weekly<br>Modules</h4>
                        <p class="text-secondary">Organized weekly learning journey with fresh modules available each
                            week.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="icon-box bg-green-light mb-4"><i class="fa-solid fa-fire"></i></div>
                        <h4 class="fw-bold mb-3">Daily<br>Practice</h4>
                        <p class="text-secondary">Short, engaging exercises designed for 30 minutes of daily practice.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="icon-box bg-orange-light mb-4"><i class="fa-solid fa-comments"></i></div>
                        <h4 class="fw-bold mb-3">Fun Offline<br>Meetings</h4>
                        <p class="text-secondary">Join interactive speaking sessions with games and real conversations.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="icon-box bg-purple-light mb-4"><i class="fa-solid fa-chart-pie"></i></div>
                        <h4 class="fw-bold mb-3">Progress<br>Tracking</h4>
                        <p class="text-secondary">Monitor your journey from A0-A1 to A2 with detailed analytics.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section text-center">
        <div class="container">
            <h2 class="fw-bold display-5 mb-4">Ready to Start Your Journey?</h2>
            <p class="lead mb-5 opacity-75 mx-auto" style="max-width: 700px;">
                Join hundreds of informatics students who are improving their English skills with IEC Platform.
            </p>
            <a href="sign-in.php" class="btn btn-light btn-lg px-5 py-3 fw-bold text-primary shadow">
                <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Login to Start
            </a>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center mb-3 justify-content-center justify-content-lg-start">
                        <div class="logo-box me-2"><i class="fa-solid fa-graduation-cap"></i></div>
                        <h4 class="text-white fw-bold mb-0">IEC Platform</h4>
                    </div>
                    <p class="text-center text-lg-start">Empowering informatics students to master English through innovative flipped classroom learning.
                    </p>
                </div>
                <div class="col-lg-2 offset-lg-1 text-center text-lg-start">
                    <h5>Platform</h5>
                    <a href="#features">Features</a>
                    <a href="#about">How it Works</a>
                </div>
                <div class="col-lg-2 text-center text-lg-start">
                    <h5>Support</h5>
                    <a href="#">Help Center</a>
                    <a href="#">Contact Us</a>
                </div>
                <div class="col-lg-3 text-center text-lg-start">
                    <h5>Connect</h5>
                    <div class="d-flex gap-3 justify-content-center justify-content-lg-start social-links">
                        <a href="#" class="text-white fs-4"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="text-white fs-4"><i class="fa-brands fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-top border-secondary mt-5 pt-4 text-center text-secondary small">
                &copy; 2025 IEC Platform. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>