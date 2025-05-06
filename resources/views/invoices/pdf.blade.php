<!DOCTYPE html>
<html>
<head>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            padding: 30px;
            background: #f8f9fa;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.07);
        }

        .invoice-header {
            margin-bottom: 2rem;
            border-bottom: 2px solid #ddd;
            padding-bottom: 15px;
        }

        .invoice-header h1 {
            margin: 0;
            font-size: 2.5rem;
            color: #1a73e8;
        }

        .invoice-header p {
            margin: 5px 0;
            color: #555;
        }

        .client-info {
            margin-bottom: 1.5rem;
        }

        .client-info h3 {
            margin-bottom: 0.5rem;
            color: #1a73e8;
        }

        .client-info p {
            margin: 3px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        thead {
            background: #1a73e8;
            color: #fff;
        }

        th, td {
            padding: 0.9rem;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            font-weight: 600;
        }

        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        .total {
            font-weight: bold;
            color: #000;
        }

        tfoot td {
            border-top: 2px solid #1a73e8;
            background: #f1f1f1;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
<div class="invoice-box">
    <div class="invoice-header">
        <h1>Invoice #{{ $invoice->invoice_number }}</h1>
        <p>Date: {{ $invoice->invoice_date->format('d M Y') }}</p>
        <p>Due Date: {{ $invoice->due_date->format('d M Y') }}</p>
    </div>

    <div class="client-info">
        <h3>Bill To:</h3>
        <p>{{ $invoice->client_name }}</p>
        <p>{!! nl2br(e($invoice->client_address)) !!}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Unit Cost</th>
                <th>Quantity</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item['description'] }}</td>
                <td>₹{{ number_format($item['unit_cost'], 2) }}</td>
                <td>{{ $item['quantity'] }}</td>
                <td class="text-right">₹{{ number_format($item['unit_cost'] * $item['quantity'], 2) }}</td>
            </tr>
            @endforeach 
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="total">Total Amount:</td>
                <td class="total text-right">₹{{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
</body>
</html>
