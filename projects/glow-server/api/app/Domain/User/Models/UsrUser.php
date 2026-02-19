<?php

declare(strict_types=1);

namespace App\Domain\User\Models;

use App\Domain\Common\Utils\StringUtil;
use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Entities\UsrUserEntity;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use App\Domain\Tutorial\Enums\TutorialFunctionName;
use Carbon\CarbonImmutable;

/**
 * @property string $id
 * @property int $status
 * @property string $tutorial_status
 * @property int $tos_version
 * @property int $privacy_policy_version
 * @property int $global_consent_version
 * @property int $iaa_version
 * @property string|null $bn_user_id
 * @property int $is_account_linking_restricted
 * @property string|null $client_uuid
 * @property string|null $suspend_end_at
 * @property string $game_start_at
 */
class UsrUser extends UsrEloquentModel implements UsrUserInterface
{
    use HasFactory;

    protected $fillable = [
        'game_start_at',
    ];

    public function getUsrUserId(): string
    {
        return $this->id;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getTutorialStatus(): string
    {
        return $this->tutorial_status;
    }

    public function setTutorialStatus(string $tutorialStatus): void
    {
        $this->tutorial_status = $tutorialStatus;
    }

    /**
     * チュートリアル未プレイかどうか
     * @return bool true: 未プレイ, false: 1つ以上プレイ済み
     */
    public function isTutorialUnplayed(): bool
    {
        return StringUtil::isNotSpecified($this->tutorial_status);
    }

    public function isMainPartTutorialCompleted(): bool
    {
        return $this->tutorial_status === TutorialFunctionName::MAIN_PART_COMPLETED->value;
    }

    public function getTosVersion(): int
    {
        return $this->tos_version;
    }

    public function setTosVersion(int $tosVersion): void
    {
        $this->tos_version = $tosVersion;
    }

    public function getPrivacyPolicyVersion(): int
    {
        return $this->privacy_policy_version;
    }

    public function setPrivacyPolicyVersion(int $privacyPolicyVersion): void
    {
        $this->privacy_policy_version = $privacyPolicyVersion;
    }

    public function getGlobalConsentVersion(): int
    {
        return $this->global_consent_version;
    }

    public function setGlobalConsentVersion(int $globalConsentVersion): void
    {
        $this->global_consent_version = $globalConsentVersion;
    }

    public function setBnUserId(string $bnUserId): void
    {
        $this->bn_user_id = $bnUserId;
    }

    public function getBnUserId(): ?string
    {
        return $this->bn_user_id;
    }

    public function hasBnUserId(): bool
    {
        return StringUtil::isSpecified($this->bn_user_id);
    }

    public function setIsAccountLinkingRestricted(int $isAccountLinkingRestricted): void
    {
        $this->is_account_linking_restricted = $isAccountLinkingRestricted;
    }

    public function isAccountLinkingRestricted(): bool
    {
        return $this->is_account_linking_restricted === 1;
    }

    public function getClientUuid(): ?string
    {
        return $this->client_uuid;
    }

    public function getSuspendEndAt(): ?string
    {
        return $this->suspend_end_at;
    }

    public function getGameStartAt(): ?string
    {
        return $this->game_start_at;
    }

    public function getCreatedAt(): ?CarbonImmutable
    {
        return new CarbonImmutable($this->created_at);
    }

    public function getIaaVersion(): int
    {
        return $this->iaa_version;
    }

    public function setIaaVersion(int $iaaVersion): void
    {
        $this->iaa_version = $iaaVersion;
    }

    public function toEntity(): UsrUserEntity
    {
        return new UsrUserEntity(
            $this->getUsrUserId(),
            $this->getBnUserId(),
            $this->hasBnUserId(),
            $this->getTutorialStatus(),
            $this->getGameStartAt(),
            $this->getClientUuid(),
        );
    }
}
