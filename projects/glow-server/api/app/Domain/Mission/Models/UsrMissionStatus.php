<?php

declare(strict_types=1);

namespace App\Domain\Mission\Models;

use App\Domain\Mission\Enums\MissionBeginnerStatus;
use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use Carbon\CarbonImmutable;

/**
 * @property string $usr_user_id
 * @property int $beginner_mission_status
 * @property string $latest_mst_hash
 * @property ?string $mission_unlocked_at
 */
class UsrMissionStatus extends UsrEloquentModel implements UsrMissionStatusInterface
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

    public function isBeginnerMissionCompleted(): bool
    {
        return $this->beginner_mission_status === MissionBeginnerStatus::COMPLETED->value;
    }

    public function isBeginnerMissionFullyUnlocked(): bool
    {
        return $this->beginner_mission_status === MissionBeginnerStatus::FULLY_UNLOCKED->value;
    }

    public function getLatestMstHash(): string
    {
        return $this->latest_mst_hash;
    }

    public function setLatestMstHash(string $hash): void
    {
        $this->latest_mst_hash = $hash;
    }

    public function getMissionUnlockedAt(): ?string
    {
        return $this->mission_unlocked_at;
    }

    public function setMissionUnlockedAt(CarbonImmutable $now): void
    {
        $this->mission_unlocked_at = $now->toDateTimeString();
    }

    public function setBeginnerMissionStatus(MissionBeginnerStatus $status): void
    {
        $this->beginner_mission_status = $status->value;
    }

    /**
     * マスタデータハッシュを比較して、即時達成判定が必要かどうかを返す
     * true: 必要, false: 不要
     */
    public function needInstantClear(string $currentMstHash): bool
    {
        return $this->latest_mst_hash !== $currentMstHash;
    }
}
