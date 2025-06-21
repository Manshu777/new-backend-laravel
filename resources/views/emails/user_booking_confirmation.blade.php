<!DOCTYPE html>
<html>
<head>
    <style>
        .container {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
        }
        .content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 12px;
        }
        .booking-details {
            margin-top: 20px;
        }
        .booking-details p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Next Gen Trip</h1>
            <h3>Your Holiday Booking Confirmation</h3>
        </div>
        <div class="content">
            <p>Dear {{ $booking->username }},</p>
            <p>Thank you for booking with Next Gen Trip! We're excited to confirm your holiday package booking.</p>
            
            <div class="booking-details">
                <h3>Booking Details:</h3>
                <p><strong>Holiday Package:</strong> {{ $booking->holiday_name }}</p>
                <p><strong>Name:</strong> {{ $booking->username }}</p>
                <p><strong>Email:</strong> {{ $booking->email }}</p>
                <p><strong>Phone:</strong> {{ $booking->phone_number }}</p>
                @if($booking->message)
                    <p><strong>Message:</strong> {{ $booking->message }}</p>
                @endif
            </div>
            
            <p>We'll contact you soon with more details about your trip. If you have any questions, feel free to reach out to us.</p>
            <p>Best Regards,<br>The Next Gen Trip Team</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Next Gen Trip. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
