@php
    $tabGroups = $this->getTabGroups();
    $currentTab = $this->getCurrentTab();
@endphp

<div>
    @foreach($tabGroups as $tabGroup)
        <x-filament::tabs :contained="true">
            @foreach($tabGroup as $key => $value)
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
