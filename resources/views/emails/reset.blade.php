<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; color: #222; }
        .container { max-width: 480px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #0001; padding: 32px; }
        .btn { display: inline-block; background: #7c3aed; color: #fff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 24px; }
        .btn:hover { background: #5b21b6; }
        .footer { margin-top: 32px; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Password Reset Request</h2>
        <p>Hello,</p>
        <p>We received a request to reset your password. Click the button below to set a new password for your account:</p>
        <a href="{{ $mailData['resetUrl'] }}" class="btn">Reset Password</a>
        <p>If you did not request a password reset, you can safely ignore this email. Your password will remain unchanged.</p>
        <div class="footer">
            <p>Thank you,<br>The Junto Team</p>
        </div>
    </div>
</body>
</html>
