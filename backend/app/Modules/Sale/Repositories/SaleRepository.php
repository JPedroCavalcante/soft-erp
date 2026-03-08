<?php

namespace App\Modules\Sale\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Sale\Models\Sale;

class SaleRepository extends BaseRepository
{
    public function __construct(Sale $model)
    {
        parent::__construct($model);
    }
}
