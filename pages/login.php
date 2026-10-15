<?php

session_start();

$errors = $_SESSION["login_errors"] ?? [];

$success = $_SESSION["register_success"] ?? null;

unset($_SESSION["login_errors"]);

unset($_SESSION["register_success"]);

?>


<?php if (!empty($errors)): ?>

    <div class="auth-error">

        <?php foreach ($errors as $error): ?>

            <p>
                <i class="fas fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($error); ?>
            </p>

        <?php endforeach; ?>

    </div>

<?php endif; ?>


<?php if ($success): ?>

    <div class="auth-success">

        <i class="fas fa-circle-check"></i>

        <?php echo htmlspecialchars($success); ?>

    </div>

<?php endif; ?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>

<body>

    <!-- NAVBAR -->

    <header class="navbar">

        <div class="container nav-content">

            <a href="../index.php" class="logo">
                <span>
                    <img src="../assets/images/log.jpeg"
                         alt="I Can Hear You Logo">
                </span>

                I Can Hear You Rwanda
            </a>

            <nav>

                <a href="../index.php">Home</a>
                <a href="services.php">Services</a>
                <a href="about.php">About</a>
                <a href="contact.php">Contact</a>

                <a href="register.php" class="btn btn-primary">
                    Register
                </a>

            </nav>

        </div>

    </header>


    <!-- LOGIN SECTION -->

    <main class="auth-section">

        <div class="auth-container">


            <!-- LEFT SIDE -->

            <div class="auth-info">

                <span class="badge">
                    Welcome Back
                </span>

                <h1>
                    Good to see you again.
                </h1>

                <p>
                    Sign in to continue your journey with
                    I Can Hear You Rwanda. Your safe space is waiting.
                </p>


                <div class="auth-feature">

                    <div class="auth-feature-icon">
                        🔒
                    </div>

                    <div>
                        <h3>Private & Secure</h3>

                        <p>
                            Your personal information and
                            conversations are protected.
                        </p>
                    </div>

                </div>


                <div class="auth-feature">

                    <div class="auth-feature-icon">
                        💬
                    </div>

                    <div>
                        <h3>Stay Connected</h3>

                        <p>
                            Continue conversations with
                            your counsellor.
                        </p>
                    </div>

                </div>

            </div>


            <!-- LOGIN FORM -->

            <div class="auth-card">

                <div class="auth-header">

                    <h2>
                        Sign in
                    </h2>

                    <p>
                        Enter your details to access your account.
                    </p>

                </div>


                <form action="../auth/login-process.php" method="POST">


                    <!-- EMAIL -->

                    <div class="form-group">

                        <label for="email">
                            Email Address
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your email"
                            required
                        >

                    </div>


                    <!-- PASSWORD -->

                    <div class="form-group">

                        <div class="label-row">

                            <label for="password">
                                Password
                            </label>

                            <a href="#">
                                Forgot password?
                            </a>

                        </div>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            required
                        >

                    </div>


                    <!-- REMEMBER -->

                    <div class="remember">

                        <label>

                            <input
                                type="checkbox"
                                name="remember"
                            >

                            Remember me

                        </label>

                    </div>


                    <!-- BUTTON -->

                    <button
                        type="submit"
                        class="auth-btn">
                        Sign In
                    </button>


                </form>


                <!-- REGISTER -->

                <div class="auth-footer">

                    <p>
                        Don't have an account?
                        <a href="register.php">
                            Create one
                        </a>
                    </p>

                </div>

            </div>

        </div>

    </main>


    <!-- FOOTER -->

    <footer>

        <div class="footer-bottom">

            <p>
                © 2026 I Can Hear You Rwanda. All rights reserved.
            </p>

        </div>

    </footer>

</body>
</html>