<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NextGenTrip - OTP Verification</title>
<style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            padding: 20px;
        }
        .email-container {
            background: #ffffff;
            max-width: 400px;
            margin: auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #007bff;
            margin-bottom: 5px;
        }
        .tagline {
            font-size: 14px;
            color: #555;
            margin-bottom: 15px;
        }
        .otp-code {
            font-size: 24px;
            color: #ff5733;
            font-weight: bold;
            margin: 10px 0;
        }
        .footer {
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        .brand {
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
        }
        .support-link {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
</style>
</head>
<body>
<div class="email-container">
<h2>Welcome to <span class="brand">NextGenTrip</span>, {{ $mail }}!</h2>
<p class="tagline"><em>Your Ultimate Travel Companion</em></p>
<p>Your OTP code for verification is:</p>
<p class="otp-code">{{ $otp }}</p>
<p>Please enter this code to complete your verification.</p>
<p class="footer">
            If you didn’t request this, please ignore this email. <br> 
            Need help? Contact us at <a href="mailto:support@nextgentrip.com" class="support-link">support@nextgentrip.com</a>
</p>
</div>
</body>
</html>