<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
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

        .header {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 30px 20px 20px;
            color: white;
        }

        .back-arrow {
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 24px;
            font-weight: bold;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 500;
        }

        .card {
            background: white;
            border-radius: 25px;
            padding: 40px 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-top: -60px;
            position: relative;
            z-index: 1;
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
        }

        input {
            width: 100%;
            padding: 16px 20px;
            border: none;
            background: #f5f5f9;
            border-radius: 12px;
            font-size: 14px;
            color: #333;
            outline: none;
            transition: background 0.3s;
        }

        input::placeholder {
            color: #a0a0b8;
        }

        input:focus {
            background: #ebebf0;
        }

        .error {
            color: #ff4d4d;
            font-size: 13px;
            margin-top: 5px;
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #ff6b4a 0%, #ff5436 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 25px;
            box-shadow: 0 6px 20px rgba(255, 84, 54, 0.3);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 84, 54, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="orange-header">
        <div class="header">
            <a href="{{ route('user.login') }}" style="display:flex; align-items:center; gap:15px; text-decoration:none; color:white;">
                <div class="back-arrow">←</div>
                <h1>Sign Up</h1>
            </a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='50' fill='%23e0e0e0'/%3E%3Ccircle cx='50' cy='38' r='18' fill='%23ffdbac'/%3E%3Cpath d='M20 85 Q20 60 50 60 Q80 60 80 85 Z' fill='%2388398a'/%3E%3Cpath d='M32 35 Q32 28 38 28 Q44 28 44 35 Z' fill='%23d4a574'/%3E%3Cpath d='M56 35 Q56 28 62 28 Q68 28 68 35 Z' fill='%23d4a574'/%3E%3Ccircle cx='42' cy='40' r='2' fill='%23333'/%3E%3Ccircle cx='58' cy='40' r='2' fill='%23333'/%3E%3Cpath d='M43 48 Q50 52 57 48' stroke='%23ff8b8b' stroke-width='2' fill='none' stroke-linecap='round'/%3E%3C/svg%3E" alt="Avatar" class="avatar">

            <form id="signupForm" action="{{ route('user.register.submit') }}" method="POST">
                @csrf

                <div class="form-group">
                    <input type="text" name="name" placeholder="Enter your name" value="{{ old('name') }}">
                    @error('name') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <input type="email" name="email" placeholder="Email" value="{{ old('email') }}">
                    @error('email') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <input type="tel" name="mobile" placeholder="Mobile" value="{{ old('mobile') }}">
                    @error('mobile') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <input type="password" name="password" placeholder="Enter password">
                    @error('password') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <input type="password" name="password_confirmation" placeholder="Confirm password">
                </div>

                <div class="form-group">
                    <input type="text" name="ref_code" placeholder="Referral Code" value="{{ old('ref_code') }}">
                </div>

                <button type="submit" class="submit-btn">Sign Up</button>
            </form>
        </div>
    </div>

    <!-- ✅ Toastr Notification -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <script>
        @if(session('success'))
            toastr.success("{{ session('success') }}");
        @endif
        @if(session('error'))
            toastr.error("{{ session('error') }}");
        @endif
    </script>
</body>
</html>
