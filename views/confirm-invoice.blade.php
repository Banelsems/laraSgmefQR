<!DOCTYPE html>
<html>
<head>
    <title>Confirm Invoice</title>
</head>
<body>
    <h1>Confirm Invoice</h1>

    <img src="{{ $qrcode }}" alt="QR code" />

    <p>Invoice data:</p>
    <ul>
        <li>UID: {{ $data['uid'] }}</li>
        <li>Status: {{ $data['status'] }}</li>
        <li>Amount: {{ $data['amount'] }}</li>
        <li>Created at: {{ $data['createdAt'] }}</li>
    </ul>
</body>
</html>
