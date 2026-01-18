<x-filament-panels::page>
    <!-- 基本情報 -->
    {{$this->infoList}}

    <!-- アイテムタイプごとの詳細情報 -->
    {{$this->characterFragmentInfoList()}}
    {{ $this->randomFragmentBoxTable() }}
    {{ $this->selectionFragmentBoxTable() }}
</x-filament-panels::page>
