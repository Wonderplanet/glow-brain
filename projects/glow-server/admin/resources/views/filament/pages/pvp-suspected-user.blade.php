<x-filament-panels::page>
    <div>
        <x-filament::breadcrumbs :breadcrumbs="$this->breadcrumbList" />
    </div>
    @if ($this->aggregationFlg)
        <div class="bg-primary-100 border-l-4 border-primary-500 text-primary-700 p-4 mb-4 rounded-r">
            <p class="font-bold text-lg">集計期間が終了してるため、復帰できません。</p>
        </div>
    @else
        <form method="POST" wire:submit="reactivateUserInRanking">
            <div class="py-4">
                <x-filament::button type="submit">一括復帰</x-filament::button>
            </div>
        </form>
    @endif

    {{ $this->table }}
</x-filament-panels::page>
