<?php

declare(strict_types=1);

namespace App\Domain\Shop\Entities;

/**
 * WebStore購入アイテムEntity
 *
 * Xsollaウェブフックで送信されるitemsの構造を表現するEntity
 * 参考: docs/sdd/features/外部決済/ゲーム体験仕様書.pdf p.18
 */
class WebStoreItemEntity
{
    /**
     * @param string $type アイテムタイプ（例: 'virtual_good', 'bundle' など）
     * @param string $sku 商品SKU（Xsollaに登録された一意のID）
     * @param int|null $quantity 数量（オプション）
     */
    public function __construct(
        private readonly string $type,
        private readonly string $sku,
        private readonly ?int $quantity = null,
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    /**
     * virtual_goodタイプかどうかを判定
     */
    public function isVirtualGood(): bool
    {
        return $this->type === 'virtual_good';
    }

    /**
     * 配列からEntityを生成
     *
     * @param array<string, mixed> $data アイテムデータ配列
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? '',
            sku: $data['sku'] ?? '',
            quantity: $data['quantity'] ?? null,
        );
    }

    /**
     * Entityを配列に変換
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'sku' => $this->sku,
            'quantity' => $this->quantity,
        ];
    }
}
