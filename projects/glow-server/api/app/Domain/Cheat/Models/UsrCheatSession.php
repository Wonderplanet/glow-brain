<?php

declare(strict_types=1);

namespace App\Domain\Cheat\Models;

use App\Domain\Cheat\Entities\CheatSessionParty;
use App\Domain\Cheat\Entities\CheatSessionPartyWithOpponent;
use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;

/**
 * @property string $usr_user_id
 * @property string $content_type
 * @property string $target_id
 * @property string $party_status
 */
class UsrCheatSession extends UsrEloquentModel implements UsrCheatSessionInterface
{
    use HasFactory;

    protected $primaryKey = 'usr_user_id';

    protected $fillable = [
        'usr_user_id',
        'content_type',
        'target_id',
        'party_status',
    ];

    public function getUsrUserId(): string
    {
        return $this->usr_user_id;
    }

    public function getContentType(): string
    {
        return $this->content_type;
    }

    public function getTargetId(): string
    {
        return $this->target_id;
    }

    public function getPartyStatus(): string
    {
        return $this->party_status;
    }

    public function init(string $usrUserId): UsrCheatSession
    {
        $this->usr_user_id = $usrUserId;
        $this->content_type = '';
        $this->target_id = '';
        $this->party_status = json_encode([]);

        return $this;
    }

    public function setPartyStatus(
        string $contentType,
        string $targetId,
        CheatSessionParty $cheatSessionParty
    ): void {
        $this->content_type = $contentType;
        $this->target_id = $targetId;
        $this->party_status = $cheatSessionParty->toJson();
    }

    /**
     * 対戦相手がいる場合のパーティステータスを設定（PvP等で使用）
     */
    public function setPartyStatusWithOpponent(
        string $contentType,
        string $targetId,
        CheatSessionPartyWithOpponent $sessionWithOpponent
    ): void {
        $this->content_type = $contentType;
        $this->target_id = $targetId;
        $this->party_status = $sessionWithOpponent->toJson();
    }

    public function makeModelKey(): string
    {
        return $this->usr_user_id;
    }
}
