<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Entities;

readonly class NullAllowanceCallbackEntity
{
    /**
     * コンストラクタ
     *
     * AllowanceがNullのときに実行されるコールバック返り値専用エンティティ
     *
     * @param string $osPlatform OSプラットフォーム
     * @param string $productId ストアプロダクトID
     * @param string $productSubId サブプロダクトID
     * @param string $deviceId デバイスID
     * @param array<mixed> $detail ログ出力用の詳細情報
     */
    public function __construct(
        public readonly string $osPlatform,
        public readonly string $productId,
        public readonly string $productSubId,
        public readonly string $deviceId,
        public readonly array $detail = [],
    ) {
    }
}
