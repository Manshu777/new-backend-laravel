<!DOCTYPE html>
<html>
<head>
    <title>Flight Booking Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { color: #333; }
        .details { margin-top: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Flight Booking Confirmation</h1>

    <p>Thank you for your booking. Below are your flight details:</p>

    <div class="details">
        <h2>Flight Details</h2>
        <p><strong>PNR:</strong> {{ $ticket['PNR'] }}</p>
        <p><strong>Booking ID:</strong> {{ $ticket['BookingId'] }}</p>
        <p><strong>Flight:</strong> {{ $ticket['AirlineName'] }} {{ $ticket['FlightNumber'] }}</p>
        <p><strong>From:</strong> {{ $ticket['Origin'] }} at {{ $ticket['DepTime'] }}</p>
        <p><strong>To:</strong> {{ $ticket['Destination'] }} at {{ $ticket['ArrTime'] }}</p>
    </div>

    <div class="details">
        <h2>Passenger Details</h2>
        <table>
            <tr>
                <th>Title</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Type</th>
            </tr>
            @foreach ($ticket['Passenger'] as $passenger)
                <tr>
                    <td>{{ $passenger['Title'] }}</td>
                    <td>{{ $passenger['FirstName'] }}</td>
                    <td>{{ $passenger['LastName'] }}</td>
                    <td>{{ $passenger['PassengerType'] }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="details">
        <h2>Invoice Details</h2>
        <p><strong>Invoice No:</strong> {{ $ticket['InvoiceNo'] }}</p>
        <p><strong>Amount:</strong> {{ $ticket['InvoiceAmount'] }} {{ $ticket['Currency'] }}</p>
        <p><strong>Base Fare:</strong> {{ $ticket['BaseFare'] }}</p>
        <p><strong>Tax:</strong> {{ $ticket['Tax'] }}</p>
        <p><strong>Other Charges:</strong> {{ $ticket['OtherCharges'] }}</p>
        <p><strong>Created On:</strong> {{ $ticket['InvoiceCreatedOn'] }}</p>
    </div>
</body>
</html>