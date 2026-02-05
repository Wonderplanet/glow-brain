<?php

declare(strict_types=1);

namespace App\Domain\Message\Models;

use App\Domain\Resource\Log\Models\LogModel;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $usr_user_id
 * @property string $trigger_source
 * @property string $trigger_value
 * @property string $pre_grant_reward_json
 */
class LogSystemMessageAddition extends LogModel
{
    use HasFactory;

    public function setUsrUserId(string $usrUserId): void
    {
        $this->usr_user_id = $usrUserId;
    }

    public function setTriggerSource(string $triggerSource): void
    {
        $this->trigger_source = $triggerSource;
    }

    public function setTriggerValue(string $triggerValue): void
    {
        $this->trigger_value = $triggerValue;
    }

    /**
     * @param array<mixed> $preGrantRewardJson
     */
    public function setPreGrantRewardJson(array $preGrantRewardJson): void
    {
        $this->pre_grant_reward_json = json_encode($preGrantRewardJson);
    }
}
