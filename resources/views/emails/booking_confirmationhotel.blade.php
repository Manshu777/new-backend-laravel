<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Booking Confirmation</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f4f6;
            min-height: 100vh;
            display: table;
            width: 100%;
            padding: 16px;
        }

        .container {
            max-width: 640px;
            width: 100%;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }

        .header {
            background: linear-gradient(135deg, #4b6cb7, #182848);
            color: #ffffff;
            padding: 24px;
            text-align: center;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 600;
        }

        .header p {
            font-size: 16px;
            margin-top: 8px;
            opacity: 0.9;
        }

        .content {
            padding: 24px;
        }

        .content p {
            color: #4b5563;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 16px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 16px;
        }

        .booking-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 32px;
        }

        .booking-table td {
            padding: 8px;
            color: #4b5563;
        }

        .booking-table td.label {
            font-weight: 500;
            color: #374151;
            width: 40%;
        }

        .guest-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .guest-table {
            width: 100%;
            border-collapse: collapse;
        }

        .guest-table td {
            padding: 8px;
            color: #4b5563;
        }

        .guest-table td.label {
            font-weight: 500;
            color: #374151;
            width: 40%;
        }

        .footer {
            background-color: #f9fafb;
            padding: 24px;
            text-align: center;
        }

        .footer p {
            color: #6b7280;
            font-size: 12px;
        }

        .footer a {
            color: #2563eb;
            text-decoration: none;
            font-size: 12px;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            .booking-table td, .guest-table td {
                display: block;
                width: 100%;
            }
            .booking-table td.label, .guest-table td.label {
                width: 100%;
                font-weight: 500;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Booking Confirmation</h1>
            <p>Thank you for choosing us!</p>
        </div>

        <!-- Content -->
        <div class="content">
            <p>
                Dear {{ $bookingDetails['HotelRoomsDetails'][0]['HotelPassenger'][0]['FirstName'] }} {{ $bookingDetails['HotelRoomsDetails'][0]['HotelPassenger'][0]['LastName'] }},
            </p>
            <p>
                Your hotel reservation has been successfully confirmed. Below are the details of your booking.
            </p>

            <!-- Booking Details -->
            <div>
                <h2 class="section-title">Booking Details</h2>
                <table class="booking-table">
                    <tr>
                        <td class="label">Booking ID:</td>
                        <td>{{ $bookingDetails['BookingId'] }}</td>
                    </tr>
                    <tr>
                        <td class="label">Confirmation Number:</td>
                        <td>{{ $bookingDetails['ConfirmationNo'] }}</td>
                    </tr>
                    <tr>
                        <td class="label">Status:</td>
                        <td>{{ $bookingDetails['HotelBookingStatus'] }}</td>
                    </tr>
                    <tr>
                        <td class="label">Total Amount:</td>
                        <td>{{ $bookingDetails['NetAmount'] }}</td>
                    </tr>
                </table>
            </div>

            <!-- Guest Details -->
            <div>
                <h2 class="section-title">Guest Details</h2>
                @foreach ($bookingDetails['HotelRoomsDetails'] as $room)
                    <div class="guest-card">
                        <h3 class="section-title" style="font-size: 16px;">Room {{ $loop->iteration }}</h3>
                        @foreach ($room['HotelPassenger'] as $passenger)
                            <table class="guest-table">
                                <tr>
                                    <td class="label">Name:</td>
                                    <td>{{ $passenger['Title'] }} {{ $passenger['FirstName'] }} {{ $passenger['LastName'] }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Email:</td>
                                    <td>{{ $passenger['Email'] }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Phone:</td>
                                    <td>{{ $passenger['Phoneno'] }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Age:</td>
                                    <td>{{ $passenger['Age'] }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Pax Type:</td>
                                    <td>{{ $passenger['PaxType'] == 1 ? 'Adult' : 'Child' }}</td>
                                </tr>
                            </table>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <!-- Footer Message -->
            <p>
                Please find the detailed booking confirmation attached as a PDF.
            </p>
            <p>
                If you have any questions, feel free to contact our support team. We look forward to welcoming you!
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© {{ date('Y') }} Your Hotel Name. All rights reserved.</p>
            <a href="#">Contact Support</a>
        </div>
    </div>
</body>
</html>