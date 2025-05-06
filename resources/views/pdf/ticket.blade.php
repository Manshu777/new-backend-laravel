<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Flight Ticket Invoice</title>
  <style>
    body {
      background-color: #f3f4f6;
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 700px;
      margin: 40px auto;
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      overflow: hidden;
    }
    .header {
      background-color: #2563eb;
      color: white;
      padding: 16px 24px;
      display: flex;
      align-items: center;
    }
    .header img {
      height: 40px;
      margin-right: 16px;
    }
    .section {
      padding: 24px;
      border-top: 1px solid #e5e7eb;
    }
    h3 {
      margin-top: 0;
      color: #111827;
    }
    .label {
      color: #6b7280;
      font-size: 14px;
    }
    .value {
      font-weight: 500;
      font-size: 14px;
    }
    table.details {
      width: 100%;
      border-collapse: collapse;
      margin-top: 12px;
    }
    table.details th, table.details td {
      border: 1px solid #e5e7eb;
      padding: 8px;
      font-size: 14px;
      text-align: left;
    }
    table.details th {
      background-color: #f9fafb;
    }
    .footer {
      text-align: center;
      font-size: 12px;
      color: #6b7280;
      padding: 16px;
      background: #f9fafb;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <img src="{{ asset('/images/logo.png') }}" alt="Logo">
      <div>
        <h2 style="margin:0;">e-Ticket Invoice</h2>
        <div style="font-size:14px;">Booking ID: {{ $ticket['booking_id'] }} | Issued: {{ $ticket['issued_date'] }}</div>
      </div>
    </div>

    <div class="section">
      <h3>Passenger Details</h3>
      <p><span class="label">Name:</span> <span class="value">{{ $ticket['passenger_name'] }}</span></p>
      <p><span class="label">Gender:</span> <span class="value">{{ $ticket['gender'] }}</span></p>
      <p><span class="label">Nationality:</span> <span class="value">{{ $ticket['nationality'] }}</span></p>
      <p><span class="label">Ticket No:</span> <span class="value">{{ $ticket['ticket_no'] }}</span></p>
    </div>

    <div class="section">
      <h3>Flight Information</h3>
      <table class="details">
        <thead>
          <tr>
            <th>Route</th>
            <th>Date</th>
            <th>Flight</th>
            <th>Time</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
        
            <tr>
              <td>{{ $ticket['full_route'] }}</td>
              <td>{{ $ticket['flight_date'] }}</td>
              <td>{{ $ticket['flight_name'] ?? $ticket['airline'] ?? 'N/A' }}</td>
             
              <td>{{ $ticket['status'] }}</td>
            </tr>
         
        </tbody>
      </table>
    </div>

    <div class="section">
      <h3>Fare Summary</h3>
      <table class="details">
        <tr>
          <th>Base Fare</th>
          <th>Tax</th>
          <th>Total (BDT)</th>
        </tr>
        <tr>
          <td>{{ $ticket['base_fare'] }}</td>
          <td>{{ $ticket['tax'] }}</td>
          <td><strong>{{ $ticket['total_bdt'] }}</strong></td>
        </tr>
      </table>
    </div>

    <div class="section">
      <p>✈️ <strong>Route:</strong> {{ $ticket['full_route'] }}<br>
         🗓️ <strong>Date:</strong> {{ $ticket['flight_date'] }}</p>
    </div>

    <div class="footer">
      This is an auto-generated invoice from [Your Company Name].
    </div>
  </div>
</body>
</html>
