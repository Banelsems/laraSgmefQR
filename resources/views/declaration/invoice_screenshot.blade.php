    <div style="font-family: sans-serif; width: 800px; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, .15);">
        <h1 style="font-size: 1.5rem; font-weight: bold; text-align: center; margin-bottom: 1rem;">FACTURE NORMALISÉE</h1>
        <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem;">
            <div>
                <strong>De:</strong><br>
                {{ $invoice->ifu }}<br>
                {{ $companyInfo['name'] ?? 'Votre Entreprise' }}
            </div>
            <div style="text-align: right;">
                <strong>Facture #:</strong> {{ $invoice->uid }}<br>
                <strong>Créée le:</strong> {{ $invoice->created_at->format('d/m/Y') }}
            </div>
        </div>
        <hr style="margin-top: 1rem; margin-bottom: 1rem;">
        <h3 style="font-weight: bold; margin-bottom: 0.5rem;">Articles:</h3>
        <table style="width: 100%; text-align: left;">
            <tr style="background-color: #e5e7eb;">
                <th style="padding: 0.5rem;">Article</th>
                <th style="padding: 0.5rem; text-align: right;">Total</th>
            </tr>
            @foreach($invoice->items as $item)
            <tr>
                <td style="padding: 0.5rem;">{{ $item->name }} ({{ $item->quantity }} x {{ $item->price }})</td>
                <td style="padding: 0.5rem; text-align: right;">{{ number_format($item->quantity * $item->price, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endforeach
            <tr style="font-weight: bold; border-top-width: 2px;">
                <td style="padding: 0.5rem; text-align: right;">Total HT</td>
                <td style="padding: 0.5rem; text-align: right;">{{ number_format($invoice->total_amount, 0, ',', ' ') }} FCFA</td>
            </tr>
        </table>
        <hr style="margin-top: 1rem; margin-bottom: 1rem;">
        <div style="text-align: center;">
            <img src="data:image/png;base64,{{ $securityElements['qrCode'] ?? '' }}" alt="QR Code" style="margin-left: auto; margin-right: auto;">
            <p style="margin-top: 0.5rem;"><strong>Code MECeF:</strong> {{ $securityElements['codeMECeFDGI'] ?? '' }}</p>
        </div>
    </div>
