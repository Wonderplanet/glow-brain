<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Resource\Mst\Models\MstItem as BaseMstItem;
use App\Utils\AssetUtil;

class MstItem extends BaseMstItem implements IAssetImage
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_item_i18n()
    {
        return $this->hasOne(MstItemI18n::class, 'mst_item_id', 'id');
    }

    public function getName(): string
    {
        return $this->mst_item_i18n?->name ?? '';
    }

    public function getItemType(): string
    {
        return $this->type;
    }

    /**
     * Laravel Eloquentモデルでitem_type_label属性でアクセスされた際に自動的に呼び出させるアクセサ
     * 'item_type_label'と指定して属性アクセスした際に、実行される。
     */
    public function getItemTypeLabelAttribute(): string
    {
        $itemTypeEnum = ItemType::from($this->type);
        if ($itemTypeEnum === null) {
            return '';
        }
        return $itemTypeEnum->label();
    }

    public function isCharacterFragment(): bool
    {
        return $this->type === ItemType::CHARACTER_FRAGMENT->value;
    }

    public function isRankUpMaterial(): bool
    {
        return $this->type === ItemType::RANK_UP_MATERIAL->value;
    }

    public function isIdleCoinBox(): bool
    {
        return $this->type === ItemType::IDLE_COIN_BOX->value;
    }

    public function isIdleRankUpMaterialBox(): bool
    {
        return $this->type === ItemType::IDLE_RANK_UP_MATERIAL_BOX->value;
    }

    public function isRandomFragmentBox(): bool
    {
        return $this->type === ItemType::RANDOM_FRAGMENT_BOX->value;
    }

    public function isSelectionFragmentBox(): bool
    {
        return $this->type === ItemType::SELECTION_FRAGMENT_BOX->value;
    }

    public function isFragmentBox(): bool
    {
        return $this->isRandomFragmentBox() || $this->isSelectionFragmentBox();
    }

    public function makeAssetPath(): ?string
    {
        if ($this->isCharacterFragment()) {
            return AssetUtil::findAssetPathFromTemplates(
                ['item_icon_piece/item_icon_piece!{release_key}/item_icon_{asset_key}.png'],
                $this->asset_key,
                $this->release_key
            );
        }

        return AssetUtil::findAssetPathFromTemplates(
            ['item_icon/item_icon!{release_key}/item_icon_{asset_key}.png'],
            $this->asset_key,
            $this->release_key
        );
    }

    public function makeBgPath(): ?string
    {
        return AssetUtil::makeBgItemIconFramePathByRarity($this->rarity);
    }
}
