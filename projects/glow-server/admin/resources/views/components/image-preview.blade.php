@props(['url' => null, 'label' => '画像'])

@if($url)
    <div class="mt-2 bg-base-100 w-96">
        <label class="text-sm">{{ $label }}</label>
        <figure>
            <img
                class="mt-2"
                src="{{ $url }}"
                />
        </figure>
    </div>
@endif
