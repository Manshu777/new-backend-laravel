<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Flight Booking Confirmation</title>
    <style>
        body {
            font-family: 'Helvetica Neue', sans-serif;
            background-color: #f5f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            background: #ffffff;
            max-width: 600px;
            margin: 20px auto;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        .header img {
            width: 100%;
            height: auto;
        }
        .content {
            padding: 30px;
            text-align: center;
        }
        .title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        .subtitle {
            color: #555;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .trip-info {
            font-size: 20px;
            margin: 15px 0;
            font-weight: bold;
            color: #333;
        }
        .info-table {
            width: 100%;
            margin: 20px 0;
            text-align: left;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        .info-table .label {
            color: #555;
            font-weight: bold;
        }
        .btn {
            padding: 12px 20px;
            background-color: #085247;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
            display: inline-block;
            font-size: 14px;
        }
        .receipt {
            margin-top: 20px;
            text-align: left;
            font-size: 14px;
        }
        .receipt p {
            margin: 5px 0;
        }
        .footer {
            background-color: #eef6f9;
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: #555;
        }
        .services img {
            width: 40px;
            margin: 10px;
        }
        .services div {
            display: inline-block;
            text-align: center;
            margin: 10px;
        }
        @media screen and (max-width: 600px) {
            .container {
                margin: 10px;
                padding: 10px;
            }
            .content {
                padding: 20px;
            }
            .info-table td {
                display: block;
                width: 100%;
                box-sizing: border-box;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://yourdomain.com/images/flight-banner.jpg" alt="Flight Banner">
        </div>
        <div class="content">
            <div class="title">Flight Booking Confirmation</div>
            <div class="subtitle">{{ isset($bookingData['Segments'][0]['Origin']['DepTime']) ? \Carbon\Carbon::parse($bookingData['Segments'][0]['Origin']['DepTime'])->format('M d, Y') : 'N/A' }}</div>

            <a href="{{ $bookingData['manage_link'] ?? '#' }}" class="btn">Manage Your Trip</a>

            <div class="trip-info">{{ $bookingData['Origin'] ?? 'N/A' }} → {{ $bookingData['Destination'] ?? 'N/A' }}</div>
            <div class="subtitle">{{ $bookingData['Origin'] ?? 'N/A' }} to {{ $bookingData['Destination'] ?? 'N/A' }}</div>

            @foreach ($bookingData['Segments'] as $index => $segment)
                <table class="info-table">
                    <tr>
                        <td class="label">Airline:</td>
                        <td>{{ $segment['Airline']['AirlineCode'] ?? 'N/A' }} - {{ $segment['Airline']['AirlineName'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Flight Number:</td>
                        <td>{{ $segment['Airline']['FlightNumber'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Departure:</td>
                        <td>{{ isset($segment['Origin']['DepTime']) ? \Carbon\Carbon::parse($segment['Origin']['DepTime'])->format('M d, Y H:i') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Arrival:</td>
                        <td>{{ isset($segment['Destination']['ArrTime']) ? \Carbon\Carbon::parse($segment['Destination']['ArrTime'])->format('M d, Y H:i') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Duration:</td>
                        <td>{{ isset($segment['Duration']) ? floor($segment['Duration'] / 60) . 'h ' . ($segment['Duration'] % 60) . 'm' : 'N/A' }}</td>
                    </tr>
                </table>
            @endforeach

            <table class="info-table">
                <tr>
                    <td class="label">PNR:</td>
                    <td>{{ $bookingData['PNR'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Booking ID:</td>
                    <td>{{ $bookingData['BookingId'] ?? 'N/A' }}</td>
                </tr>
            </table>

            <div class="title">Passenger Details</div>
            <table class="info-table">
                @foreach ($passengerData as $passenger)
                    <tr>
                        <td class="label">Name:</td>
                        <td>{{ $passenger['Title'] ?? '' }} {{ $passenger['FirstName'] ?? '' }} {{ $passenger['LastName'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Pax Type:</td>
                        <td>
                            @if (isset($passenger['PaxType']))
                                {{ $passenger['PaxType'] == 1 ? 'Adult' : ($passenger['PaxType'] == 2 ? 'Child' : 'Infant') }}
                            @else
                                {{ $passenger['PassengerType'] ?? 'N/A' }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Passport No:</td>
                        <td>{{ $passenger['PassportNo'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Contact:</td>
                        <td>{{ $passenger['ContactNo'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Email:</td>
                        <td>{{ $passenger['Email'] ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </table>

            <div class="title">Invoice Details</div>
            <div class="receipt">
                <p><strong>Invoice Number:</strong> {{ $invoiceData['InvoiceNo'] ?? 'N/A' }}</p>
                <p><strong>Invoice Date:</strong> {{ isset($invoiceData['InvoiceCreatedOn']) ? \Carbon\Carbon::parse($invoiceData['InvoiceCreatedOn'])->format('M d, Y') : 'N/A' }}</p>
                <p><strong>Base Fare:</strong> {{ $invoiceData['Currency'] ?? 'USD' }} {{ number_format($invoiceData['BaseFare'] ?? 0, 2) }}</p>
                <p><strong>Taxes:</strong> {{ $invoiceData['Currency'] ?? 'USD' }} {{ number_format($invoiceData['Tax'] ?? 0, 2) }}</p>
                <p><strong>Other Charges:</strong> {{ $invoiceData['Currency'] ?? 'USD' }} {{ number_format($invoiceData['OtherCharges'] ?? 0, 2) }}</p>
                <p><strong>Total:</strong> {{ $invoiceData['Currency'] ?? 'USD' }} {{ number_format($invoiceData['InvoiceAmount'] ?? 0, 2) }}</p>
            </div>

            <a href="{{ $bookingData['download_link'] ?? '#' }}" class="btn">Download Ticket</a>
            <a href="{{ $bookingData['change_link'] ?? '#' }}" class="btn">Make Changes</a>
        </div>

        <div class="footer">
            <div>Additional Services</div>
            <div class="services">
                <div>
                    <img src="https://yourdomain.com/icons/hotel.png" alt="Hotel"><br>Hotels
                </div>
                <div>
                    <img src="https://yourdomain.com/icons/car.png" alt="Car Rental"><br>Car Rental
                </div>
                <div>
                    <img src="https://yourdomain.com/icons/insurance.png" alt="Insurance"><br>Insurance
                </div>
                <div>
                    <img src="https://yourdomain.com/icons/food.png" alt="Breakfast"><br>Breakfast
                </div>
            </div>
            <div>Need help? Contact us at <a href="mailto:info@nextgentrip.com">info@nextgentrip.com</a></div>
        </div>
    </div>
</body>
</html>