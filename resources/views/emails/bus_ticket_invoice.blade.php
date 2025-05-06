<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bus Ticket - NextGenTrip</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .container {
            border: 1px solid #ddd;
            padding: 20px;
        }
        h2, h4 {
            color: #007bff;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 8px 10px;
            border-bottom: 1px solid #ccc;
            text-align: left;
        }
        .footer {
            margin-top: 25px;
            font-size: 12px;
            color: #555;
        }
        .company-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-header h1 {
            margin: 0;
            font-size: 28px;
            color: #333;
        }
        .company-header p {
            margin: 4px 0;
            font-size: 14px;
            color: #555;
        }
        hr {
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- Company Header -->
        <div class="company-header">
            <h1>NextGenTrip</h1>
            <img src="https://nextgentrip.com/images/NextGenTrip.jpg" width="100" alt="NextGenTrip Logo">


            <p>Email: info@nextgentrip.com </p>
            <p>Website: www.nextgentrip.com</p>
        </div>

        <hr>

        <h2>Bus Ticket Confirmation</h2>

        <p><strong>Passenger Name:</strong> {{ $passenger_name }}</p>
        <p><strong>Email:</strong> {{ $email }}</p>
        <p><strong>Phone:</strong> {{ $phone }}</p>
        <p><strong>Status:</strong> {{ $status }}</p>

        <h4>Trip Details</h4>
        <table>
            <tr><th>From</th><td>{{ $source }}</td></tr>
            <tr><th>To</th><td>{{ $destination }}</td></tr>
            <tr><th>Departure Time</th><td>{{ $departure }}</td></tr>
            <tr><th>Arrival Time</th><td>{{ $arrival }}</td></tr>
            <tr><th>Bus Name</th><td>{{ $bus_name }}</td></tr>
            <tr><th>Seat Number</th><td>{{ $seat_no }}</td></tr>
        </table>

        <h4>Payment Details</h4>
        <table>
            <tr><th>Ticket No</th><td>{{ $ticket_no }}</td></tr>
            <tr><th>PNR</th><td>{{ $pnr }}</td></tr>
            <tr><th>Invoice Number</th><td>{{ $invoice_no }}</td></tr>
            <tr><th>Amount Paid</th><td>&#8377;{{ number_format($amount, 2) }}</td></tr>
        </table>

        <p class="footer">Thank you for booking with <strong>NextGenTrip</strong>. Please carry a valid ID during your journey. For any assistance, contact our support team.</p>
    </div>
</body>
</html>
