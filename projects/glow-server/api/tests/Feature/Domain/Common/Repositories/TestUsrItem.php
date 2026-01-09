<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Common\Repositories;

use App\Domain\Item\Models\UsrItem;

class TestUsrItem extends UsrItem
{
    public function __construct(array $attributes)
    {
        parent::__construct($attributes);
    }
}
