<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Request - Café Lamees</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h1 {
            color: #064e3b;
            text-align: center;
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
        .details {
            background: #f8fdf8;
            padding: 20px;
            border-radius: 8px;
            border-left: 5px solid #064e3b;
            margin: 20px 0;
        }
        .label {
            font-weight: bold;
            color: #064e3b;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            color: #666;
            font-size: 14px;
        }
        hr {
            border: none;
            border-top: 1px solid #eee;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>New Contact / Booking Request</h1>
        <p class="subtitle">A new message has been submitted via the website.</p>

        <hr>

        <div class="details">
            <p><span class="label">Name:</span> {{ $contact->name }}</p>
            <p><span class="label">Email:</span> {{ $contact->email }}</p>
            <p><span class="label">Phone:</span> {{ $contact->phone }}</p>

            @if($contact->service)
                <p><span class="label">Service:</span> {{ $contact->service }}</p>
            @endif

            @if($contact->preferred_date)
                <p><span class="label">Preferred Date:</span> {{ $contact->preferred_date->format('d M Y') }}</p>
            @endif

            <p><span class="label">Message:</span></p>
            <p style="margin-top: 8px; background: #fff; padding: 15px; border-radius: 6px;">
                {{ $contact->message ?? 'No message provided.' }}
            </p>
        </div>

        <p style="color: #666;">
            <strong>Submitted at:</strong> {{ $contact->created_at->format('d M Y, h:i A') }}
        </p>

        <div class="footer">
            Best regards,<br>
            <strong>Café Lamees System</strong>
        </div>
    </div>
</body>
</html>