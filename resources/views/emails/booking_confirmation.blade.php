<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Flight Booking Confirmation</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 700px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(90deg, #005555, #008080);
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .header img {
            max-width: 150px;
            height: auto;
        }
        .header h1 {
            margin: 10px 0;
            font-size: 24px;
            font-weight: 600;
        }
        .section {
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
        }
        .section:last-child {
            border-bottom: none;
        }
        .section h2 {
            color: #005555;
            font-size: 20px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .table th, .table td {
            border: 1px solid #e0e0e0;
            padding: 10px;
            text-align: left;
            font-size: 14px;
        }
        .table th {
            background-color: #f8f8f8;
            font-weight: 600;
            color: #333333;
        }
        .table td {
            background-color: #ffffff;
        }
        .table .total {
            font-weight: 700;
            background-color: #f0f0f0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .info-grid p {
            margin: 5px 0;
            font-size: 14px;
        }
        .info-grid p strong {
            color: #005555;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #777777;
            background-color: #f8f8f8;
        }
        .footer a {
            color: #008080;
            text-decoration: none;
        }
        .highlight {
            background-color: #e6f3f3;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        @media screen and (max-width: 600px) {
            .container {
                margin: 10px;
                padding: 10px;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>


 <body>
    <div class="container">
        <div class="header">
            <!-- Replace with your logo URL -->
            <img src="https://via.placeholder.com/150x50?text=Your+Logo" alt="Travel Agency Logo">
            <h1>Flight Booking Confirmation</h1>
        </div>

        <div class="section">
            <h2>Booking Details</h2>
            <div class="info-grid">
                <p><strong>PNR:</strong> {{ $bookingData['PNR'] ?? 'N/A' }}</p>
                <p><strong>Booking ID:</strong> {{ $bookingData['BookingId'] ?? 'N/A' }}</p>
                <p><strong>Origin:</strong> {{ $bookingData['Origin'] ?? 'N/A' }}</p>
                <p><strong>Destination:</strong> {{ $bookingData['Destination'] ?? 'N/A' }}</p>
            </div>
            @foreach ($bookingData['Segments'] as $index => $segment)
                <div class="segment">
                    <h3>Flight Segment {{ $index + 1 }}</h3>
                    <div class="info-grid">
                        <p><strong>Airline:</strong> {{ $segment['Airline']['AirlineCode'] ?? 'N/A' }} - {{ $segment['Airline']['AirlineName'] ?? 'N/A' }}</p>
                        <p><strong>Flight Number:</strong> {{ $segment['Airline']['FlightNumber'] ?? 'N/A' }}</p>
                        <p><strong>Departure:</strong> {{ isset($segment['Origin']['DepTime']) ? \Carbon\Carbon::parse($segment['Origin']['DepTime'])->format('M d, Y H:i') : 'N/A' }}</p>
                        <p><strong>Arrival:</strong> {{ isset($segment['Destination']['ArrTime']) ? \Carbon\Carbon::parse($segment['Destination']['ArrTime'])->format('M d, Y H:i') : 'N/A' }}</p>
                        <p><strong>Duration:</strong> {{ isset($segment['Duration']) ? floor($segment['Duration'] / 60) . 'h ' . ($segment['Duration'] % 60) . 'm' : 'N/A' }}</p>
                    </div>
                </div>
            @endforeach
            <div class="highlight">
                <p><strong>Travel Date:</strong> {{ isset($bookingData['Segments'][0]['Origin']['DepTime']) ? \Carbon\Carbon::parse($bookingData['Segments'][0]['Origin']['DepTime'])->format('M d, Y') : 'N/A' }}</p>
            </div>
        </div>

        <div class="section">
            <h2>Passenger Details</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Pax Type</th>
                        <th>Passport No</th>
                        <th>Contact</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($passengerData as $passenger)
                        <tr>
                            <td>{{ $passenger['Title'] ?? '' }} {{ $passenger['FirstName'] ?? '' }} {{ $passenger['LastName'] ?? '' }}</td>
                            <td>
                                @if (isset($passenger['PaxType']))
                                    {{ $passenger['PaxType'] == 1 ? 'Adult' : ($passenger['PaxType'] == 2 ? 'Child' : 'Infant') }}
                                @else
                                    {{ $passenger['PassengerType'] ?? 'N/A' }}
                                @endif
                            </td>
                            <td>{{ $passenger['PassportNo'] ?? 'N/A' }}</td>
                            <td>{{ $passenger['ContactNo'] ?? 'N/A' }}</td>
                            <td>{{ $passenger['Email'] ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>Invoice Details</h2>
            <div class="info-grid">
                <p><strong>Invoice Number:</strong> {{ $invoiceData['InvoiceNo'] ?? 'N/A' }}</p>
                <p><strong>Invoice Date:</strong> {{ isset($invoiceData['InvoiceCreatedOn']) ? \Carbon\Carbon::parse($invoiceData['InvoiceCreatedOn'])->format('M d, Y') : 'N/A' }}</p>
                <!-- <p><strong>Commission Earned:</strong> {{ $invoiceData['Currency'] ?? 'USD' }} {{ number_format($invoiceData['CommissionEarned'] ?? 0, 2) }}</p> -->
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Base Fare</td>
                        <td>{{ $invoiceData['Currency'] ?? 'USD' }} {{ number_format($invoiceData['BaseFare'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Taxes</td>
                        <td>{{ $invoiceData['Currency'] ?? 'USD' }} {{ number_format($invoiceData['Tax'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Other Charges</td>
                        <td>{{ $invoiceData['Currency'] ?? 'USD' }} {{ number_format($invoiceData['OtherCharges'] ?? 0, 2) }}</td>
                    </tr>

                    <tr class="total">
                        <td><strong>Total</strong></td>
                        <td><strong>{{ $invoiceData['Currency'] ?? 'USD' }} {{ number_format($invoiceData['InvoiceAmount'] ?? 0, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>Thank you for choosing us! For support, contact <a href="mailto:info@nextgentrip.com">info@nextgentrip.com</a> or call +91-98775 79319.</p>
            <p>© {{ date('Y') }} Your Travel Agency. All rights reserved.</p>
        </div>
    </div>
</body>

</html>