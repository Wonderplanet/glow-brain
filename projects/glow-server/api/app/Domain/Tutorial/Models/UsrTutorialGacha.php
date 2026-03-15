<?php

declare(strict_types=1);

namespace App\Domain\Tutorial\Models;

use App\Domain\Common\Utils\StringUtil;
use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use Carbon\CarbonImmutable;

/**
 * @property string $id
 * @property string $usr_user_id
 * @property string|null $gacha_result_json
 * @property string|null $confirmed_at
 */
class UsrTutorialGacha extends UsrEloquentModel implements UsrTutorialGachaInterface
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

    /**
     * GachaResultJsonを配列化したデータを取得
     * @return array<mixed>
     */
    public function getGachaResultJson(): array
    {
        return (array)json_decode($this->gacha_result_json ?? '[]', true);
    }

    /**
     * GachaResultJsonを配列化したデータをセット
     * @param array<mixed> $gachaResultJson
     */
    public function setGachaResultJson(array $gachaResultJson): void
    {
        $this->gacha_result_json = json_encode($gachaResultJson);
    }

    public function getConfirmedAt(): ?string
    {
        return $this->confirmed_at;
    }

    /**
     * ガシャ結果確定済みかどうか
     * @return bool true: 確定済み、false: 未確定
     */
    public function isConfirmed(): bool
    {
        return StringUtil::isSpecified($this->confirmed_at);
    }

    /**
     * 確定済みにする
     * @param CarbonImmutable $now
     * @return void
     */
    public function confirm(CarbonImmutable $now): void
    {
        $this->confirmed_at = $now->toDateTimeString();
    }
}
