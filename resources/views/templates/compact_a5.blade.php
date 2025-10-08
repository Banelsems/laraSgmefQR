<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $invoice->uid ?? $invoice->id }}</title>
    <style>
        @media print {
            @page {
                size: A5;
                margin: 0.5cm;
            }
            body { margin: 0; }
            .no-print { display: none; }
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.2;
            color: #333;
        }
        
        .invoice-container {
            max-width: 14.8cm;
            margin: 0 auto;
            padding: 10px;
            background: white;
        }
        
        .header {
            text-align: center;
            border-bottom: 1px solid #2563eb;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .header h1 {
            color: #2563eb;
            font-size: 16px;
            margin: 0 0 5px 0;
        }
        
        .invoice-number {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .info-box {
            width: 48%;
            border: 1px solid #e5e7eb;
            padding: 5px;
            font-size: 9px;
        }
        
        .info-box h4 {
            margin: 0 0 5px 0;
            font-size: 10px;
            color: #2563eb;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 2px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8px;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #d1d5db;
            padding: 3px;
            text-align: left;
        }
        
        .items-table th {
            background: #f9fafb;
            font-weight: bold;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .totals {
            float: right;
            width: 60%;
            margin-top: 10px;
            font-size: 9px;
        }
        
        .totals table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .totals td {
            padding: 2px 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .totals .total-final {
            font-weight: bold;
            background: #f3f4f6;
            border-top: 1px solid #2563eb;
        }
        
        .qr-section {
            clear: both;
            margin-top: 20px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        
        .qr-code {
            display: inline-block;
            margin: 5px;
        }
        
        .security-info {
            margin-top: 10px;
            font-size: 7px;
            color: #6b7280;
        }
        
        .print-actions {
            margin-bottom: 10px;
            text-align: center;
        }
        
        .btn {
            display: inline-block;
            padding: 4px 8px;
            margin: 0 2px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 2px;
            border: none;
            cursor: pointer;
            font-size: 10px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 2px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-confirmed { background: #dcfce7; color: #166534; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-cancelled { background: #f3f4f6; color: #374151; }
        .status-error { background: #fecaca; color: #991b1b; }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Actions d'impression -->
        <div class="print-actions no-print">
            <button onclick="window.print()" class="btn">Imprimer</button>
            <a href="{{ route('sgmef.invoices.pdf', $invoice->uid) }}" class="btn">PDF</a>
            <a href="{{ route('sgmef.invoices.show', $invoice->uid) }}" class="btn" style="background: #6b7280;">Retour</a>
        </div>

        <!-- En-tête compact -->
        <div class="header">
            <h1>{{ config('app.name', 'Entreprise') }}</h1>
            <div class="invoice-number">FACTURE {{ $invoice->uid ?? $invoice->id }}</div>
            <p>{{ $invoice->created_at->format('d/m/Y') }} - Type: {{ $invoice->type }}</p>
            <span class="status-badge status-{{ $invoice->status->value }}">
                {{ $invoice->status->getLabel() }}
            </span>
        </div>

        <!-- Informations parties -->
        <div class="info-row">
            <div class="info-box">
                <h4>Émetteur</h4>
                <p><strong>{{ config('lara_sgmef_qr.company_name', 'Entreprise') }}</strong></p>
                <p>IFU: {{ $invoice->ifu }}</p>
            </div>
            
            <div class="info-box">
                <h4>Client</h4>
                @php
                    $clientData = $invoice->raw_request['client'] ?? [];
                @endphp
                <p><strong>{{ $clientData['name'] ?? 'Client' }}</strong></p>
                @if(!empty($clientData['ifu']))
                    <p>IFU: {{ $clientData['ifu'] }}</p>
                @endif
            </div>
        </div>

        <!-- Articles compacts -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Prix</th>
                    <th>Qté</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $items = $invoice->raw_request['items'] ?? [];
                @endphp
                @foreach($items as $item)
                    @php
                        $itemTotal = ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
                    @endphp
                    <tr>
                        <td>{{ Str::limit($item['name'] ?? 'Article', 20) }}</td>
                        <td class="text-right">{{ number_format($item['price'] ?? 0, 0, ',', ' ') }}</td>
                        <td class="text-right">{{ $item['quantity'] ?? 1 }}</td>
                        <td class="text-right">{{ number_format($itemTotal, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totaux compacts -->
        <div class="totals">
            <table>
                @php
                    $responseData = $invoice->raw_response ?? [];
                @endphp
                @if(isset($responseData['totalTaxAmount']))
                    <tr>
                        <td>TVA:</td>
                        <td class="text-right">{{ number_format($responseData['totalTaxAmount'], 0, ',', ' ') }}</td>
                    </tr>
                @endif
                <tr class="total-final">
                    <td><strong>TOTAL:</strong></td>
                    <td class="text-right"><strong>{{ number_format($invoice->total_amount, 0, ',', ' ') }} FCFA</strong></td>
                </tr>
            </table>
        </div>

        <!-- QR Code compact -->
        @if($invoice->isConfirmed() && $invoice->qr_code_data)
            <div class="qr-section">
                <div class="qr-code">
                    {!! QrCode::size(80)->generate($invoice->qr_code_data) !!}
                </div>
                
                <div class="security-info">
                    @if($invoice->mecf_code)
                        <p><strong>MECeF:</strong> {{ $invoice->mecf_code }}</p>
                    @endif
                    <p><strong>Confirmée:</strong> {{ $invoice->confirmed_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        @endif

        <!-- Pied de page compact -->
        <div style="margin-top: 15px; text-align: center; font-size: 7px; color: #6b7280;">
            <p>Facture électronique - Conforme aux exigences fiscales béninoises</p>
        </div>
    </div>
</body>
</html>
