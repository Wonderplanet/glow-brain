<x-filament-panels::page>
    <form method="POST" wire:submit="send">
        <x-filament::input.wrapper style="display: inline-block">
            <x-filament::input type="number" wire:model="minutesElapsed" inputmode="numeric" pattern="[0-9]*" />
        </x-filament::input.wrapper>
        <x-filament::button type="submit" class="mt-3">分経過</x-filament::button>
        <br />
        <span>※入力した時間分だけ経過した時の報酬量を計算します。計算結果は、報酬詳細内に表示されます。</span>
    </form>

    {{$this->infoList}}
    <x-rewards-table :title="'報酬詳細'" :rows="$this->getRewardTableRows()" />
</x-filament-panels::page>
