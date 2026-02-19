<x-filament-widgets::widget>
    <x-filament::section>
        @if(empty($result))
            <p class="font-bold text-danger-600">配信中のリリースデータが設定されていません。</p>
            <p class="font-bold text-danger-600">マスタデータ配信管理から配信設定を実施するか、環境構築ドキュメントに従ってコマンドを実施してください</p>
            <br />
            <x-filament::link style="text-decoration: underline;" :href="route('filament.admin.resources.mng-master-releases.index')">
                マスターデータ配信管理
            </x-filament::link>
            <br />
            <x-filament::link
                style="text-decoration: underline;"
                href="https://github.com/Wonderplanet/laravel-wp-framework/tree/develop?tab=readme-ov-file#%E7%AE%A1%E7%90%86%E3%83%84%E3%83%BC%E3%83%AB%E3%81%8B%E3%82%89%E3%83%AA%E3%83%AA%E3%83%BC%E3%82%B9%E3%82%92%E8%A1%8C%E3%81%86"
                target="_blank"
            >
                環境構築ドキュメント
            </x-filament::link>
        @else
            <p>
                配信中のリリースキー : <span class="font-bold text-primary-600">{{$result['releaseKey']}}</span>
            </p>
            <p>
                配信中のdata hash : <span class="font-bold text-primary-600">{{$result['dataHash']}}</span>
            </p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
