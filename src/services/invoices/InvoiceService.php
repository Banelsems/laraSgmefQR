<?php
namespace banelsems\LaraSgmefQR\src\Services\Invoices;
use Exception;
use App\Models\Invoice;
use MercurySeries\Flashy\Flashy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class InvoiceService{
	public function __construct()
	{
	}
    public function createInvoiceData($sale): array
    {
        // Get the currently authenticated user
        $currentUser = Auth::user();

        // Initialize variables
        $amount = 0;

        // Determine the AIB tax type based on the sale's aib_amount
        $aibTaxType = match ($sale->aib_amount) {
            '1' => 'A',
            '5' => 'B',
            default => null,// This is the default case when $sale->aib_amount doesn't match 1 or 5.
        };

        // Build the request payload in the desired format
        $data = [
            'ifu' => '0202113169876', // Replace this with the actual value
            'type' => 'FV',
            'aib' => $aibTaxType,
            'items' => [],
            'client' => [
                'contact' => $sale->phone,
                'ifu' => $sale->numero_ifu,
                'name' => $sale->fullname,
                'address' => $sale->address,
            ],
            'operator' => [
                'id' => $currentUser->id,
                'name' => $currentUser->name,
            ],
            'payment' => [],
            'reference' => $sale->sale_number,
        ];

        // Add sale item elements
        foreach ($sale->saleItems as $saleItem) {
            // Determine the tax percentage based on the tax_group
            $taxPercentage = match ($sale ->tax_group) {
                'A' => 0,
                'B' => 18,
                'C' => 0,
                'D' => 18,
                'E' => 0,
                'F' => 18,
                // Add other cases as needed
                default => 0,
            };

            // Calculate tax amount and total price
            $price = $saleItem->price;
            $taxAmount = $price * ($taxPercentage / 100);
            $totalPrice = $price + $taxAmount;

            // Add the item to the data array
            $data['items'][] = [
                'code' => $saleItem->product->SKU,
                'name' => $saleItem->product->name,
                'price' => $totalPrice,
                'quantity' => $saleItem->quantity,
                'taxGroup' => $sale->tax_group,
                'originalPrice' => $price,
                'priceModification' => "Price modified with tax group " . $saleItem->tax_group,
            ];

            // Update the total amount
            $amount += $totalPrice * $saleItem->quantity;
        }

        // Add payment information to the data array
        $data['payment'][] = [
            'name' => 'ESPECES',
            'amount' => $amount,
        ];
        return $data;
    }
    public function invoiceRequestDataDto(array $data, int $sale_id): Invoice
    {
        try {
            DB::beginTransaction();
            // Check if an invoice record with the given sale_id already exists
            $invoice = Invoice::where('sale_id', $sale_id)->first();
            if ($invoice) {
                // If an invoice record exists, update it with the new data
                $invoice->update(['invoiceRequestDataDto' => $data]);
            } else {
                // If no invoice record exists, create a new one
                $invoice = Invoice::create([
                    'sale_id' => $sale_id,
                    'invoiceRequestDataDto' => $data,
                ]);
            }
            DB::commit(); // Commit the transaction after successful processing
            return $invoice;
        } catch (Exception $e) {
            DB::rollback(); // Rollback the transaction in case of an exception
            // Log the error
            Log::error('Error creating/updating invoice request data: ' . $e->getMessage());
            // Flash an error message to the user
            Flashy::error('Une erreur s\'est produite lors de la création/mise à jour des données de la facture. Veuillez réessayer plus tard.');
            // Redirect the user back to the sale page or any other desired behavior
            return back();
        }
    }

    //public function invoiceResponseDataDto(InvoiceResponseDataDtoRequest $request): Invoice
    public function invoiceResponseDataDto( $createInvoice,int $id):Invoice
    {
       // Find the invoice by ID
        $invoice = Invoice::find($id);
        // dd($invoice);

        if (!$invoice) {
            throw new \Exception('Invoice not found.');
        }

        // Update the invoice record
        $invoice->update([
            'invoiceResponseDataDto' => $createInvoice,
            'statusInvoice'=>"pending",
        ]);
        // dd($invoice);
        return $invoice;
    }

    //public function securityElementsDto(SecurityElementsDtoRequest $createInvoice,int $sale_idt): Invoice

    public function securityElementsDto(array $invoiceResponseDataDto,int $invoice_id,string $statusInvoice): Invoice
    {
       // Find the invoice by ID
        $invoice = Invoice::find($invoice_id);

        if (!$invoice) {
            throw new \Exception('Invoice not found.');
        }
        // change status invoice 
        if (isset($securityElementsDto['errorCode']) && isset($securityElementsDto['errorDesc'])){
            $statusInvoice = "error";
        }

        // Update the invoice record
        $invoice->update([
            'securityElementsDto' => $invoiceResponseDataDto,
            'statusInvoice'=>$statusInvoice,
        ]);

        return $invoice;

    }

}