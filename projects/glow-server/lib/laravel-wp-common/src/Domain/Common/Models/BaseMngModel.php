<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Common\Models;

// use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;
use WonderPlanet\Domain\Common\Models\BaseModel;
use WonderPlanet\Domain\Common\Utils\DBUtility;

/**
 * mng DBレコードの基底クラス
 */
abstract class BaseMngModel extends BaseModel
{
    use HasUuids;

    /**
     * 主キーが整数でないため、自動incrementを無効化する
     *
     * @var boolean
     */
    public $incrementing = false;

    /**
     * 主キーの型をstringにする
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @var boolean
     */
    public $timestamps = true;

    /**
     *
     * @param array<mixed> $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // tableにクラス名からテーブル名を設定する
        // デフォルトはクラス名のスネークケース(複数形)になるので、それを変換テーブルを通して設定されたテーブル名に直す
        $this->table = DBUtility::getTableName(Str::snake(Str::pluralStudly(class_basename($this))));
    }

    /**
     * コネクション名を指定
     *
     * @return string
     */
    protected function getConnNameInternal(): string
    {
        return DBUtility::getMngConnName();
    }
}
