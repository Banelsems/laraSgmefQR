<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Services;

use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
use Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface;
use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

class DeclarationGeneratorService
{
    public function __construct(
        protected InvoiceManagerInterface $invoiceManager,
        protected SgmefApiClientInterface $apiClient
    ) {
    }

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
            File::makeDirectory($tempDir, 0o755, true);

            foreach ($testCases as $index => $case) {
                $requestData = array_merge($case['request'], [
                    'ifu' => $companyInfo['ifu'],
                    'operator' => config('lara_sgmef_qr.default_operator'),
                    'client' => ['name' => 'Client de Test Emecef'],
                ]);

                if (in_array($requestData['type'] ?? null, ['FA', 'EA'], true)) {
                    $originalType = $requestData['type'] === 'EA' ? 'EV' : 'FV';
                    $originalData = $requestData;
                    $originalData['type'] = $originalType;
                    unset($originalData['reference']);

                    $originalInvoice = $this->invoiceManager->createInvoice(InvoiceRequestDto::fromArray($originalData));
                    $this->invoiceManager->confirmInvoice($originalInvoice->uid);
                    $requestData['reference'] = $originalInvoice->uid;
                }

                $invoiceDto = InvoiceRequestDto::fromArray($requestData);

                $createdInvoice = $this->invoiceManager->createInvoice($invoiceDto);
                $confirmedInvoice = $this->invoiceManager->confirmInvoice($createdInvoice->uid);

                $html = view('lara-sgmef-qr::declaration.invoice_screenshot', [
                    'invoice' => $confirmedInvoice,
                    'securityElements' => $confirmedInvoice->security_elements ?? [],
                    'companyInfo' => $companyInfo,
                ])->render();

                $screenshotPath = $tempDir . '/case_' . ($index + 1) . '.png';
                if (app()->runningUnitTests()) {
                    File::put($screenshotPath, 'screenshot placeholder');
                } else {
                    Browsershot::html($html)->save($screenshotPath);
                }

                $testResults[] = [
                    'name' => $case['name'],
                    'description' => $case['description'] ?? 'N/A',
                    'image_path' => $screenshotPath,
                ];
            }

            $pdf = Pdf::loadView('lara-sgmef-qr::declaration.declaration', [
                'companyInfo' => $companyInfo,
                'sfeInfo' => $sfeInfo,
                'testResults' => $testResults,
                'declarationCss' => $this->declarationCss(),
            ]);

            $pdfPath = storage_path('app/declaration_emecef.pdf');
            $pdf->save($pdfPath);

            return $pdfPath;
        } finally {
            // Garantit le nettoyage des ressources temporaires, même en cas d'erreur
            if ($tempDir && File::isDirectory($tempDir)) {
                File::deleteDirectory($tempDir);
            }

            $baseTempDir = storage_path('app/lara_sgmef_qr_temp');
            if (File::isDirectory($baseTempDir) && count(File::directories($baseTempDir)) === 0 && count(File::files($baseTempDir)) === 0) {
                File::deleteDirectory($baseTempDir);
            }
        }
    }

    private function declarationCss(): string
    {
        $appCss = resource_path('css/declaration.css');
        $packageCss = dirname(__DIR__, 2) . '/resources/css/declaration.css';

        foreach ([$appCss, $packageCss] as $path) {
            if (File::exists($path)) {
                return File::get($path);
            }
        }

        return '';
    }
}
