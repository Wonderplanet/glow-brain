<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Shop\Entities;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Entities\LogTriggers\LogTrigger;

class TestShopLogTrigger extends LogTrigger
{
    public function getLogTriggerData(): LogTriggerDto
    {
        return new LogTriggerDto(
            'TestShopLogTrigger',
            'test_shop_log_trigger',
        );
    }
}
