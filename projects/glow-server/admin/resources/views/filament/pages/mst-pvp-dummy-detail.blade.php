@php
use App\Filament\Pages\MstOutpostDetail;
use App\Filament\Pages\MstUnitDetail;

$mstOutpostDetailUrl = MstOutpostDetail::getUrl();

$dummyOutposts = $this->dummyOutposts();
$dummyUserUnits = $this->dummyUserUnits();
@endphp
<x-filament-panels::page>
    {{ $this->infoList() }}
    <x-filament::card>
        <h3>編成キャラ情報</h3>
        <table class="table">
            <tr>
                <th>キャラID</th>
                <th>キャラ名称</th>
                <th>キャラ画像</th>
                <th>レベル</th>
                <th>ランク</th>
                <th>グレードレベル</th>
            </tr>
            <tbody>
            @foreach($dummyUserUnits as $dummyUserUnit)
            <tr>
                <td>
                    <a href="{{ MstUnitDetail::getUrl(['mstUnitId' => $dummyUserUnit['mst_unit_id']]) }}" class="link">
                        {{ $dummyUserUnit['mst_unit_id'] }}
                    </a>
                </td>
                <td>{{ $dummyUserUnit['name'] }}</td>
                <td><x-asset-image :assetPath="$dummyUserUnit['asset_path']" :bgPath="$dummyUserUnit['bg_path']" /></td>
                <td>{{ $dummyUserUnit['level'] }}</td>
                <td>{{ $dummyUserUnit['rank'] }}</td>
                <td>{{ $dummyUserUnit['grade_level'] }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </x-filament::card>
    <x-filament::card>
        <h3>ゲート情報</h3>
        <table class="table">
            <tr>
                <th>ID</th>
                <th>ゲート強化名</th>
                <th>レベル</th>
                <th>強化詳細</th>
            </tr>
            <tbody>
                @foreach($dummyOutposts as $dummyOutpost)
                    <tr>
                        <td>
                            <a href="{{ $mstOutpostDetailUrl }}" class="link">
                                {{ $dummyOutpost['id'] }}
                            </a>
                        </td>
                        <td>{{ $dummyOutpost['name'] }}</td>
                        <td>{{ $dummyOutpost['level'] }}</td>
                        <td>{{ $dummyOutpost['description'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-filament::card>
</x-filament-panels::page>
