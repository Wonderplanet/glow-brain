<?php

declare(strict_types=1);

namespace App\Domain\User\Models;

use App\Domain\Common\Utils\StringUtil;
use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Entities\UsrUserProfileEntity;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Crypt;

/**
 * @property string $usr_user_id
 * @property string $name
 * @property string $birth_date
 * @property string $mst_unit_id
 * @property string $mst_emblem_id
 * @property CarbonImmutable $name_update_at
 */
class UsrUserProfile extends UsrEloquentModel implements UsrUserProfileInterface
{
    use HasFactory;

    protected $primaryKey = 'usr_user_id';

    protected $fillable = [
        'usr_user_id',
    ];

    public function getMyId(): string
    {
        return $this->my_id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $newName, CarbonImmutable $nameUpdateAt): void
    {
        $this->name = $newName;
        $this->name_update_at = $nameUpdateAt;
    }

    public function getNameUpdateAt(): string
    {
        return (string) $this->name_update_at;
    }

    public function getBirthDate(): ?int
    {
        if ($this->hasBirthDate() === false) {
            return null;
        }

        // 暗号化キー(APP_KEY)が変更された場合、復号化に失敗する可能性があるため、nullを返す
        // nullで返すと、生年月日を再登録できてしまうが、以下の理由で許容と判断しました
        // - 暗号化キーが変更されることはほぼない
        // - 例外を投げると、生年月日情報のためだけに、ゲーム進行できなくなるのは避けたい
        // - 暗号化キーが変更された場合、すべての既存データが無効なデータになるので、更新されてほしい
        try {
            return (int) Crypt::decryptString($this->birth_date);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function setBirthDate(int $birthDate): void
    {
        $this->birth_date = Crypt::encryptString((string) $birthDate);
    }

    public function hasBirthDate(): bool
    {
        return StringUtil::isSpecified($this->birth_date);
    }

    public function getMstUnitId(): string
    {
        return $this->mst_unit_id;
    }

    public function setMstUnitId(string $mstUnitId): void
    {
        $this->mst_unit_id = $mstUnitId;
    }

    public function getMstEmblemId(): string
    {
        return $this->mst_emblem_id;
    }

    public function setMstEmblemId(string $mstEmblemId): void
    {
        $this->mst_emblem_id = $mstEmblemId;
    }

    public function setNameUpdateAt(?CarbonImmutable $nameUpdateAt): void
    {
        $this->name_update_at = $nameUpdateAt;
    }

    /**
     * ユーザー名を変更するのが初めてかどうか
     * @return bool true:初めて false:2回目以降
     */
    public function isFirstNameChange(): bool
    {
        return $this->name_update_at === null;
    }

    public function makeModelKey(): string
    {
        return $this->usr_user_id;
    }

    public function toEntity(): UsrUserProfileEntity
    {
        return new UsrUserProfileEntity(
            $this->usr_user_id,
            $this->name,
            $this->mst_unit_id,
            $this->mst_emblem_id,
            $this->getBirthDate(),
            $this->hasBirthDate(),
            $this->my_id,
        );
    }
}
