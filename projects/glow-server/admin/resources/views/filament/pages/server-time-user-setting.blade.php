<x-filament-panels::page>
    @if (env('APP_ENV', '') === 'prod')
        <p>本番環境は使用できません。</p>
    @else
        <x-filament::card>
            {{ $this->infolist }}
        </x-filament::card>

        <form wire:submit="setTimeSetting">
            サーバー時間変更
            {{ $this->form }}
            <div class="mt-6">
                <x-submit-button>変更</x-submit-button>
            </div>
            <div wire:loading>
                <x-filament::loading-indicator class="h-5 w-5" />
            </div>
        </form>

        <form wire:submit="resetTimeSetting">
            サーバー時間リセット
            <div class="mt-6">
                <x-submit-button>リセット</x-submit-button>
            </div>
            <div wire:loading>
                <x-filament::loading-indicator class="h-5 w-5" />
            </div>
        </form>
    @endif
</x-filament-panels::page>
