<?php

namespace App\Models\Adm;

class AdmPromotionTag extends AdmModel
{
    protected $table = 'adm_promotion_tags';

    protected $guarded = [];

    /**
     * 直近作成されたタグを取得して、テーブル表示時の選択肢リストの形で返す
     * @param int $limit
     * @return array
     */
    public static function getLatestTagOptions(
        int $limit = 10
    ): array {
        return self::orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->pluck('id', 'id')
            ->toArray();
    }

    public function formatToResponse(): array
    {
        $array = parent::toArray();

        unset($array['adm_promotion_tag_groups']);

        return $array;
    }

    public static function createFromResponseArray(array $response): self
    {
        $model = new self();
        $model->fill($response);
        return $model;
    }
}
