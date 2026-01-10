@props([
'tableData' => [],
'resetLink' => '',
'rowspanColumn' => '',
'rowspanData' => [],
'diffLink' => '',
])

@push('scripts')
    <script>
        {{-- 更新アイコンと同じものを指定しつつ、 animate-spin を設定--}}
        const loadingIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-white animate-spin"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>`;
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
                    if (checkedValues.length < 1) {
                        alert("取り込むシートが選択させていません。");
                        return;
                    }
                    // 読み込みアニメーション追加
                    const targetElement = document.getElementById('diffCheck');
                    targetElement.innerHTML = loadingIcon + targetElement.innerHTML;
                    let queryString = checkedValues.map(val => 'id[]=' + encodeURIComponent(val)).join('&');
                    window.location.href = "{{$diffLink}}" + '?' + queryString;
                }
            }
        }

        function cacheReset(link) {
            const targetElement = document.getElementById('listUpdate');
            targetElement.innerHTML = loadingIcon;
            window.location.href = link
        }
    </script>
@endpush

<x-filament-panels::page>
    @if (session('flash_message'))
        <x-filament::card>
            {{ session('flash_message') }}
        </x-filament::card>
    @endif

    {{-- Filamentが用意しているsubmitのイベントを使わず、自前で実装 --}}
    <form id="action" x-data="dataBinding" x-on:submit.prevent="submitForm()">
        <div class="flex justify-between mb-4">
            <x-filament::button id="diffCheck" type="submit">差分確認＆インポート実行に進む</x-filament::button>
            <x-filament::button id="listUpdate" onclick="cacheReset('{{$resetLink}}')" color="info" tooltip="シートの更新を一覧に反映します">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </x-filament::button>
        </div>

        <x-filament-tables::container>
            <x-filament-tables::table>
                <x-slot name="header">
                    <x-filament-tables::header-cell><input type='checkbox' id='checkAll' x-on:click='toggleCheckboxes'/></x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>ファイル名</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>メモ</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>シート名</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>リンク</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>最終投入日時</x-filament-tables::header-cell>
                </x-slot>

                <div>
                    @foreach ($tableData as $index => $record)
                        <x-filament-tables::row :recordAction="true">
                            @foreach ($record as $key => $column)
                                @if ($key === 'id')
                                    <x-filament-tables::cell class="px-4 py-1 items-center" >
                                        <input type="checkbox" name="id[]" value="{{$column}}"/>
                                    </x-filament-tables::cell>
                                @elseif ($key === 'fileName')
                                    @if($rowspanColumn !== $column)
                                        <x-filament-tables::cell class="px-4 py-1 border" rowspan="{{$rowspanData[$column]}}">
                                            {{ $column }}
                                        </x-filament-tables::cell>
                                    @endif
                                    @php
                                        $rowspanColumn = $column;
                                    @endphp
                                @elseif ($key === 'link')
                                    <x-filament-tables::cell class="px-4 py-1 border" >
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
                                    <x-filament-tables::cell class="px-4 py-1 border" >
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
</x-filament-panels::page>
