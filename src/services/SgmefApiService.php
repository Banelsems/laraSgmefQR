<?php
namespace banelsems\LaraSgmefQR\src\services;

use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Barryvdh\QrCode\Facades\QRCode;

class LaraSgmefQR  implements SgmefApiContract
{
   protected $httpClient;
   protected $apiHost;

   public function __construct(HttpClient $httpClient)
   {
      $this->httpClient = $httpClient;
      $this->apiHost = Config::get('larasgmefqr.api_host');
      $this->apiKey = Config::get('larasgmefqr.api_key');
   }

   // Configuration

   public function setApiCredentials($apiKey)
   {
      $this->httpClient->withHeaders([
         'Authorization' => 'Bearer ' . $api_key,
      ]);
   }

   // API communication functions

   public function getStatuses()
   {
      $endpoint = $this->apiHost . '/info/status';
      return $this->callApiEndpoint('GET', $endpoint);
   }

   public function getTaxGroups()
   {
      $endpoint = $this->apiHost . '/info/taxGroups';
      return $this->callApiEndpoint('GET', $endpoint);
   }

   public function getInvoiceTypes()
   {
      $endpoint = $this->apiHost . '/info/invoiceTypes';
      return $this->callApiEndpoint('GET', $endpoint);
   }

   public function getPaymentTypes()
   {
      $endpoint = $this->apiHost . '/info/paymentTypes';
      return $this->callApiEndpoint('GET', $endpoint);
   }

   public function createInvoice(array $data)
   {
      $endpoint = $this->apiHost . '/invoice';
      return $this->callApiEndpoint('POST', $endpoint, $data);
   }

   public function getInvoice($uid)
{
    $endpoint = $this->apiHost . '/invoice/' . $uid;

    try {
        $invoice = $this->callApiEndpoint('GET', $endpoint);
    } catch (\Exception $e) {
        return redirect('/invoices')->with('error', 'Invoice not found');
    }

    if (!$invoice) {
        return redirect('/invoices')->with('error', 'Invoice not found');
    }

    // Store the invoice data in a JSON file
    $filename = 'invoices/' . $uid . '.json';
    file_put_contents($filename, json_encode($invoice));

    return view('invoice', [
        'invoice' => $invoice,
    ]);
}

   
   protected function confirmInvoice($uid)
   {
      $endpoint = $this->apiHost . '/invoice/' . $uid . '/confirm';

      $data = [];

      // Use callApiEndpoint to make the API call
      $response = $this->callApiEndpoint('POST', $endpoint, $data);

      if ($response->successful()) {
         $data = $response->json();

         // Generate the QR code
         $qrcode = QRCode::generate($data['qrCode']);

         // Return the view with the QR code
         return view('confirm-invoice', [
               'qrcode' => $qrcode,
               'data' => $data,
         ]);
      }

      throw new \Exception($response->json('message'), $response->status());
   }

   protected function confirmInvoice($uid)
   {
      $endpoint = $this->apiHost . '/invoice/' . $uid . '/confirm';

      $data = [];

      // Use callApiEndpoint to make the API call
      $response = $this->callApiEndpoint('POST', $endpoint, $data);

      if ($response->successful()) {
         $data = $response->json();

         // Generate the QR code
         //$qrcode = QRCode::generate($data['qrCode']);
         // Generate the QR code manually
         $qrcode = '<svg viewBox="0 0 200 200">' .
                    '<rect x="0" y="0" width="200" height="200" fill="white" />' .
                    '<text x="100" y="100" fill="black" font-size="100">' . $data['qrCode'] . '</text>' .
                  '</svg>';

         // Store the invoice data in a JSON file
         $filename = 'invoices/' . $uid . '.json';
         $invoice = json_decode(file_get_contents($filename));

         // Return the view with the QR code
         return view('invoiceNormalize', [
               'qrcode' => $qrcode,
               'data' => $data,
               'invoice' => $invoice,
         ]);
      }

      throw new \Exception($response->json('message'), $response->status());
   }


   protected function cancelInvoice($uid)
{
    $endpoint = $this->apiHost . '/invoice/' . $uid . '/cancel';

    $data = [];

    // Use callApiEndpoint to make the API call
    $response = $this->callApiEndpoint('POST', $endpoint, $data);

    if ($response->successful()) {
        // The numerization has been cancelled
        return view('invoiceNormalize', [
            'data' => $response->json(),
            'cancelled' => true,
        ]);
    } else {
        // An error occurred
        throw new \Exception($response->json('message'), $response->status());
    }
}



   protected function callApiEndpoint($method, $endpoint, ?array $data = [])
   {
      try {
         $response = Http::withHeaders($this->httpClient->getHeaders())->{$method}($endpoint, $data);

         if ($response->successful()) {
               return $response->json();
         }

         throw new \Exception($response->json('message'), $response->status());

      } catch (\Exception $e) {
         throw $e;
      }
   }

}
