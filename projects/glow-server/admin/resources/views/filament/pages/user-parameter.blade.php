<x-filament-panels::page>
    <x-description-list :list="$userParameter" />
    <div>
        <x-filament::button :href="route('filament.admin.pages.edit-user-parameter', ['userId' => $userId])" tag="a">
            編集
        </x-filament::button>
    </div>
</x-filament-panels::page>
