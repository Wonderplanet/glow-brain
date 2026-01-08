@props([
'title' => '',
'gitBranch' => '',
'hash' => '',
'tableData' => [],
])

@if (is_array($this->getHeaderActions()))
    @php
        $actions = $this->getHeaderActions();
        $checkDiffAction = array_filter(
            $actions,
            fn (\Filament\Pages\Action | \Filament\Pages\ActionGroup $action): bool => $action->getName() === 'checkDiff',
        );
        $operationActions = array_filter(
            $actions,
            fn (\Filament\Pages\Action | \Filament\Pages\ActionGroup $action): bool => $action->getName() === 'import',
        );
        $debugActions = array_filter(
            $actions,
            fn (\Filament\Pages\Action | \Filament\Pages\ActionGroup $action): bool => $action->getName() !== 'import',
        );
    @endphp
@endif
@php
    $header = array_map(fn ($key) =>
        $key === 'id' ?
            new \Illuminate\Support\HtmlString("<input type='checkbox' id='checkAll' x-on:click='toggleCheckboxes'/>") :
            $key,
        array_keys($tableData[0]));
@endphp

@push('scripts')
    <script>
        {{-- Filamentが利用しているAlpine.jsにx-data内のHTML属性をbindさせて各処理を記述 --}}
        function dataBinding() {
            return {
                toggleCheckboxes() {
                    const checked = document.getElementById("checkAll").checked;
                    const checkboxes = document.getElementsByName("id[]");
                    checkboxes.forEach(function (e) {
                        e.checked = checked;
                    });
                },
                submitForm() {
                    let checkedValues = [];
                    document.querySelectorAll('input[type=checkbox]:checked').forEach(checkbox => {
                        checkedValues.push(checkbox.value);
                    });
                    if (checkedValues === []) return;
                    let queryString = checkedValues.map(val => 'id[]=' + encodeURIComponent(val)).join('&');
                    window.location.href = "{{ route(App\Filament\Pages\MasterDataDiff::getRouteName()) }}" + '?' + queryString;
                }
            }
        }
    </script>
@endpush

<x-page-without-action>
    @if (session('flash_message'))
        <x-filament::card>
            {{ session('flash_message') }}
        </x-filament::card>
    @endif
    <x-filament::card title="{{ $title }}">
        <div class="m-4">
            <div>現在のGit操作ブランチ : {{$gitBranch}}</div>
            <div>現在DB適用中のコミットハッシュ : {{$hash}} </div>
        </div>
    </x-filament::card>

    {{-- Filamentが用意しているsubmitのイベントを使わず、自前で実装 --}}
    <form id="action" x-data="dataBinding" x-on:submit.prevent="submitForm()">
        <div class="py-4">
            <x-filament::button type="submit">チェックしたシート＋未取り込みデータの差分を確認する</x-filament::button>
        </div>
        <x-filament-tables::container>
            <x-filament-tables::table>
                <x-slot name="header">
                    @foreach ($header as $column)
                        <x-filament-tables::header-cell>{{$column}}</x-filament-tables::header-cell>
                    @endforeach
                </x-slot>

                <div>
                    @foreach ($tableData as $index => $record)
                        <x-filament-tables::row :recordAction="true">
                            @foreach ($record as $key => $column)
                                @if ($key === 'id')
                                    <x-filament-tables::cell class="px-4 py-1" >
                                        <input type="checkbox" name="id[]" value="{{$column}}"/>
                                    </x-filament-tables::cell>
                                @elseif ($key === 'リンク')
                                    <x-filament-tables::cell class="px-4 py-1" >
                                        <x-filament::link
                                            color="warning"
                                            tag="a"
                                            size="sm"
                                            icon="heroicon-o-arrow-top-right-on-square"
                                            iconPosition="after"
                                            href="{{$column}}"
                                            target="_blank"
                                        >
                                            シートを開く
                                        </x-filament::link>
                                    </x-filament-tables::cell>
                                @else
                                    <x-filament-tables::cell class="px-4 py-1" >
                                        {{ $column }}
                                    </x-filament-tables::cell>
                                @endif
                            @endforeach
                        </x-filament-tables::row>
                    @endforeach
                </div>
            </x-filament-tables::table>
        </x-filament-tables::container>
    </form>
</x-page-without-action>

