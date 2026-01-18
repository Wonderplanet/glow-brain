<?php

declare(strict_types=1);

namespace App\Domain\Stage\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use App\Domain\Stage\Enums\StageSessionStatus;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * @property string $mst_stage_id
 * @property StageSessionStatus $is_valid
 * @property int $party_no
 * @property int $continue_count
 * @property int $daily_continue_ad_count
 * @property string $opr_campaign_ids
 * @property int $auto_lap_count
 * @property string $latest_reset_at
 */
class UsrStageSession extends UsrEloquentModel implements UsrStageSessionInterface
{
    use HasFactory;

    protected $primaryKey = 'usr_user_id';

    protected $fillable = [
        'usr_user_id',
        'mst_stage_id',
        'is_valid',
        'party_no',
        'continue_count',
        'daily_continue_ad_count',
        'opr_campaign_ids',
        'auto_lap_count',
        'latest_reset_at',
        'is_challenge_ad',
    ];

    protected $casts = [
        'is_valid' => StageSessionStatus::class,
    ];

    public function init(string $usrUserId, CarbonImmutable $now): UsrStageSession
    {
        $this->id = $this->newUniqueId();
        $this->usr_user_id = $usrUserId;
        $this->mst_stage_id = '0';
        $this->is_valid = StageSessionStatus::CLOSED;
        $this->party_no = 0;
        $this->continue_count = 0;
        $this->daily_continue_ad_count = 0;
        $this->opr_campaign_ids = json_encode([]);
        $this->auto_lap_count = 1;
        $this->latest_reset_at = $now->toDateTimeString();

        return $this;
    }

    public function getMstStageId(): string
    {
        return $this->mst_stage_id;
    }

    public function getIsValid(): StageSessionStatus
    {
        return $this->is_valid;
    }

    public function getPartyNo(): int
    {
        return $this->party_no;
    }

    public function getContinueCount(): int
    {
        return $this->continue_count;
    }

    public function getDailyContinueAdCount(): int
    {
        return $this->daily_continue_ad_count;
    }

    public function getOprCampaignIds(): Collection
    {
        return collect((array) json_decode($this->opr_campaign_ids ?? '[]'));
    }

    public function isClosed(): bool
    {
        return $this->getIsValid() === StageSessionStatus::CLOSED;
    }

    public function isStarted(): bool
    {
        return $this->getIsValid() === StageSessionStatus::STARTED;
    }

    public function isStartedByMstStageId(string $mstStageId): bool
    {
        return $this->getMstStageId() === $mstStageId && $this->isStarted();
    }

    public function closeSession(): void
    {
        $this->is_valid = StageSessionStatus::CLOSED;
    }

    public function startSession(
        string $mstStageId,
        int $partyNo,
        Collection $oprCampaignIds,
        bool $isChallengeAd,
        int $lapCount,
    ): void {
        $this->mst_stage_id = $mstStageId;
        $this->is_valid = StageSessionStatus::STARTED;
        $this->party_no = $partyNo;
        $this->continue_count = 0;
        $this->is_challenge_ad = $isChallengeAd ? 1 : 0;
        $this->opr_campaign_ids = json_encode($oprCampaignIds->toArray());
        $this->auto_lap_count = $lapCount;
    }

    public function incrementContinueCount(): void
    {
        $this->continue_count++;
    }

    public function incrementDailyContinueAdCount(): void
    {
        $this->daily_continue_ad_count++;
    }

    public function getLatestResetAt(): string
    {
        return $this->latest_reset_at;
    }

    public function resetDaily(CarbonImmutable $now): void
    {
        $this->daily_continue_ad_count = 0;
        $this->latest_reset_at = $now->toDateTimeString();
    }

    public function makeModelKey(): string
    {
        return $this->usr_user_id;
    }

    public function isChallengeAd(): bool
    {
        return $this->is_challenge_ad === 1;
    }

    public function getAutoLapCount(): int
    {
        return $this->auto_lap_count;
    }
}
