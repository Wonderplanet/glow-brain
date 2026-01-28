<x-filament-panels::page>
    <x-description-list :list="$userTutorial" />
    <div>
        @if(!empty($userTutorial))
            <x-filament::button :href="route('filament.admin.pages.edit-user-tutorial', ['userId' => $userId])" tag="a">
                編集
            </x-filament::button>
        @endif
    </div>
</x-filament-panels::page>
