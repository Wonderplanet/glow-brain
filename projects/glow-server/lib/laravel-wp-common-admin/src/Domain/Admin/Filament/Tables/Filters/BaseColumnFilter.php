<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Admin\Filament\Tables\Filters;

use Filament\Tables\Filters\Filter;

/**
 * カラム検索をするための基本クラス
 * デフォルトではmake($name)で指定されたものを使用する
 *
 * 検索する対象のカラム名を設定するプロパティを用意している
 * 単純な検索のみを想定しているので、複雑なフィルタをかけたい場合はqueryで個別に拡張すること
 *
 */
class BaseColumnFilter extends Filter
{
    /**
     * 検索対象のカラム名
     *
     * @var string
     */
    protected $columnName = '';

    /**
     * 兼卓対象のカラム名を取得
     *
     * @return string
     */
    public function getColumnName(): string
    {
        return $this->columnName;
    }

    /**
     * 検索対象のカラム名を設定
     * カラム名だけ変えたくなった場合に使う
     *
     * @param string $columnName
     * @return self
     */
    public function columnName(string $columnName): self
    {
        $this->columnName = $columnName;
        return $this;
    }

    /**
     * 初期設定
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 検索対象のカラム名を設定
        // デフォルトは$name
        $this->columnName = $this->name;
    }
}
