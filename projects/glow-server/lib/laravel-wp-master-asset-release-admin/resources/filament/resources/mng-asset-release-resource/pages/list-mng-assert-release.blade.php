<div style="margin-left: 0.5rem; margin-bottom: 0.5rem; margin-top: 0.5rem;">
    <p>アセットデータがない場合はJenkinsを起動して作成してください</p>
    <x-filament::link
        style="text-decoration: underline;"
        href="{{ $this->iosJenkinsUrl }}"
        target="_blank"
    >
        iOS
    </x-filament::link>
    /
    <x-filament::link
        style="text-decoration: underline;"
        href="{{ $this->androidJenkinsUrl }}"
        target="_blank"
    >
        Android
    </x-filament::link>
</div>
