<style>
    .table-container {
        height: 250px; /* テーブルの高さを指定 */
        overflow-y: auto; /* 縦スクロールを有効化 */
    }

    thead {
        position: sticky;
        top: 0;
        background-color: #f2f2f2;
    }

    .filament-tables-pagination {
        display: none; /* ページネーション部分を非表示 */
    }
</style>
<div class="table-container">
    <table class='table'>
        <thead>
            <tr>
                <th>日時</th>
                <th>お知らせ件名</th>
                <th>ステータス</th>
                <th>内容</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($this->infoTable() as $infoTable)
                <tr>
                    <td>{{$infoTable['pre_notice_start_at']}}</td>
                    <td>{{$infoTable['title']}}</td>
                    <td>{{$infoTable['enable']}}</td>
                    <td>{{$infoTable['content']}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>

{{-- <div class="table-container">
    {{$this->table}}
</div> --}}
