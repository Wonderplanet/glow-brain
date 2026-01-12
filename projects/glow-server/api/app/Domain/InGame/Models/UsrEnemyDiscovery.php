<?php

declare(strict_types=1);

namespace App\Domain\InGame\Models;

use App\Domain\Resource\Enums\EncyclopediaCollectStatus;
use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Entities\UsrEnemyDiscoveryEntity;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;

/**
 * @property string $mst_enemy_character_id
 */
class UsrEnemyDiscovery extends UsrEloquentModel implements UsrEnemyDiscoveryInterface
{
    use HasFactory;

    /**
     * UsrModelManagerでキャッシュ管理する際に使うユニークキーを作成する
     */
    public function makeModelKey(): string
    {
        return self::makeModelKeyAsStatic(
            $this->usr_user_id,
            $this->mst_enemy_character_id,
        );
    }

    /**
     * Repositoryでキャッシュからデータを効率良く取得できるように、staticでキーを生成するメソッドを用意
     */
    public static function makeModelKeyAsStatic(
        string $usrUserId,
        string $mstEnemyCharacterId,
    ): string {
        return $usrUserId . $mstEnemyCharacterId;
    }

    public function getMstEnemyCharacterId(): string
    {
        return $this->mst_enemy_character_id;
    }

    public function getIsNewEncyclopedia(): int
    {
        return $this->is_new_encyclopedia;
    }

    public function markAsCollected(): void
    {
        $this->is_new_encyclopedia = EncyclopediaCollectStatus::IS_NOT_NEW->value;
    }

    public function isAlreadyCollected(): bool
    {
        return $this->is_new_encyclopedia === EncyclopediaCollectStatus::IS_NOT_NEW->value;
    }

    public function toEntity(): UsrEnemyDiscoveryEntity
    {
        return new UsrEnemyDiscoveryEntity(
            $this->usr_user_id,
            $this->mst_enemy_character_id,
            $this->is_new_encyclopedia,
        );
    }
}
