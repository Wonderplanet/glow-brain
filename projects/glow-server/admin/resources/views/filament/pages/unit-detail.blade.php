@php
    $levelUpStarusesList    = $this->getLevelUpStarusesList();
    $rankUpStarusesList     = $this->getRankUpStatusesList();
    $gradeUpStatusesList    = $this->getGradeUpStatusesList();
@endphp
<x-filament-panels::page>
    {{$this->infolist}}
    {{$this->battleStatusList}}
    <x-filament::card>
        <h3>レベルステータス情報</h3>
        <table class="table">
            <thead>
                <th>レベル</th>
                <th>必要コイン</th>
                <th>HP</th>
                <th>攻撃力</th>
            </tr>
            </thead>
            <tbody>
            @foreach($levelUpStarusesList as $value)
                <tr>
                    <td>{{ $value['level']}}</td>
                    <td>{{ $value['required_coin']}}</td>
                    <td>{{ $value['level_up_hp']}}</td>
                    <td>{{ $value['level_up_attack_power']}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </x-filament::card>
    <x-filament::card>
        <h3>ランクアップステータス情報</h3>
        <table class="table">
            <thead>
                <th>ランク</th>
                <th>必要なリミテッドメモリー数</th>
                <th>必要レベル</th>
                <th>HP</th>
                <th>攻撃力</th>
            </tr>
            </thead>
            <tbody>
            @foreach($rankUpStarusesList as $value)
                <tr>
                    <td>{{ $value['rank']}}</td>
                    <td>{{ $value['amount']}}</td>
                    <td>{{ $value['require_level']}}</td>
                    <td>{{ $value['rank_up_hp']}}</td>
                    <td>{{ $value['rank_up_attack_power']}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </x-filament::card>
    <x-filament::card>
        <h3>グレードアップステータス情報</h3>
        <table class="table">
            <thead>
                <th>グレードレベル</th>
                <th>必要なかけら数</th>
                <th>HP</th>
                <th>攻撃力</th>
            </tr>
            </thead>
            <tbody>
            @foreach($gradeUpStatusesList as $value)
                <tr>
                    <td>{{ $value['grade_level']}}</td>
                    <td>{{ $value['require_amount']}}</td>
                    <td>{{ $value['grade_up_hp']}}</td>
                    <td>{{ $value['grade_up_attack_power']}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </x-filament::card>
</x-filament-panels::page>
