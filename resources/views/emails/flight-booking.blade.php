<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Booking Confirmation</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif; font-size: 16px; color: #333;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #0066cc; color: #ffffff; text-align: center; padding: 20px; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                            <h1 style="margin: 0; font-size: 24px; font-weight: bold;">Next Gen Trip Pvt Ltd</h1>
                            <p style="margin: 5px 0 0; font-size: 16px;">Flight Booking Confirmation</p>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 20px;">
                            <!-- Greeting -->
                            <p style="margin: 0 0 20px; font-size: 16px;">Dear {{ $ticket['user_name'] }},</p>
                            <p style="margin: 0 0 20px; font-size: 16px;">Thank you for booking with YourApp Travel! Your flight booking has been successfully confirmed. Below are the details:</p>

                            <!-- Booking Details -->
                            <h2 style="font-size: 18px; font-weight: bold; color: #0066cc; margin: 20px 0 10px;">Booking Details</h2>
                            <table width="100%" cellpadding="5" cellspacing="0" style="font-size: 14px;">
                                <tr>
                                    <td style="width: 50%;"><strong>PNR:</strong> {{ $ticket['pnr'] }}</td>
                                    <td style="width: 50%;"><strong>Booking ID:</strong> {{ $ticket['booking_id'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Booking Date:</strong> {{ $ticket['date_of_booking'] }}</td>
                                    <td><strong>Invoice No:</strong> {{ $ticket['invoice_no'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Invoice Amount:</strong> {{ $ticket['total_fare'] }} {{ $ticket['passengers'][0]['Fare']['Currency'] }}</td>
                                    <td></td>
                                </tr>
                            </table>

                            <!-- Flight Details -->
                            <h2 style="font-size: 18px; font-weight: bold; color: #0066cc; margin: 20px 0 10px;">Flight Details</h2>
                            <table width="100%" cellpadding="5" cellspacing="0" style="background-color: #f9f9f9; border-radius: 4px; font-size: 14px;">
                                <tr>
                                    <td><strong>Flight:</strong> {{ $ticket['flight_name'] }} ({{ $ticket['flight_number'] }})</td>
                                </tr>
                                <tr>
                                    <td><strong>Route:</strong> {{ $ticket['full_route'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Departure:</strong> {{ \Carbon\Carbon::parse($ticket['segments'][0]['Origin']['DepTime'])->format('d F Y, H:i') }} (Terminal {{ $ticket['segments'][0]['Origin']['Airport']['Terminal'] }})</td>
                                </tr>
                                <tr>
                                    <td><strong>Arrival:</strong> {{ \Carbon\Carbon::parse($ticket['segments'][0]['Destination']['ArrTime'])->format('d F Y, H:i') }} (Terminal {{ $ticket['segments'][0]['Destination']['Airport']['Terminal'] }})</td>
                                </tr>
                                <tr>
                                    <td><strong>Baggage:</strong> {{ $ticket['segments'][0]['Baggage'] }} (Cabin: {{ $ticket['segments'][0]['CabinBaggage'] }})</td>
                                </tr>
                            </table>

                            <!-- Passenger Details -->
                            <h2 style="font-size: 18px; font-weight: bold; color: #0066cc; margin: 20px 0 10px;">Passenger Details</h2>
                            @foreach ($ticket['passengers'] as $passenger)
                                <table width="100%" cellpadding="5" cellspacing="0" style="background-color: #f9f9f9; border-radius: 4px; font-size: 14px; margin-bottom: 10px;">
                                    <tr>
                                        <td><strong>Name:</strong> {{ $passenger['Title'] }} {{ $passenger['FirstName'] }} {{ $passenger['LastName'] }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Passport:</strong> {{ $passenger['PassportNo'] }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Contact:</strong> {{ $passenger['ContactNo'] }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong> {{ $passenger['Email'] }}</td>
                                    </tr>
                                </table>
                            @endforeach

                            <!-- Fare Breakdown -->
                            <h2 style="font-size: 18px; font-weight: bold; color: #0066cc; margin: 20px 0 10px;">Fare Breakdown</h2>
                            <table width="100%" cellpadding="5" cellspacing="0" style="background-color: #f9f9f9; border-radius: 4px; font-size: 14px;">
                                <tr>
                                    <td><strong>Base Fare:</strong> {{ $ticket['passengers'][0]['Fare']['BaseFare'] }} {{ $ticket['passengers'][0]['Fare']['Currency'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Taxes:</strong> {{ $ticket['passengers'][0]['Fare']['Tax'] }} {{ $ticket['passengers'][0]['Fare']['Currency'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Other Charges:</strong> {{ $ticket['passengers'][0]['Fare']['OtherCharges'] }} {{ $ticket['passengers'][0]['Fare']['Currency'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Fare:</strong> {{ $ticket['passengers'][0]['Fare']['PublishedFare'] }} {{ $ticket['passengers'][0]['Fare']['Currency'] }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9f9f9; text-align: center; padding: 20px; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                            <p style="margin: 0; font-size: 14px; color: #666;">For any queries, contact us at <a href="mailto:support@yourapp.com" style="color: #0066cc; text-decoration: none;">support@yourapp.com</a> or call {{ $ticket['airline_toll_free_no'] }}.</p>
                            <p style="margin: 10px 0 0; font-size: 14px; color: #666;">Thank you for choosing YourApp Travel!</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>