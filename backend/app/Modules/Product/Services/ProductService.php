<?php

namespace App\Modules\Product\Services;

use App\Modules\Product\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Modules\Product\Models\Product;

class ProductService
{
    public function __construct(
        private readonly ProductRepository $repository
    ) {}

    public function index(): Collection
    {
        return $this->repository->all();
    }

    public function store(array $data): Product
    {
        $data['stock'] = 0;
        $data['average_cost'] = 0;

        return $this->repository->create($data);
    }

    public function show(int $id): Product
    {
        return $this->repository->find($id);
    }

    public function update(int $id, array $data): Product
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
