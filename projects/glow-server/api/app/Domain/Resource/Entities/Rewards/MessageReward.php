<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Message\Enums\MessageSource;
use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

class MessageReward extends BaseReward
{
    private ?string $expiredAt;

    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
        ?string $messageSource = null,
        ?string $mngMessageId = null,
        ?string $expiredAt = null,
    ) {
        switch ($messageSource) {
            case MessageSource::MNG_MESSAGE->value:
                $logTriggerData = new LogTriggerDto(
                    LogResourceTriggerSource::MESSAGE_REWARD->value,
                    $mngMessageId ?? '',
                );
                break;
            default:
                $logTriggerData = new LogTriggerDto(
                    LogResourceTriggerSource::SYSTEM_MESSAGE_REWARD->value,
                    $messageSource ?? '',
                );
                break;
        }
        $this->expiredAt = $expiredAt;

        parent::__construct(
            $type,
            $resourceId,
            $amount,
            $logTriggerData,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function formatToLogWithTrigger(): array
    {
        $log = parent::formatToLogWithTrigger();
        $log['expiredAt'] = $this->expiredAt;

        return $log;
    }
}
