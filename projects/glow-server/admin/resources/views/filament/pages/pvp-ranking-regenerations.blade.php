<x-filament-panels::page>
    <div>
        <x-filament::breadcrumbs :breadcrumbs="$this->breadcrumbList" />
    </div>
    <x-filament::card>
        <h3>ランクマッチシーズンID : {{ $this->sysPvpSeasonId }}</h3>
        <br />
        {{ $this->form }}
    </x-filament::card>

    @if($rankingData)
        <x-filament::card>
            <h3>ランクマッチランキング</h3>
            <table class="table">
                <tr>
                    <th>ランク</th>
                    <th>プレイヤーID</th>
                    <th>名前</th>
                    <th>スコア</th>
                </tr>
                <tbody>
                @foreach($rankingData as $value)
                    <tr>
                        <td>{{$value['rank']}}</td>
                        <td>{{$value['usr_user_id']}}</td>
                        <td>{{$value['name']}}</td>
                        <td>{{$value['score']}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </x-filament::card>
    @endif

</x-filament-panels::page>
