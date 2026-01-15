<div>
    @foreach($confirmDetails as $index => $detail)
        <div class="border" style="margin-bottom: 3rem; border-width: 2px;">
            <p class="font-bold" style="margin-bottom: 0.5rem;">
                @if($detail['isEnabled'])
                    <span style="color:deeppink">配信中</span>
                @else
                    <span style="color:darkolivegreen">準備中</span>
                @endif
                {{$detail['title']}}
            </p>
            <p class="flex" style="margin-bottom: 0.5rem;">
                <x-filament::badge color="success">新規:{{$detail['newCount']}}件</x-filament::badge>
                <x-filament::badge color="danger" style="margin-left: 0.5rem;">削除:{{$detail['deleteCount']}}件</x-filament::badge>
                <x-filament::badge color="primary" style="margin-left: 0.5rem;">変更:{{$detail['modifyCount']}}件</x-filament::badge>
            </p>
            <x-filament-tables::table style="margin-bottom: 0.5rem;">
                <x-slot name="header">
                    <x-filament-tables::header-cell></x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>変更前</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell></x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>変更後</x-filament-tables::header-cell>
                </x-slot>
                <div class="px-4 py-1 items-center">
                    <x-filament-tables::row :recordAction="true">
                        <x-filament-tables::cell>Gitリビジョン</x-filament-tables::cell>
                        <x-filament-tables::cell>{{$detail['beforeGitRevision']}}</x-filament-tables::cell>
                        <x-filament-tables::cell>-></x-filament-tables::cell>
                        <x-filament-tables::cell>
                            <span @if($detail['beforeGitRevision'] !== $detail['afterGitRevision']) style="color: deeppink" @endif>
                                {{$detail['afterGitRevision']}}
                            </span>
                        </x-filament-tables::cell>
                    </x-filament-tables::row>
                </div>
                <div class="px-4 py-1 items-center">
                    <x-filament-tables::row :recordAction="true">
                        <x-filament-tables::cell>DataHash</x-filament-tables::cell>
                        <x-filament-tables::cell>{{$detail['beforeDataHash']}}</x-filament-tables::cell>
                        <x-filament-tables::cell>-></x-filament-tables::cell>
                        <x-filament-tables::cell>
                            <span @if($detail['beforeDataHash'] !== $detail['afterDataHash']) style="color: deeppink" @endif>
                                {{$detail['afterDataHash']}}
                            </span>
                        </x-filament-tables::cell>
                    </x-filament-tables::row>
                </div>
            </x-filament-tables::table>
            <br />
            @if($detail['isEnabled'])
                <x-filament::badge color="danger" icon="heroicon-c-exclamation-triangle" iconSize="lg" class="font-bold">
                    新規バージョンが即時配信されます
                </x-filament::badge>
            @endif
        </div>
    @endforeach
</div>
