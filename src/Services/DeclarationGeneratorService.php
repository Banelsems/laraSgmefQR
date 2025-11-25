<?php

namespace Banelsems\LaraSgmefQr\Services;

use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

class DeclarationGeneratorService
{
    public function __construct(
        protected InvoiceManagerInterface $invoiceManager,
        protected SgmefApiClient $apiClient
    ) {}

    public function generateDeclaration(): string
    {
        $companyInfo = config('lara_sgmef_qr.company_info');
        $sfeInfo = config('lara_sgmef_qr.sfe_info');
        $testCases = config('lara_sgmef_qr.emecef_test_cases', []);
        $testResults = [];
        
        // Déclare le répertoire temporaire pour qu'il soit accessible dans le bloc finally
        $tempDir = null;

        try {
            $tempDir = storage_path('app/lara_sgmef_qr_temp/' . uniqid());
            File::makeDirectory($tempDir, 0755, true);

            foreach ($testCases as $index => $case) {
                $requestData = array_merge($case['request'], [
                    'ifu' => $companyInfo['ifu'],
                    'operator' => config('lara_sgmef_qr.default_operator'),
                    'client' => ['name' => 'Client de Test Emecef'],
                ]);
                $invoiceDto = InvoiceRequestDto::fromArray($requestData);

                $createdInvoice = $this->invoiceManager->createInvoice($invoiceDto);
                $securityElements = $this->apiClient->confirmInvoice($createdInvoice->uid);

                $html = view('lara-sgmef-qr::declaration.invoice_screenshot', [
                    'invoice' => $createdInvoice,
                    'securityElements' => $securityElements->toArray(),
                    'companyInfo' => $companyInfo,
                ])->render();

                $screenshotPath = $tempDir . "/case_" . ($index + 1) . ".png";
                Browsershot::html($html)->save($screenshotPath);
                
                $testResults[] = [
                    'name' => $case['name'], 
                    'description' => $case['description'] ?? 'N/A',
                    'image_path' => $screenshotPath
                ];
            }

            $pdf = Pdf::loadView('lara-sgmef-qr::declaration.declaration', [
                'companyInfo' => $companyInfo,
                'sfeInfo' => $sfeInfo,
                'testResults' => $testResults,
            ]);
            
            $pdfPath = storage_path('app/declaration_emecef.pdf');
            $pdf->save($pdfPath);

            return $pdfPath;
        } finally {
            // Garantit le nettoyage des ressources temporaires, même en cas d'erreur
            if ($tempDir && File::isDirectory($tempDir)) {
                File::deleteDirectory($tempDir);
            }
        }
    }
}
