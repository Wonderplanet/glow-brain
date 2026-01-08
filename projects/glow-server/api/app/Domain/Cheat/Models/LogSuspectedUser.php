<?php

declare(strict_types=1);

namespace App\Domain\Cheat\Models;

use App\Domain\Resource\Log\Models\LogModel;
use App\Domain\Resource\Traits\HasFactory;
use Carbon\CarbonImmutable;

/**
 * @property string $id
 * @property string $usr_user_id
 * @property string $nginx_request_id
 * @property string $request_id
 * @property int $logging_no
 * @property string $content_type
 * @property string|null $target_id
 * @property string $cheat_type
 * @property string $detail
 * @property string $suspected_at
 */
class LogSuspectedUser extends LogModel
{
    use HasFactory;

    public function setUsrUserId(string $usrUserId): void
    {
        $this->usr_user_id = $usrUserId;
    }

    public function setNginxRequestId(string $nginxRequestId): void
    {
        $this->nginx_request_id = $nginxRequestId;
    }

    public function setRequestId(string $requestId): void
    {
        $this->request_id = $requestId;
    }

    public function setLoggingNo(int $loggingNo): void
    {
        $this->logging_no = $loggingNo;
    }

    public function setContentType(string $contentType): void
    {
        $this->content_type = $contentType;
    }

    public function setTargetId(?string $targetId): void
    {
        $this->target_id = $targetId;
    }

    public function setCheatType(string $cheatType): void
    {
        $this->cheat_type = $cheatType;
    }

    /**
     * @param array<mixed> $detail
     */
    public function setDetail(array $detail): void
    {
        $this->detail = json_encode($detail ?: []);
    }

    public function setSuspectedAt(CarbonImmutable $suspectedAt): void
    {
        $this->suspected_at = $suspectedAt->toDateTimeString();
    }
}
