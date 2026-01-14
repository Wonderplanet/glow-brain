@php
    $htmlDescription = $getRecord()?->mng_in_game_notice_i18n?->description;

    // TODO: フォントサイズなどをクライアント表示と合わせるようにする
@endphp

{{-- このbladeのみに対して有効なcssを記述する --}}
<style>
    /* heading */
    #tip-tap-html-column h1 {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.5;
    }
    #tip-tap-html-column h2 {
        font-size: 1.25rem;
        font-weight: 600;
        line-height: 1.5;
    }

    /* 箇条書き ドット始まり */
    #tip-tap-html-column ul {
        list-style-type: disc;
        padding-left: 1.5rem;
    }

    /* 番号付きリスト */
    #tip-tap-html-column ol {
        list-style-type: decimal;
        padding-left: 1.5rem;
    }
</style>

<div id="tip-tap-html-column" class="text-sm leading-6 px-3 py-4">
    @if ($htmlDescription)
        {!! tiptap_converter()->asHTML($htmlDescription) !!}
    @endif
</div>
