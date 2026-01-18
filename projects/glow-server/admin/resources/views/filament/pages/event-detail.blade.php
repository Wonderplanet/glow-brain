@php
    $eventLoginBonusTable = $this->eventLoginBonusTable();
@endphp
<x-filament-panels::page>
    {{$this->infolist()}}
    {{$this->eventQuestTable()}}
    @if($eventLoginBonusTable)
    @foreach($eventLoginBonusTable as $key => $data)
    <x-filament::card>
        <h3>イベントログインボーナス : {{$key}}</h3>
        <h4>対象期間 : {{$data['day']['start_at']}} ~ {{$data['day']['end_at']}}</h4>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>条件とするログイン日数</th>
                <th>報酬情報</th>
            </tr>
            </thead>
            <tbody>
                @foreach($eventLoginBonusTable[$key]['info'] as $value)
                <tr>
                    <td>{{$value['id']}}</td>
                    <td>{{$value['login_day_count']}}</td>
                    <td>
                        @foreach($value['rewardInfo'] as $key=>$rewardInfo)
                            <x-reward-info :rewardInfo="$rewardInfo" />
                            <br />
                        @endforeach
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </x-filament::card>
    @endforeach
    @endif
</x-filament-panels::page>
