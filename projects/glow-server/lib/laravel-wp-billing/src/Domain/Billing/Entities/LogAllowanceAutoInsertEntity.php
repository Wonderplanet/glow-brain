<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Entities;

/**
 * allowance自動登録でdetailに出力するログエンティティ
 */
readonly class LogAllowanceAutoInsertEntity
{
    /**
     * コンストラクタ
     *
     * @param string $process
     * @param string $requestProductId
     * @param array<mixed> $callbackDetail
     */
    public function __construct(
        public readonly string $process,
        public readonly string $requestProductId,
        public readonly array $callbackDetail = [],
    ) {
    }

    /**
     * Json形式文字列に変換
     *
     * @return string
     */
    public function toDetail(): string
    {
        return json_encode([
            'process' => $this->process,
            'request_product_id' => $this->requestProductId,
            'callback_detail' => $this->callbackDetail,
        ]);
    }
}
