@props([
'header' => [],
'entities' => [],
'actions' => [],
])
@if (is_array($this->getHeaderActions()))
    @php
        $actions = $this->getHeaderActions();
    @endphp
@endif
<x-page-without-action>
    <div class="flex justify-end">
        <x-filament-actions::actions :actions="$actions" class="shrink-0" />
    </div>
    @if(count($entities) === 0)
        <x-filament::card>
            <div class="m-4">
                <div>差分はありません</div>
            </div>
        </x-filament::card>
    @endif
    @foreach ($entities as $entity)
        <x-filament-tables::container>
            <div class="p-2 text-xl">
                {{ $entity->getSheetName()}}
            </div>
            <x-filament-tables::table>
                <x-slot name="header">
                    @foreach ($header as $column)
                        <x-filament-tables::header-cell class="text-left">{{$column}}</x-tables::header-cell>
                    @endforeach
                </x-slot>

                <div>
                    <x-filament-tables::row :recordAction="true">
                        <x-filament-tables::cell class="text-right pr-2 w-8 py-1" rowspan="3">
                            {{$entity->getLine() == -1 ? '' : $entity->getLine()}}
                            @if($entity->getCount() > 1)
                                <br />~ {{$entity->getCount()+$entity->getLine()-1}}
                            @endif
                        </x-filament-tables::cell>

                        <x-filament-tables::cell class="px-4 py-1" >
                            {{new \Illuminate\Support\HtmlString(implode('<br />',$entity->getOldData()))}}
                        </x-filament-tables::cell>
                    </x-filament-tables::row>
                    <x-filament-tables::row :recordAction="true">
                        <x-filament-tables::cell class="px-4 py-1" >
                            {{new \Illuminate\Support\HtmlString(implode('<br />',$entity->getNewData()))}}
                        </x-filament-tables::cell>
                    </x-filament-tables::row>
                </div>
            </x-filament-tables::table>
        </x-filament-tables::container>
    @endforeach
</x-page-without-action>
