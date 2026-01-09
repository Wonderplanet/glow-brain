<?php

declare(strict_types=1);

namespace App\Traits;

use App\Constants\AthenaConstant;
use App\Constants\SystemConstants;
use Carbon\CarbonImmutable;

/**
 * Athenaクエリ結果からモデルを作成するための共通メソッドを提供するトレイト
 *
 * 使用するクラスはIAthenaModelを実装する必要があります
 */
trait AthenaModelTrait
{
    /**
     * Athenaクエリ結果の配列からモデルインスタンスを作成する
     * 配列のキーをそのままモデルのプロパティにマッピングします
     */
    public static function createFromAthenaArray(array $data): static
    {
        $model = new self();
        $model->fillFromAthenaData($data);
        return $model;
    }

    /**
     * 配列のキーをモデルのプロパティに自動マッピングする
     * 日時フィールドは自動的にタイムゾーン変換を行う
     */
    protected function fillFromAthenaData(array $data): void
    {
        $dateTimeFields = ['created_at', 'updated_at'];

        foreach ($data as $key => $value) {
            if (in_array($key, $dateTimeFields) && !is_null($value)) {
                $this->$key = $this->parseAthenaDateTime($value);
            } else {
                $this->$key = $value;
            }
        }
    }


    /**
     * Athenaの日時文字列をビューのタイムゾーンに変換する
     */
    private function parseAthenaDateTime(string $datetime): string
    {
        return CarbonImmutable::parse($datetime, AthenaConstant::ATHENA_DATETIME_TIMEZONE)
            ->setTimezone(SystemConstants::VIEW_TIMEZONE)
            ->toDateTimeString();
    }
}
