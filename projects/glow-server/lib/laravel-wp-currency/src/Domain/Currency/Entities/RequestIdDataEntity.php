<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Entities;

use WonderPlanet\Domain\Currency\Enums\RequestIdType;

/**
 * ログに記録するリクエストIDのデータを保持するエンティティ
 */
class RequestIdDataEntity
{
    public function __construct(
        private string $requestId,
        private RequestIdType $requestIdType
    ) {
    }

    /**
     * リクエストIDを取得する
     *
     * @return string
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }

    /**
     * リクエストIDのタイプを取得する
     *
     * @return RequestIdType
     */
    public function getRequestIdType(): RequestIdType
    {
        return $this->requestIdType;
    }

    /**
     * リクエストIDのタイプとIDを取得する
     *
     * 例: 'gen:123456'
     *
     * @return string
     */
    public function getRequestTypeAndId(): string
    {
        return $this->requestIdType->value . ':' . $this->requestId;
    }
}
