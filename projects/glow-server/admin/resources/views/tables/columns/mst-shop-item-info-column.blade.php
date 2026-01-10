@php
    $rewardInfo = $getState();

    use App\Filament\Pages\MstShopItems;
    $url = MstShopItems::getUrl(['mstShopItemId' => $rewardInfo->getId()]);
@endphp
<div >
    <a href="{{ $url }}" class="link">
        <span class="text-sm">
            <x-reward-info :rewardInfo="$rewardInfo" />
        </span>
    </a>
</div>
