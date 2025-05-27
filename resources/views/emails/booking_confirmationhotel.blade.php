<!DOCTYPE html>
<html>
<head>
    <title>Hotel Booking Confirmation</title>
</head>
<body>
    <h1>Hotel Booking Confirmation</h1>
    <p>Dear {{ $bookingDetails['HotelRoomsDetails'][0]['HotelPassenger'][0]['FirstName'] }} {{ $bookingDetails['HotelRoomsDetails'][0]['HotelPassenger'][0]['LastName'] }},</p>
    <p>Thank you for your booking! Your hotel reservation has been confirmed.</p>
    
    <h2>Booking Details</h2>
    <ul>
        <li><strong>Booking ID:</strong> {{ $bookingDetails['BookingId'] }}</li>
        <li><strong>Confirmation Number:</strong> {{ $bookingDetails['ConfirmationNo'] }}</li>

        <!-- <li><strong>Booking Reference:</strong> {{ $bookingDetails['BookingRefNo'] }}</li> -->
        <li><strong>Status:</strong> {{ $bookingDetails['HotelBookingStatus'] }}</li>
        <li><strong>Total Amount:</strong> {{ $bookingDetails['NetAmount'] }}</li>
        <!-- <li><strong>Guest Nationality:</strong> {{ $bookingDetails['GuestNationality'] }}</li> -->
    </ul>

    <h2>Guest Details</h2>
    @foreach ($bookingDetails['HotelRoomsDetails'] as $room)
        <h3>Room {{ $loop->iteration }}</h3>
        @foreach ($room['HotelPassenger'] as $passenger)
            <ul>
                <li><strong>Name:</strong> {{ $passenger['Title'] }} {{ $passenger['FirstName'] }} {{ $passenger['LastName'] }}</li>
                <li><strong>Email:</strong> {{ $passenger['Email'] }}</li>
                <li><strong>Phone:</strong> {{ $passenger['Phoneno'] }}</li>
                <li><strong>Age:</strong> {{ $passenger['Age'] }}</li>
                <li><strong>Pax Type:</strong> {{ $passenger['PaxType'] == 1 ? 'Adult' : 'Child' }}</li>
            </ul>
        @endforeach
    @endforeach

    <p>Please find the detailed booking confirmation attached as a PDF.</p>
    <p>Thank you for choosing our service!</p>
</body>
</html>