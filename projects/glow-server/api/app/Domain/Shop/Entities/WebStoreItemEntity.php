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
     * @param int|null $amount アイテムの金額（W5: items[*].amount）
     */
    public function __construct(
        private readonly string $type,
        private readonly string $sku,
        private readonly ?int $quantity = null,
        private readonly ?int $amount = null,
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

    public function getAmount(): int
    {
        return $this->amount ?? 0;
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
        // W4: purchase.order.lineitems[*].price.amount (数値)
        // W5: items[*].amount (文字列)
        $amount = null;
        if (isset($data['amount'])) {
            // W5の場合
            $amount = (int)$data['amount'];
        } elseif (isset($data['price']['amount'])) {
            // W4の場合
            $amount = (int)$data['price']['amount'];
        }

        return new self(
            type: $data['type'] ?? '',
            sku: $data['sku'] ?? '',
            quantity: $data['quantity'] ?? null,
            amount: $amount,
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
