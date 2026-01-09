<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Entities;

use Exception;

/**
 * scrapeForeignCurrencyDailyRate メソッドの結果を返す
 *
 * 本日の為替相場のスクレイピング結果をまとめて返すためクラスを作成した
 *
 * @see \WonderPlanet\Domain\Currency\Services\CurrencyAdminService::scrapeForeignCurrencyDailyRate
 */
class ScrapeForeignCurrencyDailyRateResultEntity
{
    /**
     * コンストラクタ
     *
     * @param boolean $isForeignRateSuccess
     * @param string $foreignRateErrorMessage
     * @param Exception|null $foreignRateException
     */
    public function __construct(
        private bool $isForeignRateSuccess,
        private string $foreignRateErrorMessage = '',
        private ?Exception $foreignRateException = null,
    ) {
    }

    /**
     * 本日の外貨為替レートのスクレイピングが成功したかどうか
     *
     * @return bool
     */
    public function isForeignRateSuccess(): bool
    {
        return $this->isForeignRateSuccess;
    }

    /**
     * 現地参考為替レートのスクレイピングが成功したかどうかを設定する
     *
     * @param boolean $isForeignRateSuccess
     * @return void
     */
    public function setForeignRateSuccess(bool $isForeignRateSuccess): void
    {
        $this->isForeignRateSuccess = $isForeignRateSuccess;
    }

    /**
     * 外貨為替レートのスクレイピングで発生したエラーメッセージ
     *
     * @return string
     */
    public function getForeignRateErrorMessage(): string
    {
        return $this->foreignRateErrorMessage;
    }

    /**
     * 外貨為替レートのスクレイピングで発生したエラーメッセージを設定する
     *
     * @param string $foreignRateErrorMessage
     * @return void
     */
    public function setForeignRateErrorMessage(string $foreignRateErrorMessage): void
    {
        $this->foreignRateErrorMessage = $foreignRateErrorMessage;
    }

    /**
     * 外貨為替レートのスクレイピングで発生した例外
     *
     * @return Exception|null
     */
    public function getForeignRateException(): ?Exception
    {
        return $this->foreignRateException;
    }

    /**
     * 外貨為替レートのスクレイピングで発生した例外を設定する
     *
     * @param Exception|null $foreignRateException
     * @return void
     */
    public function setForeignRateException(?Exception $foreignRateException): void
    {
        $this->foreignRateException = $foreignRateException;
    }
}
