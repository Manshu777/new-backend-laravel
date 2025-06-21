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
            background-color: #2196F3;
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
            <h3>New Holiday Booking Notification</h3>
        </div>
        <div class="content">
            <p>A new holiday booking has been received:</p>
            
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
        </div>
    </div>
</body>
</html>