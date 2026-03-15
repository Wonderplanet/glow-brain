@props([
'releaseDiffData' => [],
'entities' => [],
'applyReleaseKeyStr' => '',
'isFirstImport' => false,
'isActiveExecButton' => true,
])

<x-filament-panels::page>
    @foreach ($releaseDiffData as $row)
        <x-filament-tables::container class="overflow-x-auto">
            <span class="font-bold" style="margin-bottom: 0.5rem; margin-left: 0.5rem;">
                <span style="{{$row['statusColor']}}">{{$row['status']}}</span> {{$row['releaseKey']}}:{{$row['description']}}
            </span>
            <x-filament-tables::table style="margin-bottom: 0.5rem;">
                <x-slot name="header">
                    <x-filament-tables::header-cell style="width:15rem"></x-filament-tables::header-cell>
                    <x-filament-tables::header-cell style="width:25rem">変更前</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell class="w-auto"></x-filament-tables::header-cell>
                    <x-filament-tables::header-cell class="w-auto">変更後</x-filament-tables::header-cell>
                </x-slot>
                <x-filament-tables::row :recordAction="true">
                    <x-filament-tables::cell>Gitリビジョン</x-filament-tables::cell>
                    <x-filament-tables::cell>{{$row['oldGitRevision']}}</x-filament-tables::cell>
                    <x-filament-tables::cell>→</x-filament-tables::cell>
                    <x-filament-tables::cell>
                        <span @if($row['oldGitRevision'] !== $row['newGitRevision']) style="color: deeppink" @endif>{{$row['newGitRevision']}}</span>
                    </x-filament-tables::cell>
                </x-filament-tables::row>
                <x-filament-tables::row :recordAction="true">
                    <x-filament-tables::cell>DataHash</x-filament-tables::cell>
                    <x-filament-tables::cell>{{$row['oldDataHash']}}</x-filament-tables::cell>
                    <x-filament-tables::cell>→</x-filament-tables::cell>
                    <x-filament-tables::cell>
                        <span @if($row['oldDataHash'] !== $row['newDataHash']) style="color: deeppink" @endif>{{$row['newDataHash']}}</span>
                    </x-filament-tables::cell>
                </x-filament-tables::row>
            </x-filament-tables::table>
        </x-filament-tables::container>
    @endforeach
    <div>
        @if(count($entities) === 0)
            {{-- 差分表示対応時に用調整 --}}
            <x-filament::card>
                @if($isFirstImport)
                    <div class="text-danger-600 font-bold">
                        <p>初回インポートのため、差分確認はできません</p>
                        <p>対象環境の最新データ(gitRevision)をすべてインポートします</p>
                    </div>
                @else
                    <div class="m-4">
                        <div>差分がありません</div>
                    </div>
                @endif
            </x-filament::card>
        @else
            <p class="font-bold">差分一覧</p>
            <x-filament-tables::container class="overflow-x-auto">
                <x-filament-tables::table>
                    <x-slot name="header">
                        <x-filament-tables::header-cell>テーブル名</x-filament-tables::header-cell>
                        <x-filament-tables::header-cell>
                            <x-filament::badge color="success">新規</x-filament::badge>
                        </x-filament-tables::header-cell>
                        <x-filament-tables::header-cell>
                            <x-filament::badge color="danger">削除</x-filament::badge>
                        </x-filament-tables::header-cell>
                        <x-filament-tables::header-cell>
                            <x-filament::badge color="primary">変更</x-filament::badge>
                        </x-filament-tables::header-cell>
                        <x-filament-tables::header-cell></x-filament-tables::header-cell>
                    </x-slot>
                    @foreach ($entities as $entity)
                        <x-filament-tables::row :recordAction="true">
                            <x-filament-tables::cell class="py-4">
                                {{$entity['sheetName']}}
                                @if (!empty($entity['structureDiffAddData']) || !empty($entity['structureDiffDeleteData']))
                                    <x-filament::modal width="7xl">
                                        <x-slot name="trigger">
                                            <x-filament::button
                                                size="xs" color="danger"
                                                icon="heroicon-c-exclamation-triangle">
                                                構造差分があります
                                            </x-filament::button>
                                        </x-slot>
                                        <x-slot name="heading">
                                            構造差分詳細は未実装
                                        </x-slot>
                                        <x-slot name="description">
                                            構造差分詳細は未実装
                                        </x-slot>
                                    </x-filament::modal>
                                @endif
                            </x-filament-tables::cell>
                            <x-filament-tables::header-cell>
                                @if (!empty($entity['newData']))
                                    <x-filament::badge class="mx-1" color="success">
                                        {{count($entity['newData'])}}
                                    </x-filament::badge>
                                @endif
                            </x-filament-tables::header-cell>
                            <x-filament-tables::header-cell>
                                @if (!empty($entity['deleteData']))
                                    <x-filament::badge
                                        class="mx-1" color="danger"
                                        icon="heroicon-c-exclamation-triangle"
                                    >
                                        {{count($entity['deleteData'])}}
                                    </x-filament::badge>
                                @endif
                            </x-filament-tables::header-cell>
                            <x-filament-tables::header-cell>
                                @if (!empty($entity['modifyData']))
                                    <x-filament::badge class="mx-1" color="primary">
                                        {{count($entity['modifyData'])}}
                                    </x-filament::badge>
                                @endif
                            </x-filament-tables::header-cell>
                            <x-filament-tables::cell>
                                @php
                                    $hasDifferent = !empty($entity['modifyData'])
                                        || !empty($entity['deleteData'])
                                        || !empty($entity['newData'])
                                        || !empty($entity['structureDiffAddData'])
                                        || !empty($entity['structureDiffDeleteData']);
                                @endphp
                                @if($hasDifferent)
                                    <x-filament::button
                                        id="show-button-{{$entity['sheetName']}}"
                                        color="info" badge-color="success" size="xs"
                                        icon="heroicon-c-arrows-right-left"
                                        data-target="{{$entity['sheetName']}}"
                                        onclick="showAndScroll(this)"
                                    >
                                        差分を見る
                                    </x-filament::button>
                                @else
                                    <x-filament::button
                                        color="gray" size="xs" icon="heroicon-c-arrows-right-left" disabled>
                                        差分なし
                                    </x-filament::button>
                                @endif
                            </x-filament-tables::cell>
                        </x-filament-tables::row>
                    @endforeach
                </x-filament-tables::table>
            </x-filament-tables::container>
        @endif
    </div>

    <div>
        @if(!empty($entities))
            <p>「差分を見る」ボタンを押すと、差分内容を表示します</p>
            <br />
        @endif
        @foreach ($entities as $entity)
            {{-- 追加、変更、削除行が何もなければスキップする--}}
            @continue(empty($entity['newData']) && empty($entity['modifyData']) && empty($entity['deleteData']))
            <div
                id="table-{{$entity['sheetName']}}"
                hidden
            >
                {{view('view-master-asset-admin::components.mng-master-releases.row-diff-table',
                    [
                        'heading' => $entity['sheetName'],
                        'columnHeaders' => $entity['header'],
                        'newData' => $entity['newData'],
                        'modifyData' => $entity['modifyData'],
                        'deleteData' => $entity['deleteData'],
                        'addColumns' => $entity['structureDiffAddData'],
                        'deleteColumns' => $entity['structureDiffDeleteData'],
                    ]
                )}}
            </div>
        @endforeach
    </div>

    <p>
        ※現在適用中のリリースキーに関して即時配信ターゲットに設定されます
        <br />
    </p>

    @if($isActiveExecButton)
        {{-- 確認モーダル処理 --}}
        {{-- MEMO サーバー側でボタンとモーダルを実装すると、モーダルを開くたびにサーバーと通信が発生し --}}
        {{--  大量データの差分があると描画遅延が発生するため、view側でモーダルを実装して無駄な通信をさせないようにしている --}}
        <x-filament::modal
            alignment="center"
            icon="heroicon-o-exclamation-triangle"
            icon-color="danger"
            width="7xl"
            footerActionsAlignment="center"
        >
            <x-slot name="trigger" class="md:w-max">
                <x-filament::button>
                    インポート実行確認
                </x-filament::button>
            </x-slot>

            <x-slot name="heading">
                必ず内容を確認してください
            </x-slot>

            {{view('view-master-asset-admin::components.mng-master-releases.diff-import-confirm-modal',
                [
                    'confirmDetails' => $confirmDetails,
                ]
            )}}

            <x-slot name="footerActions">
                <x-filament::button color="gray" x-on:click="close()">キャンセル</x-filament::button>
                <x-filament::button wire:click="import()">確定</x-filament::button>
            </x-slot>
        </x-filament::modal>
    @else
        {{-- MEMO 差分がない場合はボタンを非活性にしたかったが、モーダルのボタンをdisabledにするだけでは --}}
        {{--  非活性にできなかったので、フラグを使って差分がなければ非活性ボタンを表示するようにしている --}}
        <x-filament::button class="md:w-max" disabled>
            インポート実行確認
        </x-filament::button>
    @endif
</x-filament-panels::page>

@push('scripts')
    <script>
        /**
         * 「差分を表示」ボタン押下で対象マスターの差分を表示＆対象テーブルまでスクロールする
         *
         * @param button
         */
        function showAndScroll(button) {
            setTimeout(() => {
                const targetId = button.getAttribute('data-target'); // ボタンからターゲットIDを取得
                const targetElmId = `table-${targetId}`;

                // 全テーブルを非表示
                document.querySelectorAll('[id^="table-"]').forEach((el) => {
                    el.setAttribute('hidden', '');
                });
                // 対象のテーブルを表示
                const targetElement = document.getElementById(targetElmId);
                if (targetElement) {
                    targetElement.removeAttribute('hidden');

                    // 対象テーブルまでスクロール実行
                    const offset = 100; // 必要に応じて調整
                    window.scrollTo({
                        top: targetElement.offsetTop - offset, // ターゲット位置 - オフセット
                        behavior: 'smooth',
                    });
                }
            });
        }
    </script>
@endpush
