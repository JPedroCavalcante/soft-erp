<?php

namespace App\Modules\Purchase\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Purchase\Models\Purchase;

class PurchaseRepository extends BaseRepository
{
    public function __construct(Purchase $model)
    {
        parent::__construct($model);
    }
}
