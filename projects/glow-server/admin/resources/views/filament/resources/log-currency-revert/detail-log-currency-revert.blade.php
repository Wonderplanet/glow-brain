<x-filament-panels::page>
    詳細情報
    {{ $this->infoList }}

    <form>
        {{ $this->form }}
        <br />
        {{ $this->cancelButton }}
        {{ $this->submitButton }}
    </form>
</x-filament-panels::page>
