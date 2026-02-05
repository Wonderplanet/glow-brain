<?php

declare(strict_types=1);

namespace App\Domain\User\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;

/**
 * @property int $daily_buy_stamina_ad_count
 * @property null|string $daily_buy_stamina_ad_at
 */
class UsrUserBuyCount extends UsrEloquentModel implements UsrUserBuyCountInterface
{
    use HasFactory;

    protected $primaryKey = 'usr_user_id';

    protected $fillable = [
    ];

    protected $casts = [
    ];

    public function getDailyBuyStaminaAdCount(): int
    {
        return $this->daily_buy_stamina_ad_count;
    }

    public function getDailyBuyStaminaAdAt(): ?string
    {
        return $this->daily_buy_stamina_ad_at;
    }

    public function setDailyBuyStaminaAd(int $count, string $at): void
    {
        $this->daily_buy_stamina_ad_count = $count;
        $this->daily_buy_stamina_ad_at = $at;
    }

    public function setDailyBuyStaminaAdCount(int $count): void
    {
        $this->daily_buy_stamina_ad_count = $count;
    }

    public function makeModelKey(): string
    {
        return $this->usr_user_id;
    }
}
