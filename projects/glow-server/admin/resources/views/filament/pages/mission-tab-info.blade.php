@php
    $tabGroups = $this->getTabGroups();
@endphp
<x-filament-panels::page>
    <div>
        @foreach($tabGroups as $tabs)
            <x-filament::tabs :contained="true">
                @foreach($tabs as $key => $value)
                    <h2 class="menu-title">{{$key}}</h2>
                    @foreach($value as $tabName => $routeName)
                        <x-filament::tabs.item
                            :active="$currentTab === $tabName"
                            :href="$routeName::geturl()"
                            tag="a"
                        >
                            {{ $tabName }}
                        </x-filament::tabs.item>
                    @endforeach
                @endforeach
            </x-filament::tabs>
        @endforeach
    </div>
</x-filament-panels::page>
