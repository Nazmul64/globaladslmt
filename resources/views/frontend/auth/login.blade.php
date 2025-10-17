<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Money Ltd | Login</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
        }

        /* Header Section */
        .orange-header {
            background: linear-gradient(135deg, #ff6b4a 0%, #ff5436 100%);
            height: 200px;
            position: relative;
            overflow: hidden;
        }

        .orange-header::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: -10%;
            right: -10%;
            height: 100px;
            background: #f5f5f5;
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        }

        .container {
            max-width: 380px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Card Style */
        .card {
            background: white;
            border-radius: 25px;
            padding: 40px 30px 35px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-top: -80px;
            position: relative;
            z-index: 3;
        }

        .avatar {
            width: 90px;
            height: 90px;
            margin: 0 auto 35px;
            display: block;
            border-radius: 50%;
            background: #f0f0f0;
            border: 4px solid white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 18px;
            position: relative;
        }

        .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }

        input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid transparent;
            background: #f5f5f9;
            border-radius: 12px;
            font-size: 14px;
            color: #333;
            outline: none;
            transition: all 0.3s;
        }

        input.error {
            border-color: #dc3545;
            background: #fff5f5;
        }

        input::placeholder {
            color: #a0a0b8;
        }

        input:focus {
            background: #ebebf0;
            border-color: #ff6b4a;
        }

        .password-toggle {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            color: #a0a0b8;
            user-select: none;
            transition: color 0.2s;
        }

        .password-toggle:hover {
            color: #666;
        }

        .forgot-password {
            text-align: center;
            margin: 15px 0 25px;
        }

        .forgot-password a {
            color: #00a8ff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s;
        }

        .forgot-password a:hover {
            color: #0088cc;
            text-decoration: underline;
        }

        /* Buttons */
        .signin-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #ff6b4a 0%, #ff5436 100%);
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 15px;
            box-shadow: 0 6px 20px rgba(255, 84, 54, 0.3);
            transition: all 0.2s;
        }

        .signin-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 84, 54, 0.4);
        }

        .signin-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .divider {
            text-align: center;
            margin: 20px 0 15px;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            height: 1px;
            background: #e0e0e0;
        }

        .divider span {
            background: white;
            padding: 0 15px;
            position: relative;
            color: #999;
            font-size: 13px;
        }

        .signup-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.3);
            transition: all 0.2s;
            display: inline-block;
            text-align: center;
            text-decoration: none;
        }

        .signup-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.4);
        }

        @media (max-width: 480px) {
            .container { padding: 0 15px; }
            .card { padding: 30px 20px 25px; }
            .avatar { width: 80px; height: 80px; }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="orange-header"></div>

    <!-- Main Login Card -->
    <div class="container">
        <div class="card">
            <!-- Avatar -->
            <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png" alt="User Avatar" class="avatar">

            <!-- Login Form -->
            <form id="signinForm" action="{{ route('user.submit') }}" method="POST" novalidate>
                @csrf

                <!-- Email Field -->
                <div class="form-group mb-3">
                    <input type="email" id="emailInput" name="email" placeholder="Enter Email" required autocomplete="email">
                    <span class="error-message" id="emailError"></span>
                </div>

                <!-- Password Field -->
                <div class="form-group mb-3">
                    <input type="password" id="passwordInput" name="password" placeholder="Enter Password" required autocomplete="current-password">
                    <span class="password-toggle" id="togglePassword">👁️</span>
                    <span class="error-message" id="passwordError"></span>
                </div>

                <!-- Forgot Password -->
                <div class="forgot-password">
                    <a href="#" id="forgotPasswordLink">Forgot password?</a>
                </div>

                <!-- Login Button -->
                <button type="submit" class="signin-btn">Login</button>

                <!-- Divider -->
                <div class="divider"><span>or</span></div>

                <!-- Sign Up Button -->
                <a href="{{ route('user.register') }}" class="signup-btn">Sign Up</a>
            </form>
        </div>
    </div>

    <!-- Script -->
    <script>
        const passwordInput = document.getElementById('passwordInput');
        const togglePassword = document.getElementById('togglePassword');
        const signinForm = document.getElementById('signinForm');
        const emailInput = document.getElementById('emailInput');
        const emailError = document.getElementById('emailError');
        const passwordError = document.getElementById('passwordError');

        // Toggle Password Visibility
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            togglePassword.textContent = type === 'password' ? '👁️' : '🙈';
        });

        // Email Validation Function
        function validateEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        // Error Handlers
        function showError(input, errorEl, message) {
            input.classList.add('error');
            errorEl.textContent = message;
        }

        function clearError(input, errorEl) {
            input.classList.remove('error');
            errorEl.textContent = '';
        }

        // Real-time Validation
        emailInput.addEventListener('input', () => clearError(emailInput, emailError));
        passwordInput.addEventListener('input', () => clearError(passwordInput, passwordError));

        // Form Submit Validation
        signinForm.addEventListener('submit', (e) => {
            e.preventDefault();
            let valid = true;

            clearError(emailInput, emailError);
            clearError(passwordInput, passwordError);

            if (!emailInput.value.trim()) {
                showError(emailInput, emailError, 'Email is required');
                valid = false;
            } else if (!validateEmail(emailInput.value)) {
                showError(emailInput, emailError, 'Enter a valid email');
                valid = false;
            }

            if (!passwordInput.value) {
                showError(passwordInput, passwordError, 'Password is required');
                valid = false;
            } else if (passwordInput.value.length < 6) {
                showError(passwordInput, passwordError, 'Password must be at least 6 characters');
                valid = false;
            }

            if (valid) signinForm.submit();
        });
    </script>
</body>
</html>
