@php
    $unitGroups = array_chunk($getState(), 5);
    use App\Filament\Pages\MstUnitDetail;
@endphp
<div>

    <table class="table">
        <tbody>
        @foreach ($unitGroups as $groupIndex => $units)
        <tr>
            @foreach ($units as $index => $unit)
            <td>
                @empty($unit)
                <span class="text-sm">
                    {{ $groupIndex * 5 + $index + 1 }} : キャラクター未設定
                </span>
                @else
                <a href="{{ MstUnitDetail::getUrl(['mstUnitId' => $unit['id']]) }}" class="link">
                    <x-asset-image :assetPath="$unit['assetPath']" :bgPath="$unit['bgPath']" />
                    <span class="text-sm">{{ $groupIndex * 5 + $index + 1 }} : [{{ $unit['id'] }}]{{ $unit['name'] }}</span>
                    <br>
                    <span class="text-sm">レベル：{{ $unit['level'] }}</span>
                    <span class="text-sm ml-4">ランク：{{ $unit['rank'] }}</span>
                    <span class="text-sm ml-4">グレード：{{ $unit['gradeLevel'] }}</span>
                </a>
                @endempty
            </td>
            @endforeach
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
