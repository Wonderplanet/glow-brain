<?php

declare(strict_types=1);

namespace App\Repositories\Opr;

use App\Domain\Resource\Mst\Repositories\OprProductRepository as BaseOprProductRepository;
use App\Models\Opr\OprProduct;

class OprProductRepository extends BaseOprProductRepository
{
    protected string $modelClass = OprProduct::class;
}
