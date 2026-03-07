<x-filament-panels::page>
    <x-description-list :title="'基本情報'" :list="$this->getBasicInfo()" />

    <x-filament::section heading="BOXグループ一覧">
        @foreach ($this->getGroupTableRows() as $groupInfo)
            <x-filament::card class="mb-4">
                <div class="mb-4">
                    <h4 class="font-bold text-lg">{{ $groupInfo['title'] }}</h4>
                </div>
                <x-table
                    :title="'賞品テーブル'"
                    :columns="['賞品ID', '報酬', '在庫数', 'ピックアップ']"
                    :rows="$groupInfo['prizes']"
                />
            </x-filament::card>
        @endforeach
    </x-filament::section>
</x-filament-panels::page>
