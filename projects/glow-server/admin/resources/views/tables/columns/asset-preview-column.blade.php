@php
    $assetPath = $makeAssetPath();
    $width = $getAssetWidth();
    $isHtml = $isHtmlContentType();
    $isImage = $isImageContentType();
@endphp
<div class="p-2">
    @if ($isImage)
        {{-- 画像表示 --}}
        <img src="{{ $assetPath }}" style="width: {{ $width }}px;" class="rounded-lg shadow" />
    @elseif ($isHtml)
        {{-- HTML用アイコン表示 --}}
        <div class="flex items-center justify-center bg-gray-100 dark:bg-gray-800 rounded"
             style="width: {{ $width }}px; height: {{ $width * 0.75 }}px;">
            <x-heroicon-o-document-text class="w-12 h-12 text-gray-400" />
        </div>
    @else
        {{-- その他ファイル用アイコン表示（プレビュー不可） --}}
        <div class="flex items-center justify-center bg-gray-100 dark:bg-gray-800 rounded"
             style="width: {{ $width }}px; height: {{ $width * 0.75 }}px;">
            <x-heroicon-o-document class="w-12 h-12 text-gray-400" />
        </div>
    @endif
</div>
<p class="text-sm font-medium text-gray-950 dark:text-white mt-1">{{ $getObjectName() }}</p>
