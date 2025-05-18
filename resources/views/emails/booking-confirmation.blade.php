<!DOCTYPE html>
<html>
<head>
    <title>Booking Confirmation</title>
</head>
<body>
    <h1>Bus Booking Confirmation</h1>
    
    <h2>Booking Details</h2>
    <p><strong>Booking Status:</strong> {{ $booking['BusBookingStatus'] }}</p>
    <p><strong>Ticket Number:</strong> {{ $booking['TicketNo'] }}</p>
    <p><strong>Invoice Number:</strong> {{ $booking['InvoiceNumber'] }}</p>
    <p><strong>Invoice Amount:</strong> {{ $booking['InvoiceAmount'] }}</p>
    
    <h2>Passenger Details</h2>
    @foreach ($passengers as $passenger)
        <p><strong>Name:</strong> {{ $passenger['name'] }}</p>
        <p><strong>Email:</strong> {{ $passenger['email'] }}</p>
        <p><strong>Phone:</strong> {{ $passenger['phone'] }}</p>
        <hr>
    @endforeach
    
    <p>Please find your booking confirmation attached as a PDF.</p>
    
    <p>Thank you for booking with us!</p>
</body>
</html>