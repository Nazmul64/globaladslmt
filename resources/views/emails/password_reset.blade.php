<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #4CAF50;
            margin: 0;
        }
        .token-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 25px;
            margin: 30px 0;
            border-radius: 8px;
            text-align: center;
        }
        .token-label {
            color: #ffffff;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .token {
            font-size: 32px;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 5px;
            font-family: 'Courier New', monospace;
        }
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #4CAF50;
            margin: 20px 0;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #777;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Password Reset Request</h1>
        </div>

        <p>Hello,</p>
        <p>You are receiving this email because we received a password reset request for your account.</p>

        <div class="token-box">
            <div class="token-label">Your Password Reset Token</div>
            <div class="token">{{ $token }}</div>
        </div>

        <div class="info-box">
            <p><strong>📧 Email:</strong> {{ $email }}</p>
            <p><strong>⏰ Valid for:</strong> 60 minutes</p>
        </div>

        <p>Please enter this token in the app to reset your password.</p>

        <div class="warning">
            <strong>⚠️ Important:</strong> This password reset token will expire in 60 minutes. If you did not request a password reset, no further action is required and you can safely ignore this email.
        </div>

        <p>Best regards,<br><strong>Your App Team</strong></p>

        <div class="footer">
            <p>This is an automated message, please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
