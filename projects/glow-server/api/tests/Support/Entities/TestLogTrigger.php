<?php

namespace Tests\Support\Entities;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Entities\LogTriggers\LogTrigger;

/**
 * テスト用のLogTriggerクラス
 */
class TestLogTrigger extends LogTrigger
{
    public function getLogTriggerData(): LogTriggerDto
    {
        return new LogTriggerDto(
            'test',
            'test',
        );
    }
}
