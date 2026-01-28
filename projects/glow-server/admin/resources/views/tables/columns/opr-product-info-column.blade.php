@php
    $oprProduct = $getState();

    use App\Filament\Pages\OprProductDetail;
    if ($oprProduct) {
        $url = OprProductDetail::getUrl(['productSubId' => $oprProduct->id]);
    }
@endphp
<div>
    @if ($oprProduct)
        <a href="{{ $url }}" class="link">
            <span class="text-sm">
                [{{ $oprProduct->id }}] {{ $oprProduct->product_info }}
            </span>
        </a>
    @endif
</div>
