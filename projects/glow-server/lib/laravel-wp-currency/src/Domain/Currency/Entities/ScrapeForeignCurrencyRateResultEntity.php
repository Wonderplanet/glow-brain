<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Entities;

use Exception;

/**
 * scrapeForeignCurrencyRate メソッドの結果を返す
 *
 * 為替相場と現地参考為替相場のスクレイピング結果をまとめて返すためクラスを作成した
 *
 * @see \WonderPlanet\Domain\Currency\Services\CurrencyAdminService::scrapeForeignCurrencyRate
 */
class ScrapeForeignCurrencyRateResultEntity
{
    /**
     * コンストラクタ
     *
     * @param boolean $isForeignRateSuccess
     * @param boolean $isLocalReferenceSuccess
     * @param string $foreignRateErrorMessage
     * @param string $localReferenceErrorMessage
     * @param Exception|null $foreignRateException
     * @param Exception|null $localReferenceException
     */
    public function __construct(
        private bool $isForeignRateSuccess,
        private bool $isLocalReferenceSuccess,
        private string $foreignRateErrorMessage = '',
        private string $localReferenceErrorMessage = '',
        private ?Exception $foreignRateException = null,
        private ?Exception $localReferenceException = null,
    ) {
    }

    /**
     * 外貨為替レートのスクレイピングが成功したかどうか
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
     * 現地参考為替レートのスクレイピングが成功したかどうか
     *
     * @return bool
     */
    public function isLocalReferenceSuccess(): bool
    {
        return $this->isLocalReferenceSuccess;
    }

    /**
     * 現地参考為替レートのスクレイピングが成功したかどうかを設定する
     *
     * @param boolean $isLocalReferenceSuccess
     * @return void
     */
    public function setLocalReferenceSuccess(bool $isLocalReferenceSuccess): void
    {
        $this->isLocalReferenceSuccess = $isLocalReferenceSuccess;
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
     * 現地参考為替レートのスクレイピングで発生したエラーメッセージ
     *
     * @return string
     */
    public function getLocalReferenceErrorMessage(): string
    {
        return $this->localReferenceErrorMessage;
    }

    /**
     * 現地参考為替レートのスクレイピングで発生したエラーメッセージを設定する
     *
     * @param string $localReferenceErrorMessage
     * @return void
     */
    public function setLocalReferenceErrorMessage(string $localReferenceErrorMessage): void
    {
        $this->localReferenceErrorMessage = $localReferenceErrorMessage;
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

    /**
     * 現地参考為替レートのスクレイピングで発生した例外
     *
     * @return Exception|null
     */
    public function getLocalReferenceException(): ?Exception
    {
        return $this->localReferenceException;
    }

    /**
     * 現地参考為替レートのスクレイピングで発生した例外を設定する
     *
     * @param Exception|null $localReferenceException
     * @return void
     */
    public function setLocalReferenceException(?Exception $localReferenceException): void
    {
        $this->localReferenceException = $localReferenceException;
    }
}
