<!DOCTYPE html>
<html>
<head>
    <title>Bus Booking Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; }
        .section { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bus Booking Confirmation</h1>
        </div>
        
        <div class="section">
            <h2>Booking Details</h2>
            <table>
                <tr>
                    <th>Booking Status</th>
                    <td>{{ $booking['BusBookingStatus'] }}</td>
                </tr>
                <tr>
                    <th>Ticket Number</th>
                    <td>{{ $booking['TicketNo'] }}</td>
                </tr>
                <tr>
                    <th>Invoice Number</th>
                    <td>{{ $booking['InvoiceNumber'] }}</td>
                </tr>
                <tr>
                    <th>Invoice Amount</th>
                    <td>{{ $booking['InvoiceAmount'] }}</td>
                </tr>
            </table>
        </div>
        
        <div class="section">
            <h2>Passenger Details</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($passengers as $passenger)
                        <tr>
                            <td>{{ $passenger['name'] }}</td>
                            <td>{{ $passenger['email'] }}</td>
                            <td>{{ $passenger['phone'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>