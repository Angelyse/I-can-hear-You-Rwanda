<?php

session_start();

$errors = $_SESSION["register_errors"] ?? [];

unset($_SESSION["register_errors"]);

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

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Register | I Can Hear You Rwanda</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

    <link rel="stylesheet"
          href="../assets/css/auth.css">

</head>

<body>


    <!-- NAVBAR -->

    <header class="navbar">

        <div class="container nav-content">

            <a href="../index.php" class="logo">

                <span>
                    <img
                        src="../assets/images/log.jpeg"
                        alt="I Can Hear You Logo">
                </span>

                I Can Hear You Rwanda

            </a>


            <nav>

                <a href="../index.php">
                    Home
                </a>

                <a href="services.php">
                    Services
                </a>

                <a href="about.php">
                    About
                </a>

                <a href="contact.php">
                    Contact
                </a>

                <a href="login.php"
                   class="btn btn-outline">
                    Login
                </a>

            </nav>

        </div>

    </header>



    <!-- REGISTER -->

    <main class="auth-section">

        <div class="auth-container">


            <!-- INFORMATION -->

            <div class="auth-info">

                <span class="badge">
                    Join Us
                </span>

                <h1>
                    Start your journey
                    toward better wellbeing.
                </h1>

                <p>
                    Create an account and connect with trained
                    counsellors who are ready to listen and support you.
                </p>


                <div class="auth-feature">

                    <div class="auth-feature-icon">
                        💙
                    </div>

                    <div>

                        <h3>
                            A Safe Space
                        </h3>

                        <p>
                            Share your thoughts in a supportive
                            environment.
                        </p>

                    </div>

                </div>


                <div class="auth-feature">

                    <div class="auth-feature-icon">
                        🧑‍⚕️
                    </div>

                    <div>

                        <h3>
                            Trained Counsellors
                        </h3>

                        <p>
                            Connect with professionals based
                            on your needs.
                        </p>

                    </div>

                </div>


                <div class="auth-feature">

                    <div class="auth-feature-icon">
                        📱
                    </div>

                    <div>

                        <h3>
                            Easy Access
                        </h3>

                        <p>
                            Access support through chat and
                            online sessions.
                        </p>

                    </div>

                </div>

            </div>


            <!-- FORM -->

            <div class="auth-card register-card">

                <div class="auth-header">

                    <h2>
                        Create Account
                    </h2>

                    <p>
                        Fill in your details to get started.
                    </p>

                </div>


                <form action="../auth/register-process.php" method="POST">


                    <!-- NAME -->

                    <div class="form-group">

                        <label for="name">
                            Full Name
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="Enter your full name"
                            required
                        >

                    </div>


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


                    <!-- PHONE -->

                    <div class="form-group">

                        <label for="phone">
                            Phone Number
                        </label>

                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            placeholder="Enter your phone number"
                        >

                    </div>


                    <!-- ACCOUNT TYPE -->

                    <div class="form-group">

                        <label for="account-type">
                            I want to register as
                        </label>

                        <select id="account-type"
                            name="role" required>

                            <option value="">
                                Select account type
                            </option>

                            <option value="client">
                                Client
                            </option>

                            <option value="counselor">
                                Counsellor
                            </option>

                        </select>

                    </div>


                    <!-- PASSWORD -->

                    <div class="form-group">

                        <label for="password">
                            Password
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Create a password"
                            required
                        >

                    </div>


                    <!-- CONFIRM PASSWORD -->

                    <div class="form-group">

                        <label for="confirm-password">
                            Confirm Password
                        </label>

                        <input
                            type="password"
                            id="confirm-password"
                            name="confirm_password"
                            placeholder="Confirm your password"
                            required
                        >

                    </div>


                    <!-- TERMS -->

                    <div class="terms">

                        <label>

                            <input
                                type="checkbox"
                                name="terms"
                                required
                            >

                            I agree to the
                            <a href="#">
                                Terms of Service
                            </a>
                            and
                            <a href="#">
                                Privacy Policy
                            </a>

                        </label>

                    </div>


                    <!-- SUBMIT -->

                    <button
                        type="submit"
                        class="auth-btn">

                        Create Account

                    </button>

                </form>


                <!-- LOGIN -->

                <div class="auth-footer">

                    <p>

                        Already have an account?

                        <a href="login.php">
                            Sign in
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