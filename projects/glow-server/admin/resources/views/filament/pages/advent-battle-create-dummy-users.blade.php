<x-filament-panels::page>
    <div>
        <x-filament::breadcrumbs :breadcrumbs="$this->breadcrumbList" />
    </div>
    <x-filament::card>
        <h3>降臨バトルダミーユーザー生成</h3>
        <br />
        {{ $this->form }}
    </x-filament::card>

    @if($rankingData)
        <x-filament::card>
            <h3>降臨バトルランキング</h3>
            <table class="table">
                <tr>
                    <th>ランク</th>
                    <th>名前</th>
                    <th>スコア</th>
                    <th>パーティ情報</th>
                </tr>
                <tbody>
                @foreach($rankingData as $value)
                    <tr>
                        <td>{{$value['rank']}}</td>
                        <td>{{$value['name']}}</td>
                        <td>{{$value['score']}}</td>
                        <td>@livewire('usr-party-unit-info', ['partyUnitInfo' => $value['party']], key(''))</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </x-filament::card>
    @endif

</x-filament-panels::page>
