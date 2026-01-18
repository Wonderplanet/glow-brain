<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\LogTriggers;

use App\Domain\Item\Enums\ItemType;
use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;
use App\Domain\Resource\Mst\Entities\MstItemEntity;

class ItemFragmentBoxLogTrigger extends LogTrigger
{
    private string $fragmentBoxType;

    public function __construct(MstItemEntity $consumeMstItem)
    {
        $this->fragmentBoxType = match ($consumeMstItem->getItemType()) {
            ItemType::RANDOM_FRAGMENT_BOX->value => ItemType::RANDOM_FRAGMENT_BOX->value,
            ItemType::SELECTION_FRAGMENT_BOX->value => ItemType::SELECTION_FRAGMENT_BOX->value,
            default => '',
        };
    }

    public function getLogTriggerData(): LogTriggerDto
    {
        return new LogTriggerDto(
            LogResourceTriggerSource::ITEM_FRAGMENT_BOX_COST->value,
            $this->fragmentBoxType,
        );
    }
}
