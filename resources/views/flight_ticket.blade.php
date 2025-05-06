<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NextGenTrip - Your Flight Ticket</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .ticket-container {
            background: #ffffff;
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: #007bff;
            color: #ffffff;
            padding: 10px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .ticket-details {
            padding: 20px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h2 {
            color: #007bff;
            font-size: 18px;
            margin-bottom: 10px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .details-table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .details-table .label {
            font-weight: bold;
            color: #333;
        }
        .flight-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-top: 20px;
        }
        .support-link {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <div class="header">
            <h1>Your Flight Ticket - NextGenTrip</h1>
        </div>
        <div class="ticket-details">
            <!-- Passenger Details -->
            <div class="section">
                <h2>Passenger Details</h2>
                <table class="details-table">
                    <tr>
                        <td class="label">Name</td>
                        <td>{{ $passenger['Title'] }} {{ $passenger['FirstName'] }} {{ $passenger['LastName'] }}</td>
                    </tr>
                    <tr>
                        <td class="label">Email</td>
                        <td>{{ $passenger['Email'] }}</td>
                    </tr>
                    <tr>
                        <td class="label">Contact</td>
                        <td>{{ $passenger['ContactNo'] }}</td>
                    </tr>
                    <tr>
                        <td class="label">PNR</td>
                        <td>{{ $booking['PNR'] }}</td>
                    </tr>
                    <tr>
                        <td class="label">Booking ID</td>
                        <td>{{ $booking['BookingId'] }}</td>
                    </tr>
                </table>
            </div>

            <!-- Flight Details -->
            <div class="section">
                <h2>Flight Details</h2>
                <div class="flight-info">
                    <div>
                        <strong>Departure:</strong><br>
                        {{ $segment['Origin']['Airport']['CityName'] }} ({{ $segment['Origin']['Airport']['AirportCode'] }})<br>
                        {{ \Carbon\Carbon::parse($segment['Origin']['DepTime'])->format('d M Y, H:i') }}
                    </div>
                    <div>
                        <strong>Arrival:</strong><br>
                        {{ $segment['Destination']['Airport']['CityName'] }} ({{ $segment['Destination']['Airport']['AirportCode'] }})<br>
                        {{ \Carbon\Carbon::parse($segment['Destination']['ArrTime'])->format('d M Y, H:i') }}
                    </div>
                </div>
                <table class="details-table">
                    <tr>
                        <td class="label">Airline</td>
                        <td>{{ $segment['Airline']['AirlineName'] }} ({{ $segment['Airline']['AirlineCode'] }}-{{ $segment['Airline']['FlightNumber'] }})</td>
                    </tr>
                    <tr>
                        <td class="label">Duration</td>
                        <td>{{ floor($segment['Duration'] / 60) }}h {{ $segment['Duration'] % 60 }}m</td>
                    </tr>
                    <tr>
                        <td class="label">Baggage</td>
                        <td>{{ $segment['Baggage'] }}</td>
                    </tr>
                    <tr>
                        <td class="label">Cabin Baggage</td>
                        <td>{{ $segment['CabinBaggage'] }}</td>
                    </tr>
                </table>
            </div>

            <!-- Fare Details -->
            <div class="section">
                <h2>Fare Details</h2>
                <table class="details-table">
                    <tr>
                        <td class="label">Base Fare</td>
                        <td>{{ $fare['Currency'] }} {{ $fare['BaseFare'] }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tax</td>
                        <td>{{ $fare['Currency'] }} {{ $fare['Tax'] }}</td>
                    </tr>
                    <tr>
                        <td class="label">Total Fare</td>
                        <td>{{ $fare['Currency'] }} {{ $fare['PublishedFare'] }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="footer">
            <p>Thank you for choosing <strong>NextGenTrip</strong>!<br>
            Need help? Contact us at <a href="mailto:support@nextgentrip.com" class="support-link">support@nextgentrip.com</a></p>
        </div>
    </div>
</body>
</html>