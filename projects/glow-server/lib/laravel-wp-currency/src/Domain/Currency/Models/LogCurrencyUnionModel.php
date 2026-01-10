<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Models;

class LogCurrencyUnionModel extends BaseLogModel
{
    /**
     * UNIONした結果を扱うため、テーブル名は設定しない
     *
     * @var string
     */
    protected $table = '';

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
}
