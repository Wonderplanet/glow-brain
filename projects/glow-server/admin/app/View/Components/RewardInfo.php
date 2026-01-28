<?php

namespace App\View\Components;

use App\Entities\RewardInfo as RewardInfoEntity;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RewardInfo extends Component
{
    public ?string $resourceId = null;
    public string $name = '';
    public int $amount = 0;
    public ?string $detailUrl = null;
    public ?string $resourceType = null;
    public ?string $assetPath = '';
    public ?string $bgPath = '';

    public function __construct(?RewardInfoEntity $rewardInfo)
    {
        if ($rewardInfo === null) {
            $this->name = '不明な報酬';
            return;
        }
        $this->resourceId = $rewardInfo->getResourceId();
        $this->name = $rewardInfo->getName();
        $this->amount = $rewardInfo->getAmount();
        $this->detailUrl = $rewardInfo->getDetailUrl();
        $this->resourceType = $rewardInfo->getResourceType();
        $this->assetPath = $rewardInfo->getAssetPath();
        $this->bgPath = $rewardInfo->getBgPath();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.reward-info');
    }
}
