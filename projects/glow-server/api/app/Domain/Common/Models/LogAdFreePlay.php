<?php

declare(strict_types=1);

namespace App\Domain\Common\Models;

use App\Domain\Resource\Log\Models\LogModel;
use Carbon\CarbonImmutable;

/**
 * @property string $content_type
 * @property string $target_id
 * @property string $play_at
 */
class LogAdFreePlay extends LogModel
{
    public function setContentType(string $contentType): void
    {
        $this->content_type = $contentType;
    }

    public function setTargetId(string $targetId): void
    {
        $this->target_id = $targetId;
    }

    public function setPlayAt(CarbonImmutable $playAt): void
    {
        $this->play_at = $playAt->toDateTimeString();
    }
}
