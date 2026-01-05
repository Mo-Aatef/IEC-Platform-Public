<?php
// public/sign-in.php
require_once '../app/auth.php'; 

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $error = loginUser($email, $password, $conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - IEC Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>

<div class="split-screen">
    
    <div class="left-panel">
        <div class="brand-logo">
            <i class="fa-solid fa-graduation-cap"></i> IEC Platform
        </div>
        
        <div class="left-content">
            <h1>Transform Your<br>English Skills</h1>
            <p>Join students and tutors using the IEC Platform to master business communication through our blended learning approach.</p>
            
            <ul class="feature-list">
                <li><i class="fa-solid fa-book"></i> 28-week structured program</li>
                <li><i class="fa-solid fa-chart-line"></i> Track your progress in real-time</li>
                <li><i class="fa-solid fa-users"></i> Connect online & offline learning</li>
            </ul>
        </div>
    </div>

    <div class="right-panel">
        <div class="auth-box">
            
            <div class="header-text">
                <h2>Sign In to IEC</h2>
                <p>Please enter your details to continue.</p>
            </div>

            <?php if($error): ?>
                <div class="alert error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Email Address</label>
                    <div class="input-wrapper">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" name="email" required placeholder="name@example.com">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" required placeholder="Enter your password">
                    </div>
                </div>
                
                <div class="forgot-row">
                    <a href="#">Forgot Password?</a>
                </div>
                
                <button type="submit" class="btn-primary">Sign In &rarr;</button>
            </form>

            <div class="divider"><span>OR</span></div>
            
            <button class="social-btn" disabled title="Currently Disabled">
                <i class="fa-brands fa-google"></i> Continue with Google
            </button>
            <button class="social-btn" disabled title="Currently Disabled">
                <i class="fa-brands fa-microsoft"></i> Continue with Microsoft
            </button>

            <div class="info-box">
                <i class="fa-solid fa-circle-info" style="font-size: 16px; margin-top: 2px;"></i>
                <div>
                    <strong>New Student?</strong><br>
                    If you do not have an account yet, please contact your Tutor directly. They will register you and provide your login credentials.
                </div>
            </div>

        </div>
    </div>

</div>

</body>
</html>