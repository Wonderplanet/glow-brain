<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Entities;

use Illuminate\Support\Carbon;

/**
 * Eloquentモデルからデータを引き継ぐEntityの基底クラス
 *
 * 共通するプロパティや処理を書いておく
 */
abstract class BaseModelEntity
{
    // 共通カラム
    //   created_atとupdated_atはほぼ全てのテーブルに存在することと、
    //   存在していたらEloquentモデルの管理になるのでここで定義しておく
    //   存在しない場合はnullとなる
    public ?Carbon $created_at;
    public ?Carbon $updated_at;

    public function __construct(?object $baseModel)
    {
        $this->created_at = $baseModel->created_at ?? null;
        $this->updated_at = $baseModel->updated_at ?? null;
    }

    // getter
    public function getCreatedAt(): ?Carbon
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?Carbon
    {
        return $this->updated_at;
    }
}
