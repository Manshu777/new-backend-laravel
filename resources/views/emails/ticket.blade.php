<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Flight Ticket</title>
    <style>
        body {
            background-color: #f3f4f6;
            font-family: sans-serif;
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
            padding: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header img {
            height: 40px;
        }
        .content {
            padding: 24px;
        }
        .grid {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
        }
        .grid-item {
            width: calc(50% - 8px);
            font-size: 14px;
        }
        .label {
            color: #6b7280;
            margin-bottom: 4px;
        }
        .value {
            font-weight: 500;
        }
        .green {
            color: #16a34a;
        }
    </style>
</head>
<body>
    <div class="container">
     
     

       
        <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; font-family:sans-serif;">
  <tr>
    <td align="center">
      <table width="700" cellpadding="0" cellspacing="0" style="background:#fff; border-radius:10px; box-shadow:0 4px 6px rgba(0,0,0,0.1);">
        <tr style="background-color:#2563eb; color:#fff;">
          <td style="padding:16px; display:flex; align-items:center;">
            <img src="{{ asset('/images/logo.png') }}" alt="Logo" style="height:40px;">
            <h2 style="margin-left:20px;">Flight Ticket</h2>
          </td>
        </tr>
        <tr>
          <td style="padding:24px;">
            <table width="100%" cellpadding="8" cellspacing="0">
              @foreach($ticket as $key => $value)
                @if(!is_array($value))
                  <tr>
                    <td width="30%" style="color:#6b7280; font-size:14px;">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                    <td style="font-weight:500; font-size:14px;">{{ $value }}</td>
                  </tr>
                @endif
              @endforeach
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

    </div>
</body>
</html>














