<!DOCTYPE html>
<html>
<head>
    <title>Flight Ticket</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f8f8; padding: 10px; text-align: center; }
        .details { margin-top: 20px; }
        .details table { width: 100%; border-collapse: collapse; }
        .details th, .details td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .details th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Flight Ticket</h2>
        </div>
        <div class="details">
            <table>
                <tr>
                    <th>PNR</th>
                    <td>{{ $ticket['pnr'] }}</td>
                </tr>
                <tr>
                    <th>Booking ID</th>
                    <td>{{ $ticket['booking_id'] }}</td>
                </tr>
                <tr>
                    <th>Passenger Name</th>
                    <td>{{ $ticket['user_name'] }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $ticket['username'] }}</td>
                </tr>
                <tr>
                    <th>Phone Number</th>
                    <td>{{ $ticket['phone_number'] }}</td>
                </tr>
                <tr>
                    <th>Flight</th>
                    <td>{{ $ticket['flight_name'] }} ({{ $ticket['flight_number'] }})</td>
                </tr>
                <tr>
                    <th>Departure</th>
                    <td>{{ $ticket['departure_from'] }} at {{ $ticket['flight_date'] }}</td>
                </tr>
                <tr>
                    <th>Arrival</th>
                    <td>{{ $ticket['arrival_to'] }} at {{ $ticket['return_date'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Total Fare</th>
                    <td>INR {{ number_format($ticket['total_fare'], 2) }}</td>
                </tr>
                <tr>
                    <th>Booking Date</th>
                    <td>{{ $ticket['date_of_booking'] }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>