@php
    $mstItem = $getState();

    use App\Filament\Pages\MstItemDetail;
    $url = MstItemDetail::getUrl(['mstItemId' => $mstItem->id]);
@endphp
<div >
    <a href="{{ $url }}" class="link">
        <span class="text-sm">
            [{{ $mstItem->id }}] {{ $mstItem->mst_item_i18n?->name ?? '' }}
        </span>
    </a>
</div>
