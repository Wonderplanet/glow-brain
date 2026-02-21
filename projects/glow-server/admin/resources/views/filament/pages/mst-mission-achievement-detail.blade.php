@php
    $mstMissionAchievementDependencyList = $this->mstMissionAchievementDependencyList();
@endphp
<x-filament-panels::page>
    {{$this->infoList}}
    {{$this->criterionList}}
    {{$this->unlockCriterionList}}
    {{$this->destinationSceneList}}
    {{$this->rewardTable()}}
    <x-filament::card>
        <h3>依存関係情報</h3>
        <table class="table">
                <th>グループID</th>
                <th class="tooltip tooltip-top" data-tip="対象グループ内でのミッションの開放順。1つ前のunlock_orderを持つミッションをクリアしたら開放される。">対象グループ内でのミッションの開放順</th>
                <th>リリースキー</th>
            </tr>
            <tbody>
            @foreach($mstMissionAchievementDependencyList as $value)
                <tr>
                    <td>{{$value['group_id']}}</td>
                    <td>{{$value['unlock_order']}}</td>
                    <td>{{$value['release_key']}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </x-filament::card>
</x-filament-panels::page>
