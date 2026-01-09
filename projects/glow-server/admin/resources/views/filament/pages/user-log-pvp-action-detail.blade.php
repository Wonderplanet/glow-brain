<x-filament-panels::page>
    {{$this->infoList}}
    <x-pvp-party-unit-list :units="$this->userParty()" title="プレイヤー編成キャラ情報" />
    <x-pvp-artwork-list :artworks="$this->userArtworks()" title="プレイヤー所持原画情報" />
    <x-pvp-outpost-list :outposts="$this->userOutposts()" title="プレイヤーゲート情報" />
    <x-pvp-encyclopedia-effect-list :effects="$this->userEncyclopediaEffects()" title="プレイヤー図鑑効果情報" />

    <x-pvp-party-unit-list :units="$this->opponentParty()" title="対戦相手編成キャラ情報" />
    <x-pvp-artwork-list :artworks="$this->opponentArtworks()" title="対戦相手所持原画情報" />
    <x-pvp-outpost-list :outposts="$this->opponentOutposts()" title="対戦相手ゲート情報" />
    <x-pvp-encyclopedia-effect-list :effects="$this->opponentEncyclopediaEffects()" title="対戦相手図鑑効果情報" />
</x-filament-panels::page>
