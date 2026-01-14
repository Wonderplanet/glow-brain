<?php

declare(strict_types=1);

namespace App\Models\Log;

use App\Constants\Database;
use WonderPlanet\Domain\Currency\Models\LogCurrencyFree as BaseLogCurrencyFree;

class LogCurrencyFree extends BaseLogCurrencyFree
{
    protected $connection = Database::TIDB_CONNECTION;

    public function getUsrUserId(): string
    {
        return $this->usr_user_id;
    }

    public function getOsPlatform(): string
    {
        return $this->os_platform;
    }

    public function getBeforeIngameAmount(): int
    {
        return $this->before_ingame_amount;
    }

    public function getBeforeBonusAmount(): int
    {
        return $this->before_bonus_amount;
    }

    public function getBeforeRewardAmount(): int
    {
        return $this->before_reward_amount;
    }

    public function getChangeIngameAmount(): int
    {
        return $this->change_ingame_amount;
    }

    public function getChangeBonusAmount(): int
    {
        return $this->change_bonus_amount;
    }

    public function getChangeRewardAmount(): int
    {
        return $this->change_reward_amount;
    }

    public function getCurrentIngameAmount(): int
    {
        return $this->current_ingame_amount;
    }

    public function getCurrentBonusAmount(): int
    {
        return $this->current_bonus_amount;
    }

    public function getCurrentRewardAmount(): int
    {
        return $this->current_reward_amount;
    }

    public function getTriggerType(): string
    {
        return $this->trigger_type;
    }

    public function getTriggerId(): string
    {
        return $this->trigger_id;
    }

    public function getTriggerName(): string
    {
        return $this->trigger_name;
    }

    public function getTriggerDetail(): string
    {
        return $this->trigger_detail;
    }

    public function getRequestIdType(): string
    {
        return $this->request_id_type;
    }

    public function getRequestId(): string
    {
        return $this->request_id;
    }

    public function getNginxRequestId(): string
    {
        return $this->nginx_request_id;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at->toDateTimeString();
    }

    /**
     * Factoryクラスの取得 (デフォルトに戻す)
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        //
    }
}
