<!-- 下線を引いたテキストインプットを表示するコンポーネント -->
@php
    $style = 'border: none; outline: none; border-bottom: 1px solid #000; background-color: transparent; box-shadow: none;';
    if (isset($addStyle)) {
        # addStyleの定義があれば既存スタイルに追加
        $style .= $addStyle;
    }
    if (isset($forceStyle)) {
        # forceStyleの定義があれば既存スタイルに上書き
        $style = $forceStyle;
    }
@endphp

<input
    style="{{ $style }}"
    placeholder="{{ $placeholder }}"
    wire:model="{{ $getStatePath() }}"
    {{ $disabled }}
/>


