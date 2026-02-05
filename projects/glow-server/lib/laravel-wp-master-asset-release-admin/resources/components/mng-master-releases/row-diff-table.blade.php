@props([
    'heading' => '',
    'columnHeaders' => [],
    'newData' => [],
    'modifyData' => [],
    'deleteData' => [],
    'addColumns' => [],
    'deleteColumns' => [],
])

@php
    // カラムの追加/削除を識別してヘッダーorセルの背景色を返すコールバック関数
    $getColumnColor = function($column) use ($deleteColumns, $addColumns) {
        $color = '';
        if (in_array($column, $deleteColumns, true)) {
            $color = 'delete-column font-bold';
        } elseif (in_array($column, $addColumns, true)) {
            $color = 'add-column';
        }
        return $color;
    };

    // 対象のカラムがマイグレーションで削除されたカラムか
    $isDeleteColumn = function($column) use ($deleteColumns) {
        return in_array($column, $deleteColumns, true);
    };

    // 対象のカラムがマイグレーションで追加されたカラムか
    $isAddColumn = function($column) use ($addColumns) {
        return in_array($column, $addColumns, true);
    };

    // カラムをもとにテーブルのセル固定cssを当てはめる
    $getStickyColumnClass = function($column) use ($heading) {
        return match($column) {
            'id' => "sticky-col-id-{$heading}",
            'release_key' => "sticky-col-release-key-{$heading}",
            default => '',
        };
    }
@endphp

<p class="font-bold">{{$heading}}</p>
<x-filament-tables::container class="overflow-x-auto monospace-text" style="margin-bottom: 3rem;">
    <x-filament-tables::table>
        <x-slot name="header">
            <x-filament-tables::header-cell class="sticky-col-status-{{$heading}}">
                {{-- ↓の文字列を入れてないと、横長表示の時に左端のセルが潰れてしまう--}}
                {{-- cssで解決することができなかったので、ヘッダーに文字を埋め込みつつ非表示にすることで対応している--}}
                <span class="invisible">ステータス</span>
            </x-filament-tables::header-cell>
            @foreach($columnHeaders as $column)
                <x-filament-tables::header-cell class="px-3.5 {{$getStickyColumnClass($column)}} {{$getColumnColor($column)}}">
                    @if($isDeleteColumn($column))
                        <x-filament::badge class="mx-1" color="danger"><span class="font-bold">削除</span></x-filament::badge>
                        <span style="color: black;">{{$column}}</span>
                    @elseif($isAddColumn($column))
                        <x-filament::badge class="mx-1" color="success"><span class="font-bold">追加</span></x-filament::badge>
                        <span style="color: black;">{{$column}}</span>
                    @else
                        <span>{{$column}}</span>
                    @endif
                </x-filament-tables::header-cell>
            @endforeach
        </x-slot>

        <div class="px-4 py-1">
            @if(!empty($deleteData))
                {{-- 削除データ表示 --}}
                @foreach($deleteData as $deleteRow)
                    <x-filament-tables::row :recordAction="true">
                        <x-filament-tables::cell class="sticky-col-status-{{$heading}}">
                            <x-filament::badge class="mx-1" color="danger"><span class="font-bold">削除</span></x-filament::badge>
                        </x-filament-tables::cell>
                        @foreach($columnHeaders as $column)
                            <x-filament-tables::cell class="{{$getStickyColumnClass($column)}} {{$getColumnColor($column)}} px-3.5">
                                @php
                                    $deleteColumn = $deleteRow[$column] ?? '';
                                @endphp
                                {!! nl2br(e(str_replace('\n', "\n", $deleteColumn))) !!}
                            </x-filament-tables::cell>
                        @endforeach
                    </x-filament-tables::row>
                @endforeach
            @endif
            @if(!empty($newData))
                {{-- 新規データ表示 --}}
                    @foreach($newData as $newRow)
                        <x-filament-tables::row :recordAction="true">
                            <x-filament-tables::cell class="sticky-col-status-{{$heading}}">
                                <x-filament::badge class="mx-1" color="success"><span class="font-bold">新規</span></x-filament::badge>
                            </x-filament-tables::cell>
                            @foreach($columnHeaders as $column)
                                <x-filament-tables::cell class="{{$getStickyColumnClass($column)}} {{$getColumnColor($column)}} px-3.5">
                                    @php
                                        $newColumn = $newRow[$column] ?? '';
                                    @endphp
                                    {!! nl2br(e(str_replace('\n', "\n", $newColumn))) !!}
                                </x-filament-tables::cell>
                            @endforeach
                        </x-filament-tables::row>
                    @endforeach
            @endif
            {{-- 変更データ表示--}}
            @foreach($modifyData as $row)
                @php
                    $beforeRow = $row['beforeRow'];
                    $modifyColumnMap = $row['modifyColumnMap'];
                @endphp
                <x-filament-tables::row :recordAction="true">
                    <x-filament-tables::cell class="sticky-col-status-{{$heading}}">
                        <x-filament::badge class="mx-1" color="primary"><span class="font-bold">変更</span></x-filament::badge>
                    </x-filament-tables::cell>
                    @foreach($columnHeaders as $column)
                        <x-filament-tables::cell class="{{$getStickyColumnClass($column)}} {{$getColumnColor($column)}}">
                            @if($isAddColumn($column))
                                {{-- 追加カラム表示 --}}
                                <span class="px-3.5">
                                    @php
                                        $modifyColumn = $modifyColumnMap[$column] ?? '';
                                    @endphp
                                    {!! nl2br(e(str_replace('\n', "\n", $modifyColumn))) !!}
                                </span>
                            @elseif(isset($modifyColumnMap[$column]))
                                {{-- 変更対象のカラム表示 --}}
                                <del>
                                    <p style="margin: 0 1.8rem 0;">
                                        @php
                                            $beforeColumn = $beforeRow[$column] ?? '';
                                        @endphp
                                        {!! nl2br(e(str_replace('\n', "\n", $beforeColumn))) !!}
                                    </p>
                                </del>
                                <upd>
                                    <p style="margin: 0 1.8rem 0;">
                                        {!! nl2br(e(str_replace('\n', "\n", $modifyColumnMap[$column]))) !!}
                                    </p>
                                </upd>
                            @else
                                {{-- 削除カラム、通常カラム表示 --}}
                                <span class="px-3.5">
                                    @php
                                        $beforeColumn = $beforeRow[$column] ?? '';
                                    @endphp
                                    {!! nl2br(e(str_replace('\n', "\n", $beforeColumn))) !!}
                                </span>
                            @endif
                        </x-filament-tables::cell>
                    @endforeach
                </x-filament-tables::row>
            @endforeach
        </div>
    </x-filament-tables::table>
</x-filament-tables::container>


<style>
    del,
    upd {
        display: block;
        text-decoration: none;
        position: relative;
    }
    del, .delete-column {
        background-color: #fce8e9;
    }
    .add-column {
        background-color: #deeedd;
        font-weight: bold;
    }
    upd {
        background-color: #fffbeb;
        font-weight: bold;
    }
    del::before,
    upd::before {
        position: absolute;
        left: 0.5rem;
        font-family: monospace;
    }
    del::before {
        content: '−';
    }
    upd::before {
        content: '+';
    }

    /**
     * 等倍フォント設定
     */
    .monospace-text {
        font-family:  Menlo, Monaco, 'Courier New', monospace;
    }

    /**
     * テーブルセル固定用
     * 表示テーブル名ごとに生成する必要がある
     */
    .sticky-col-status-{{$heading}} {
        position: sticky;
        left: 0; /* 1列目の幅に合わせる */
        background-color: #fff; /* 固定列の背景色 */
        z-index: 1; /* 他のセルの上に表示 */
    }
    .sticky-col-id-{{$heading}} {
        position: sticky;
        background-color: #fff; /* 固定列の背景色 */
        z-index: 1; /* 他のセルの上に表示 */
    }
    .sticky-col-release-key-{{$heading}} {
        position: sticky;
        background-color: #fff; /* 固定列の背景色 */
        z-index: 1; /* 他のセルの上に表示 */
    }
</style>

@push('scripts')
    <script>
        /**
         * テーブルの横スクロールで、左端の3つ(ステータス、id、release_key)の列を固定させるスクリプト
         * やっていること
         *  1.左端の列から順に、テーブルごとのsticky-col-[*]-[テーブル名]のcssを埋め込んでいる要素を抽出
         *  2.要素中で一番横幅が最大の値を取得
         *  3.列要素の左端の位置を指定(ステータス列は0固定、次の列から前列の最大幅で固定していく)
         *  4.次の幅指定のために最大幅を加算(親要素から見て値で固定するため加算する必要がある)
         */
        document.addEventListener('DOMContentLoaded', () => {
            let cumulativeWidth = 0;

            // 抽出するcss名の配列
            const columns = ['status', 'id', 'release-key'];

            // 各列を処理する関数
            const processStickyColumns = () => {
                cumulativeWidth = 0; // 初期化

                columns.forEach((colClass) => {
                    // 対象の要素を抽出
                    const tableName = "{{$heading}}";
                    const selector = `.sticky-col-${colClass}-${tableName}`;
                    const stickyCols = document.querySelectorAll(selector);
                    let maxWidth = 0;

                    // 最大幅を取得
                    stickyCols.forEach((col) => {
                        maxWidth = Math.max(maxWidth, col.offsetWidth);
                    });

                    // 左位置を設定
                    stickyCols.forEach((col) => {
                        col.style.left = `${cumulativeWidth}px`;
                    });

                    // 最大幅を加算
                    cumulativeWidth += maxWidth;
                })
            };

            // 「差分を見る」ボタン押下時に処理を実行
            const button = document.querySelector('#show-button-{{$heading}}');
            if (button) {
                button.addEventListener('click', () => {
                    const parentComponent = document.querySelector('#table-{{$heading}}');
                    if (parentComponent) {
                        parentComponent.hidden = false; // 取得した親要素のhiddenを解除
                        processStickyColumns(); // 処理を実行
                    }
                });
            }
        });
    </script>
@endpush
