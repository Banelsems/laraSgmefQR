<!DOCTYPE html>
<html>
<head>
    <title>Invoice Normalize</title>
    <link href="https://cdn.tailwindcss.com/css/tailwind.min.css" rel="stylesheet">
</head>
<body>
    <section class="bg-gray-100">
        <h1 class="text-center text-xl font-bold">Invoice</h1>

        @if ($errors->any())
            <div class="bg-red-500 text-white text-center p-4">
                @foreach ($errors->all() as $error)
                    {{ $error }}
                @endforeach
            </div>
        @endif

        <p>UID: {{ $invoice->uid }}</p>
        <p>Status: {{ $invoice->status }}</p>
        <p>Amount: {{ $invoice->amount }}</p>
        <p>Created at: {{ $invoice->createdAt }}</p>

        <h2>Items</h2>
        <ul>
            @foreach ($invoice->items as $item)
                <li>
                    <img src="{{ $qrcode }}" alt="QR code" class="inline-block mr-2" />
                    <p>Code: {{ $item->code }}</p>
                    <p>Name: {{ $item->name }}</p>
                    <p>Price: {{ $item->price }}</p>
                    <p>Quantity: {{ $item->quantity }}</p>
                    <p>Tax group: {{ $item->taxGroup }}</p>
                    <p>Tax specific: {{ $item->taxSpecific }}</p>
                    <p>Original price: {{ $item->originalPrice }}</p>
                    <p>Price modification: {{ $item->priceModification }}</p>
                </li>
            endforeach
        </ul>

        <h2>Client</h2>
        <ul>
            <li>
                <p>Ifu: {{ $invoice->client->ifu }}</p>
                <p>Name: {{ $invoice->client->name }}</p>
                <p>Contact: {{ $invoice->client->contact }}</p>
                <p>Address: {{ $invoice->client->address }}</p>
            </li>
        </ul>

        <h2>Operator</h2>
        <ul>
            <li>
                <p>Id: {{ $invoice->operator->id }}</p>
                <p>Name: {{ $invoice->operator->name }}</p>
            </li>
        </ul>

        <h2>Payment</h2>
        <ul>
            @foreach ($invoice->payment as $payment)
                <li>
                    <p>Name: {{ $payment->name }}</p>
                    <p>Amount: {{ $payment->amount }}</p>
                </li>
            endforeach
        </ul>

        <p>Invoice data:</p>
        <ul>
            <li>QR code: <img src="{{ $qrcode }}" alt="QR code" /></li>
        </ul>
    </section>
</body>
</html>
