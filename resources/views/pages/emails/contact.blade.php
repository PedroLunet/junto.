<!DOCTYPE html>
<html>
<head>
    <title>New Contact Message</title>
</head>
<body>
    <h1>New Contact Message from {{ $mailData['name'] }}</h1>
    
    <p><strong>Name:</strong> {{ $mailData['name'] }}</p>
    <p><strong>Email:</strong> {{ $mailData['email'] ?? 'Not provided' }}</p>
    
    <p><strong>Message:</strong></p>
    <p>{{ $mailData['message'] }}</p>
</body>
</html>
