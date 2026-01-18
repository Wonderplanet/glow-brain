@php
    $adventBattle = $getState();
    use App\Filament\Pages\MstAdventBattleDetail;
    if ($adventBattle) {
        $url = MstAdventBattleDetail::getUrl(['mstAdventBattleId' => $adventBattle->id]);
    }
@endphp
<div >
    @if ($adventBattle)
        <a href="{{ $url }}" class="link">
            <span class="text-sm">
                [{{ $adventBattle->id }}] {{$adventBattle->mst_advent_battle_i18n->name ?? ''}}
            </span>
        </a>
    @endif
</div>
