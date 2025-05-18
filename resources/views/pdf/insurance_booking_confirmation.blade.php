
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insurance Booking Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; }
        .header { background-color: #003366; color: #ffffff; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 20px; }
        .content h2 { color: #333333; font-size: 20px; margin-top: 0; }
        .content p { color: #666666; font-size: 16px; line-height: 1.5; }
        .details-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .details-table th, .details-table td { border: 1px solid #dddddd; padding: 10px; text-align: left; font-size: 14px; }
        .details-table th { background-color: #f2f2f2; color: #333333; }
        .coverage-list { margin: 20px 0; padding-left: 20px; }
        .coverage-list li { color: #666666; font-size: 14px; line-height: 1.6; }
        .footer { background-color: #f4f4f4; padding: 10px; text-align: center; font-size: 12px; color: #999999; }
        .footer a { color: #003366; text-decoration: none; }
        @media only screen and (max-width: 600px) {
            .container { margin: 10px; }
            .header h1 { font-size: 20px; }
            .content h2 { font-size: 18px; }
            .details-table th, .details-table td { font-size: 12px; padding: 8px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Travel Insurance Co.</h1>
        </div>
        <div class="content">
            <h2>Dear {{ $pax['Title'] }} {{ $pax['FirstName'] }} {{ $pax['LastName'] }},</h2>
            <p>Thank you for booking your travel insurance with us! This is a test email to confirm email functionality. Below are the details of your insurance policy.</p>
            <h3>Booking Details</h3>
            <table class="details-table">
                <tr>
                    <th>Booking ID</th>
                    <td>{{ $itinerary['BookingId'] }}</td>
                </tr>
                <tr>
                    <th>Insurance ID</th>
                    <td>{{ $itinerary['InsuranceId'] }}</td>
                </tr>
                <tr>
                    <th>Plan Name</th>
                    <td>{{ $itinerary['PlanName'] }}</td>
                </tr>
                <tr>
                    <th>Policy Start Date</th>
                    <td>{{ $startDate }}</td>
                </tr>
                <tr>
                    <th>Policy End Date</th>
                    <td>{{ $endDate }}</td>
                </tr>
                <tr>
                    <th>Major Destination</th>
                    <td>{{ $pax['MajorDestination'] }}</td>
                </tr>
                <tr>
                    <th>Total Amount</th>
                    <td>INR {{ $pax['Price']['GrossFare'] }}</td>
                </tr>
                <tr>
                    <th>Booking Date</th>
                    <td>{{ $bookingDate }}</td>
                </tr>
            </table>
            <h3>Insured Person</h3>
            <p>
                <strong>Name:</strong> {{ $pax['Title'] }} {{ $pax['FirstName'] }} {{ $pax['LastName'] }}<br>
                <strong>Date of Birth:</strong> {{ $dob }}<br>
                <strong>Phone:</strong> +91-{{ $pax['PhoneNumber'] }}<br>
                <strong>Email:</strong> {{ $pax['EmailId'] }}<br>
                <strong>Address:</strong> {{ $pax['AddressLine1'] }}, {{ $pax['City'] }}, {{ $pax['Country'] }}, {{ $pax['PinCode'] }}
            </p>
            <h3>Coverage Details</h3>
            <ul class="coverage-list">
                {!! $coverageHtml !!}
            </ul>
            <p>For any queries or assistance, please contact our support team at <a href="mailto:support@travelinsuranceco.com">support@travelinsuranceco.com</a> or call +91-123-456-7890.</p>
        </div>
        <div class="footer">
            <p>© 2025 Travel Insurance Co. All rights reserved.<br>
               This email contains confidential information and is intended solely for the recipient. If you are not the intended recipient, please delete this email.</p>
        </div>
    </div>
</body>
</html>
