<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Invoice</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px;">
  <table width="100%" cellpadding="10" cellspacing="0" style="background: #fff; max-width: 700px; margin: auto; border: 1px solid #ddd;">
    <tr>
      <td colspan="2" style="text-align:center;">
        <h2>e-Ticket Invoice</h2>
        <p><strong>Booking ID:</strong> {{ $ticket['booking_id'] }}<br>Issued: {{ $ticket['issued_date'] }}</p>
        <p><strong>Status:</strong> Confirmed</p>
      </td>
    </tr>

    <tr>
      <td colspan="2"><hr></td>
    </tr>

    <tr>
      <td colspan="2">
        <h3>Passenger Details</h3>
        <p><strong>Name:</strong> {{ $ticket['passenger_name'] }}<br>
        <strong>Gender:</strong> {{ $ticket['gender'] }}<br>
        <strong>Nationality:</strong> {{ $ticket['nationality'] }}<br>
        <strong>Ticket No:</strong> {{ $ticket['ticket_no'] }}</p>
      </td>
    </tr>

    <tr>
      <td colspan="2">
        <h3>Flight Information</h3>
        <table width="100%" border="1" cellspacing="0" cellpadding="8" style="border-collapse: collapse;">
          <tr style="background-color: #f0f0f0;">
            <th>Route</th>
            <th>Date</th>
            <th>Flight</th>
            <th>Time</th>
            <th>Status</th>
          </tr>
       
   
            <tr>
              <td>{{ $ticket['full_route'] }}</td>
              <td>{{ $ticket['flight_date'] }}</td>
              <td>{{ $ticket['flight_name'] ?? $ticket['airline'] ?? 'N/A' }}</td> <!-- Safe access -->

              <td>{{ $ticket['status'] }}</td>
            </tr>
           
        </table>
      </td>
    </tr>

    <tr>
      <td colspan="2">
        <h3>Fare Summary</h3>
        <table width="100%" border="1" cellspacing="0" cellpadding="8" style="border-collapse: collapse;">
          <tr>
            <td>Base Fare</td>
            <td>Tax</td>
            <td>Total (BDT)</td>
          </tr>
          <tr>
            <td>{{ $ticket['base_fare'] }}</td>
            <td>{{ $ticket['tax'] }}</td>
            <td><strong>{{ $ticket['total_bdt'] }}</strong></td>
          </tr>
        </table>
        <p style="text-align:right;"><strong>USD Equivalent:</strong> ${{ $ticket['usd_amount'] }}<br>
        <strong>Rate:</strong> 1 USD = {{ $ticket['conversion_rate'] }} BDT</p>
      </td>
    </tr>

    <tr>
      <td colspan="2">
        <p>✈️ <strong>Route:</strong> {{ $ticket['full_route'] }}<br>
        🗓️ <strong>Date:</strong> {{ $ticket['flight_date'] }}</p>
      </td>
    </tr>

    <tr>
      <td colspan="2" style="text-align: center; font-size: 12px; color: #999;">
        <p>This is an auto-generated invoice from [Your Company Name]</p>
      </td>
    </tr>
  </table>
</body>
</html>