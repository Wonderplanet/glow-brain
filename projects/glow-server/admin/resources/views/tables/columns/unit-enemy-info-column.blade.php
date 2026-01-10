@php
    $unitEnemys = $getState();

@endphp
<div>
    @if ($unitEnemys)
        @foreach ($unitEnemys as $unitEnemy)
            [{{ $unitEnemy['id'] }}] {{ $unitEnemy['name'] }} x {{$unitEnemy['count']}}
            <x-asset-image :assetPath="$unitEnemy['assetPath']" :bgPath="$unitEnemy['bgPath']" />
            <br />
        @endforeach
    @endif
</div>
