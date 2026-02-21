<?php

declare(strict_types=1);

namespace App\Domain\User\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Entities\UsrUserLoginEntity;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use Carbon\CarbonImmutable;

/**
 * @property ?string $first_login_at
 * @property ?string $last_login_at
 * @property string $hourly_accessed_at
 * @property int $login_count
 * @property int $login_day_count
 * @property int $login_continue_day_count
 * @property int $comeback_day_count
 */
class UsrUserLogin extends UsrEloquentModel implements UsrUserLoginInterface
{
    use HasFactory;

    protected $primaryKey = 'usr_user_id';

    protected $fillable = [
    ];

    protected $casts = [
    ];

    /**
     * UsrModelManagerでキャッシュ管理する際に使うユニークキーを作成する
     */
    public function makeModelKey(): string
    {
        return $this->usr_user_id;
    }

    public function getFirstLoginAt(): ?string
    {
        return $this->first_login_at;
    }

    public function getLastLoginAt(): ?string
    {
        return $this->last_login_at;
    }

    public function setHourlyAccessedAt(string $hourlyAccessedAt): void
    {
        $this->hourly_accessed_at = $hourlyAccessedAt;
    }

    public function getHourlyAccessedAt(): string
    {
        return $this->hourly_accessed_at;
    }

    public function checkHourlyAccessUpdate(CarbonImmutable $now): bool
    {
        return $now->startOfHour()->gt($this->hourly_accessed_at);
    }

    public function getLoginCount(): int
    {
        return $this->login_count;
    }

    public function getLoginDayCount(): int
    {
        return $this->login_day_count;
    }

    public function getLoginContinueDayCount(): int
    {
        return $this->login_continue_day_count;
    }

    public function getComebackDayCount(): int
    {
        return $this->comeback_day_count;
    }

    public function incrementLoginDayCount(): void
    {
        $this->login_day_count++;
    }

    public function incrementLoginContinueDayCount(): void
    {
        $this->comeback_day_count = 0;
        $this->login_continue_day_count++;
    }

    public function comebackLogin(int $comebackDayCount): void
    {
        $this->comeback_day_count = $comebackDayCount;
        $this->login_continue_day_count = 1;
    }

    public function login(CarbonImmutable $now): void
    {
        if ($this->first_login_at === null) {
            // 生涯で初ログイン時のみ更新する
            $this->first_login_at = $now->toDateTimeString();
        }

        $this->last_login_at = $now->toDateTimeString();
        $this->login_count++;
    }

    public function toEntity(): UsrUserLoginEntity
    {
        return new UsrUserLoginEntity(
            $this->usr_user_id,
            $this->first_login_at,
            $this->login_day_count,
            $this->login_continue_day_count,
        );
    }
}
