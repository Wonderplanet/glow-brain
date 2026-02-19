<?php

declare(strict_types=1);

namespace App\Domain\Item\Entities;

/**
 * 放置ボックスアイテムをリアルリソースへ交換するためのEntityのインターフェース
 *
 * 基本的に、itemドメインで実体のオブジェクトを生成し、
 * idleIncentiveドメインでItemIdleBoxRewardExchangeInterfaceを介して利用する。
 * interfaceを介さずに直接利用すると、itemとidleIncentiveドメイン間での疎結合性が低下するため。
 */
interface ItemIdleBoxRewardExchangeInterface
{
    public function getType(): string;

    public function getResourceId(): ?string;

    public function getBeforeAmount(): int;

    public function getAfterAmount(): int;

    public function setAfterAmount(int $afterAmount): void;

    public function getItemType(): string;

    public function getIdleMinutes(): int;
}
