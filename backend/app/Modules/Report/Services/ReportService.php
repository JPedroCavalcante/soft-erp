<?php

namespace App\Modules\Report\Services;

use App\Modules\Report\Exports\ReportExport;
use App\Modules\Report\Repositories\ReportRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportService
{
    public function __construct(
        private readonly ReportRepository $repository
    ) {}

    public function getSalesReport(array $filters): array
    {
        return $this->repository->getSalesReport($filters);
    }

    public function getPurchasesReport(array $filters): array
    {
        return $this->repository->getPurchasesReport($filters);
    }

    public function getProfitReport(array $filters): array
    {
        return $this->repository->getProfitReport($filters);
    }

    public function getStockReport(): array
    {
        return $this->repository->getStockReport();
    }

    public function exportReport(array $data): Response|BinaryFileResponse
    {
        $reportData = match($data['report_name']) {
            'sales' => $this->repository->getSalesReport($data),
            'purchases' => $this->repository->getPurchasesReport($data),
            'profit' => $this->repository->getProfitReport($data),
            'stock' => $this->repository->getStockReport(),
        };

        $filename = $data['report_name'] . '_' . now()->format('Y-m-d_His');

        if ($data['type'] === 'pdf') {
            $pdf = Pdf::loadView("reports.{$data['report_name']}", ['data' => $reportData]);
            return $pdf->download("{$filename}.pdf");
        }

        return Excel::download(
            new ReportExport($reportData, $data['report_name']),
            "{$filename}.xlsx"
        );
    }
}

