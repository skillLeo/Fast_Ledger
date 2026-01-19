<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            background: #01677d;
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px 20px;
        }
        .invoice-details {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .invoice-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-details td {
            padding: 8px 0;
        }
        .invoice-details td:first-child {
            font-weight: bold;
            color: #666;
            width: 40%;
        }
        .amount {
            font-size: 28px;
            font-weight: bold;
            color: #01677d;
            text-align: center;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background: #01677d;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📄 Invoice from {{ $businessName }}</h1>
        </div>

        <div class="content">
            <p>Dear {{ $customerName }},</p>

            <p>Thank you for your business. Please find your invoice details below:</p>

            <div class="invoice-details">
                <table>
                    <tr>
                        <td>Invoice Number:</td>
                        <td><strong>{{ $invoiceNo }}</strong></td>
                    </tr>
                    <tr>
                        <td>Invoice Date:</td>
                        <td>{{ $invoiceDate }}</td>
                    </tr>
                    <tr>
                        <td>Due Date:</td>
                        <td>{{ $dueDate }}</td>
                    </tr>
                </table>
            </div>

            <div class="amount">
                £{{ $totalAmount }}
            </div>

            <p style="text-align: center;">
                <strong>Total Amount Due</strong>
            </p>

            <p>The invoice PDF is attached to this email for your records.</p>

            <p>If you have any questions about this invoice, please don't hesitate to contact us.</p>

            <p>Best regards,<br>
            <strong>{{ $businessName }}</strong></p>
        </div>

        <div class="footer">
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>&copy; {{ date('Y') }} {{ $businessName }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>