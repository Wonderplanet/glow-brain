<x-filament-panels::page>
    <p><span class="font-bold">{{ $this->targetType }}</span>のリストを表示しています</p>
    <p>{{ $this::PER_PAGE_MAX }} 件ずつのリストで表示しています</p>
    <p>全対象件数：<span class="font-bold">{{ $this->allTargetCount }}</span> 件</p>
    {{ $this->infoList }}
</x-filament-panels::page>
