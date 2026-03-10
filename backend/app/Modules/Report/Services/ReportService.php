<?php

namespace App\Modules\Report\Services;

use App\Modules\Report\Repositories\ReportRepository;

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
}

