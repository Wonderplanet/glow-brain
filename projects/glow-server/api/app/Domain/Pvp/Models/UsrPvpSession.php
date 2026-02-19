<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Models;

use App\Domain\Pvp\Enums\PvpSessionStatus;
use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use App\Http\Responses\Data\OpponentPvpStatusData;
use Carbon\CarbonImmutable;

class UsrPvpSession extends UsrEloquentModel implements UsrPvpSessionInterface
{
    use HasFactory;

    protected $primaryKey = 'usr_user_id';

    protected $fillable = [
        'usr_user_id',
        'id',
        'sys_pvp_season_id',
        'is_use_item',
        'party_no',
        'opponent_my_id',
        'opponent_pvp_status',
        'opponent_score',
        'is_valid',
        'battle_start_at',
    ];

    protected $casts = [
        'is_valid' => PvpSessionStatus::class,
        'is_use_item' => 'integer',
        'party_no' => 'integer',
        'opponent_score' => 'integer',
    ];

    public function init(string $usrUserId, string $sysPvpSeasonId): UsrPvpSession
    {
        $this->usr_user_id = $usrUserId;
        $this->id = $this->newUniqueId();
        $this->sys_pvp_season_id = $sysPvpSeasonId;
        $this->is_use_item = 0;
        $this->party_no = 0;
        $this->opponent_my_id = '';
        $this->opponent_pvp_status = json_encode([]);
        $this->opponent_score = 0;
        $this->is_valid = PvpSessionStatus::CLOSED;

        return $this;
    }

    public function getSysPvpSeasonId(): string
    {
        return $this->sys_pvp_season_id;
    }

    public function getPartyNo(): int
    {
        return $this->party_no;
    }

    public function getOpponentMyId(): ?string
    {
        return $this->opponent_my_id ?? '';
    }

    public function getOpponentPvpStatus(): string
    {
        return $this->opponent_pvp_status;
    }

    /**
     * @return array<mixed>
     */
    public function getOpponentPvpStatusToArray(): array
    {
        return json_decode($this->opponent_pvp_status, true);
    }

    public function getOpponentScore(): int
    {
        return $this->opponent_score;
    }

    public function getIsValid(): PvpSessionStatus
    {
        return $this->is_valid;
    }

    public function getBattleStartAt(): ?string
    {
        return $this->battle_start_at;
    }

    public function getBattleStartAtAsCarbon(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->battle_start_at);
    }

    public function isClosed(): bool
    {
        return $this->getIsValid() === PvpSessionStatus::CLOSED;
    }

    public function isStarted(): bool
    {
        return $this->getIsValid() === PvpSessionStatus::STARTED;
    }

    public function closeSession(): void
    {
        $this->is_valid = PvpSessionStatus::CLOSED;
    }

    public function calcBattleTime(CarbonImmutable $now): int
    {
        return (int) CarbonImmutable::parse($this->battle_start_at)->diffInSeconds($now);
    }

    public function getIsUseItem(): int
    {
        return $this->is_use_item;
    }

    public function isUseItem(): bool
    {
        return $this->is_use_item === 1;
    }

    public function startSession(
        string $sysPvpSeasonId,
        int $partyNo,
        string $opponentMyId,
        OpponentPvpStatusData $opponentPvpStatusData,
        int $opponentScore,
        CarbonImmutable $now,
        bool $isUseItem
    ): void {
        $this->sys_pvp_season_id = $sysPvpSeasonId;
        $this->party_no = $partyNo;
        $this->opponent_my_id = $opponentMyId;
        $this->opponent_pvp_status = $opponentPvpStatusData->formatToJson();
        $this->opponent_score = max(0, $opponentScore);
        $this->is_valid = PvpSessionStatus::STARTED;
        $this->battle_start_at = $now->toDateTimeString();
        $this->is_use_item = (int)$isUseItem;
    }

    public function makeModelKey(): string
    {
        return $this->usr_user_id;
    }
}
