<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Models;

use App\Domain\AdventBattle\Enums\AdventBattleSessionStatus;
use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use Carbon\CarbonImmutable;

/**
 * @property string $usr_user_id
 * @property string $mst_advent_battle_id
 * @property AdventBattleSessionStatus $is_valid
 * @property int $party_no
 * @property string $battle_start_at
 */
class UsrAdventBattleSession extends UsrEloquentModel implements UsrAdventBattleSessionInterface
{
    use HasFactory;

    protected $primaryKey = 'usr_user_id';

    protected $fillable = [
        'usr_user_id',
        'mst_advent_battle_id',
        'is_valid',
        'party_no',
        'battle_start_at',
        'is_challenge_ad',
    ];

    protected $casts = [
        'is_valid' => AdventBattleSessionStatus::class,
    ];

    public function init(string $usrUserId, CarbonImmutable $now): UsrAdventBattleSession
    {
        $this->id = $this->newUniqueId();
        $this->usr_user_id = $usrUserId;
        $this->mst_advent_battle_id = '0';
        $this->is_valid = AdventBattleSessionStatus::CLOSED;
        $this->party_no = 0;
        $this->battle_start_at = $now->toDateTimeString();

        return $this;
    }

    public function getMstAdventBattleId(): string
    {
        return $this->mst_advent_battle_id;
    }

    public function getIsValid(): AdventBattleSessionStatus
    {
        return $this->is_valid;
    }

    public function getPartyNo(): int
    {
        return $this->party_no;
    }

    public function getBattleStartAt(): string
    {
        return $this->battle_start_at;
    }

    public function calcBattleTime(CarbonImmutable $now): int
    {
        return (int) CarbonImmutable::parse($this->battle_start_at)->diffInSeconds($now);
    }

    public function isClosed(): bool
    {
        return $this->getIsValid() === AdventBattleSessionStatus::CLOSED;
    }

    public function isStarted(): bool
    {
        return $this->getIsValid() === AdventBattleSessionStatus::STARTED;
    }

    public function isStartedByMstAdventBattleId(string $mstAdventBattleId): bool
    {
        return $this->getMstAdventBattleId() === $mstAdventBattleId && $this->isStarted();
    }

    public function closeSession(): void
    {
        $this->is_valid = AdventBattleSessionStatus::CLOSED;
    }

    public function startSession(
        string $mstAdventBattleId,
        int $partyNo,
        CarbonImmutable $now,
        bool $isChallengeAd
    ): void {
        $this->mst_advent_battle_id = $mstAdventBattleId;
        $this->is_valid = AdventBattleSessionStatus::STARTED;
        $this->party_no = $partyNo;
        $this->is_challenge_ad = $isChallengeAd ? 1 : 0;
        $this->battle_start_at = $now->toDateTimeString();
    }

    public function makeModelKey(): string
    {
        return $this->usr_user_id;
    }

    public function isChallengeAd(): bool
    {
        return $this->is_challenge_ad === 1;
    }
}
