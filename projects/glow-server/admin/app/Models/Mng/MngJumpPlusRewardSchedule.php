<?php

namespace App\Models\Mng;

use App\Constants\Database;
use App\Domain\Resource\Mng\Models\MngJumpPlusRewardSchedule as BaseMngJumpPlusRewardSchedule;
use Carbon\CarbonImmutable;

class MngJumpPlusRewardSchedule extends BaseMngJumpPlusRewardSchedule
{
    protected $connection = Database::MANAGE_DATA_CONNECTION;

    protected $guarded = [];

    protected $casts = [
        'start_at' => 'datetime:Y-m-d H:i:s',
        'end_at' => 'datetime:Y-m-d H:i:s',
    ];

    // editページがnotfoundになるので、uuidバリデーションを無効化する
    protected function isValidUniqueId($value): bool
    {
        return true;
    }

    /**
     * ジャンプ+報酬との関連を取得
     */
    public function mng_jump_plus_rewards()
    {
        return $this->hasMany(MngJumpPlusReward::class, 'group_id', 'group_id');
    }

    /**
     * スケジュールのステータスを計算
     */
    public function calcStatus(CarbonImmutable $now): string
    {
        if ($now < $this->start_at) {
            return '期間前';
        } elseif ($now > $this->end_at) {
            return '期間外';
        } else {
            return '期間中';
        }
    }

    /**
     * ステータスバッジの色を計算
     */
    public function calcStatusBadgeColor(CarbonImmutable $now): string
    {
        $status = $this->calcStatus($now);

        switch ($status) {
            case '期間前':
                return 'primary';
            case '期間中':
                return 'success';
            case '期間外':
                return 'gray';
            default:
                return 'gray';
        }
    }

    public function formatToResponse(): array
    {
        $array = parent::toArray();

        unset($array['mng_jump_plus_rewards']);

        return $array;
    }

    public static function createFromResponseArray(array $response): self
    {
        $model = new self();
        $model->fill($response);
        return $model;
    }

    public function formatToInsertArray(): array
    {
        $array = $this->toArray();

        $now = CarbonImmutable::now();
        $array['created_at'] = $now;
        $array['updated_at'] = $now;

        return $array;
    }

    public function formatToValidationMaster(): array
    {
        return [
            'id' => $this->id,
            'start_at' => $this->start_at->utc()->toDateTimeString(),
            'end_at' => $this->end_at->utc()->toDateTimeString(),
        ];
    }
}
