<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $invoice->uid ?? $invoice->id }}</title>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 1cm;
            }
            body { margin: 0; }
            .no-print { display: none; }
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .invoice-container {
            max-width: 21cm;
            margin: 0 auto;
            padding: 20px;
            background: white;
        }
        
        .header {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: start;
        }
        
        .company-info h1 {
            color: #2563eb;
            font-size: 24px;
            margin: 0 0 10px 0;
        }
        
        .invoice-info {
            text-align: right;
        }
        
        .invoice-number {
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
        }
        
        .parties {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .party {
            width: 45%;
        }
        
        .party h3 {
            background: #f3f4f6;
            padding: 8px 12px;
            margin: 0 0 10px 0;
            font-size: 14px;
            border-left: 4px solid #2563eb;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #d1d5db;
            padding: 8px;
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
            width: 300px;
            margin-top: 20px;
        }
        
        .totals table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .totals td {
            padding: 5px 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .totals .total-final {
            font-weight: bold;
            font-size: 16px;
            background: #f3f4f6;
            border-top: 2px solid #2563eb;
        }
        
        .qr-section {
            clear: both;
            margin-top: 40px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
        
        .qr-code {
            display: inline-block;
            margin: 10px;
        }
        
        .security-info {
            margin-top: 20px;
            font-size: 10px;
            color: #6b7280;
        }
        
        .print-actions {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 16px;
            margin: 0 5px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            background: #1d4ed8;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
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
            <button onclick="window.print()" class="btn">
                <i class="fas fa-print"></i> Imprimer
            </button>
            <a href="{{ route('sgmef.invoices.pdf', $invoice->uid) }}" class="btn">
                <i class="fas fa-download"></i> Télécharger PDF
            </a>
            <a href="{{ route('sgmef.invoices.show', $invoice->uid) }}" class="btn" style="background: #6b7280;">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>

        <!-- En-tête -->
        <div class="header">
            <div class="header-content">
                <div class="company-info">
                    <h1>{{ config('app.name', 'Entreprise') }}</h1>
                    <p><strong>IFU:</strong> {{ $invoice->ifu }}</p>
                    <p>{{ config('lara_sgmef_qr.company_address', 'Adresse de l\'entreprise') }}</p>
                </div>
                <div class="invoice-info">
                    <div class="invoice-number">
                        FACTURE {{ $invoice->uid ?? $invoice->id }}
                    </div>
                    <p><strong>Date:</strong> {{ $invoice->created_at->format('d/m/Y') }}</p>
                    <p><strong>Type:</strong> {{ $invoice->type }}</p>
                    <span class="status-badge status-{{ $invoice->status->value }}">
                        {{ $invoice->status->getLabel() }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Parties -->
        <div class="parties">
            <div class="party">
                <h3>Émetteur</h3>
                <p><strong>{{ config('lara_sgmef_qr.company_name', 'Nom de l\'entreprise') }}</strong></p>
                <p>IFU: {{ $invoice->ifu }}</p>
                <p>{{ config('lara_sgmef_qr.company_address', 'Adresse') }}</p>
                <p>{{ config('lara_sgmef_qr.company_contact', 'Contact') }}</p>
            </div>
            
            <div class="party">
                <h3>Client</h3>
                @php
                    $clientData = $invoice->raw_request['client'] ?? [];
                @endphp
                <p><strong>{{ $clientData['name'] ?? 'Client' }}</strong></p>
                @if(!empty($clientData['ifu']))
                    <p>IFU: {{ $clientData['ifu'] }}</p>
                @endif
                @if(!empty($clientData['address']))
                    <p>{{ $clientData['address'] }}</p>
                @endif
                @if(!empty($clientData['contact']))
                    <p>{{ $clientData['contact'] }}</p>
                @endif
            </div>
        </div>

        <!-- Articles -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th class="text-right">Prix Unit.</th>
                    <th class="text-right">Qté</th>
                    <th class="text-right">Taxe</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $items = $invoice->raw_request['items'] ?? [];
                    $subtotal = 0;
                @endphp
                @foreach($items as $item)
                    @php
                        $itemTotal = ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
                        $subtotal += $itemTotal;
                    @endphp
                    <tr>
                        <td>
                            {{ $item['name'] ?? 'Article' }}
                            @if(!empty($item['code']))
                                <br><small>Code: {{ $item['code'] }}</small>
                            @endif
                        </td>
                        <td class="text-right">{{ number_format($item['price'] ?? 0, 0, ',', ' ') }}</td>
                        <td class="text-right">{{ $item['quantity'] ?? 1 }}</td>
                        <td class="text-right">{{ $item['taxGroup'] ?? 'A' }}</td>
                        <td class="text-right">{{ number_format($itemTotal, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totaux -->
        <div class="totals">
            <table>
                <tr>
                    <td>Sous-total HT:</td>
                    <td class="text-right">{{ number_format($subtotal, 0, ',', ' ') }} FCFA</td>
                </tr>
                @php
                    $responseData = $invoice->raw_response ?? [];
                @endphp
                @if(isset($responseData['totalTaxAmount']))
                    <tr>
                        <td>TVA:</td>
                        <td class="text-right">{{ number_format($responseData['totalTaxAmount'], 0, ',', ' ') }} FCFA</td>
                    </tr>
                @endif
                @if(isset($responseData['totalAibAmount']) && $responseData['totalAibAmount'] > 0)
                    <tr>
                        <td>AIB:</td>
                        <td class="text-right">{{ number_format($responseData['totalAibAmount'], 0, ',', ' ') }} FCFA</td>
                    </tr>
                @endif
                <tr class="total-final">
                    <td><strong>TOTAL TTC:</strong></td>
                    <td class="text-right"><strong>{{ number_format($invoice->total_amount, 0, ',', ' ') }} FCFA</strong></td>
                </tr>
            </table>
        </div>

        <!-- Section QR Code et sécurité -->
        @if($invoice->isConfirmed() && $invoice->qr_code_data)
            <div class="qr-section">
                <h3>Éléments de Sécurité</h3>
                
                <div class="qr-code">
                    {!! QrCode::size(150)->generate($invoice->qr_code_data) !!}
                    <p><strong>Code QR</strong></p>
                </div>
                
                <div class="security-info">
                    @if($invoice->mecf_code)
                        <p><strong>Code MECeF/DGI:</strong> {{ $invoice->mecf_code }}</p>
                    @endif
                    @if($invoice->confirmed_at)
                        <p><strong>Date de confirmation:</strong> {{ $invoice->confirmed_at->format('d/m/Y H:i:s') }}</p>
                    @endif
                    
                    @php
                        $securityElements = $invoice->raw_response['security_elements'] ?? [];
                    @endphp
                    @if(isset($securityElements['counters']))
                        <p><strong>Compteurs:</strong> {{ $securityElements['counters'] }}</p>
                    @endif
                    @if(isset($securityElements['nim']))
                        <p><strong>NIM:</strong> {{ $securityElements['nim'] }}</p>
                    @endif
                </div>
            </div>
        @endif

        <!-- Pied de page -->
        <div style="margin-top: 40px; text-align: center; font-size: 10px; color: #6b7280;">
            <p>Facture générée par LaraSgmefQR - Système de facturation électronique du Bénin</p>
            <p>Cette facture est conforme aux exigences de l'administration fiscale béninoise</p>
        </div>
    </div>
</body>
</html>
