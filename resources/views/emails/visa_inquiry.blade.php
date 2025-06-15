<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visa Inquiry Confirmation</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        h1{
            color: #fff;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: #1e40af;
            padding: 20px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 16px;
            color: #ffffff;
        }
        .content {
            padding: 20px;
            color: #333333;
        }
        .content p {
            margin: 0 0 15px;
            font-size: 16px;
        }
        .details {
            background: #f9fafb;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .details h2 {
            font-size: 20px;
            color: #1e40af;
            margin: 0 0 10px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .details-table td {
            padding: 8px;
            font-size: 14px;
            color: #555555;
        }
        .details-table td strong {
            color: #333333;
        }
        .next-steps h2, .contact-info h2 {
            font-size: 20px;
            color: #1e40af;
            margin: 0 0 10px;
        }
        .contact-info {
            background: #eff6ff;
            padding: 15px;
            border-radius: 6px;
        }
        .footer {
            background: #1f2937;
            color: #ffffff;
            text-align: center;
            padding: 15px;
            font-size: 12px;
        }
        .footer p {
            margin: 5px 0;
        }
        @media only screen and (max-width: 600px) {
            .container {
                width: 100%;
                margin: 10px;
            }
            .header h1 {
                font-size: 20px;
            }
            .details-table td {
                display: block;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Visa Inquiry Confirmation</h1>
            <p>Thank you for reaching out to us!</p>
        </div>

        <!-- Content -->
        <div class="content">
            <p>Dear {{ $data['name'] }},</p>
            <p>
                We have successfully received your visa inquiry. Our team will review your request and get back to you soon with further details.
            </p>

            <!-- Inquiry Details -->
            <div class="details">
                <h2>Your Inquiry Details</h2>
                <table class="details-table">
                    <tr>
                        <td><strong>Name:</strong> {{ $data['name'] }}</td>
                        <td><strong>Visa Type:</strong> {{ $data['visa_type'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong> {{ $data['email'] }}</td>
                        <td><strong>Submission Date:</strong> {{ $data['created_at'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Phone:</strong> {{ $data['phone'] }}</td>
                        @if($data['message'])
                            <td><strong>Message:</strong> {{ $data['message'] }}</td>
                        @else
                            <td></td>
                        @endif
                    </tr>
                </table>
            </div>

            <!-- Next Steps -->
            <div class="next-steps">
                <h2>What's Next?</h2>
                <p>
                    Our visa processing team will contact you within the next 2-3 business days to discuss your inquiry and provide guidance on the next steps. Please check your email (including the spam/junk folder) and phone for our response.
                </p>
            </div>

            <!-- Contact Info -->
            <div class="contact-info">
                <h2>Need Assistance?</h2>
                <p>
                    If you have any questions or need immediate assistance, feel free to reach out to us:
                </p>
                <p>
                    <strong>Email:</strong> support@visaagency.com<br>
                    <strong>Phone:</strong> +1-800-123-4567<br>
                    <strong>WhatsApp:</strong> +1-800-123-4567
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© {{ date('Y') }} Visa Agency. All rights reserved.</p>
            <p>123 Visa Street, Global City, World</p>
        </div>
    </div>
</body>
</html>