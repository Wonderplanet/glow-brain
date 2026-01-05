<x-filament-panels::page>
    <x-description-list :list="$userIdleIncentive" />
    <div>
        @if(!empty($userIdleIncentive))
            <x-filament::button :href="route('filament.admin.pages.edit-user-idle-incentive', ['userId' => $userId])" tag="a">
                編集
            </x-filament::button>
        @endif
    </div>
</x-filament-panels::page>
