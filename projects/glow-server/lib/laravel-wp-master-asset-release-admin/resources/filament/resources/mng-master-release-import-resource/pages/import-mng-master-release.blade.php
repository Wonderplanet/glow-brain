<x-filament::page>
    <x-filament-panels::form
        id="form"
        :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()"
        wire:submit="create"
    >
        {{ $this->form }}

        @if(!empty($responseErrors))
            <div class="text-danger-600">
                <p>{{$this->fromEnvironment}}環境の情報取得に失敗しました。管理者にお問い合わせください</p>
                <p>エラー詳細:{status:{{$responseErrors['status']}}, message:{{$responseErrors['error']}}}</p>
            </div>
        @elseif(is_null($this->fromEnvironment))
            <p>インポート元環境選択後、対象が表示されます</p>
        @else
            @if(empty($diffData))
                <p>{{$this->fromEnvironment}} 環境のリリース情報が確認できません</p>
            @else
                <p>{{$this->fromEnvironment}} 環境のマスターリリース情報を取り込みます</p>
                <x-filament-tables::container class="overflow-x-auto">
                    <x-filament-tables::table>
                        <x-slot name="header">
                            <x-filament-tables::header-cell class="text-left">Release Key</x-filament-tables::header-cell>
                            <x-filament-tables::header-cell class="text-left">この環境のリリースキー設定</x-filament-tables::header-cell>
                            <x-filament-tables::header-cell class="text-left">{{$this->fromEnvironment}}のリリースキー設定</x-filament-tables::header-cell>
                        </x-slot>
                        @foreach($diffData as $row)
                            <x-filament-tables::row :recordAction="true">
                                @php
                                    $self = $row['self'];
                                    $environment = $row['environment'];
                                @endphp
                                <x-filament-tables::cell class="px-4 py-1">
                                    <p>{{$self['release_key']}}</p>
                                </x-filament-tables::cell>
                                <x-filament-tables::cell class="px-4 py-1">
                                    <p>メモ欄：{{$self['description']}}</p>
                                    <p style="{{$self['style']}}">Status：{{$self['status']}}</p>
                                    <p>Git Revision：{{$self['git_revision']}}</p>
                                </x-filament-tables::cell>
                                <x-filament-tables::cell class="px-4 py-1">
                                    @if(empty($environment))
                                        <p class="text-danger-600">一致するリリースキーの情報がありません</p>
                                    @else
                                        @if(!$environment['is_latest_version'])
                                            <p class="text-danger-600">最新バージョンがセットされていません</p>
                                        @endif
                                        <p>メモ欄：{{$environment['description']}}</p>
                                        <p style="{{$environment['style']}}">Status：{{$environment['status']}}</p>
                                        <p>Git Revision：{{$environment['git_revision']}}</p>
                                    @endif
                                </x-filament-tables::cell>
                            </x-filament-tables::row>
                        @endforeach
                    </x-filament-tables::table>
                </x-filament-tables::container>
            @endif
        @endif

        @if($hasNoLatestReleaseVersion)
            <p class="text-danger-600">最新バージョンがセットされていないリリースーキーが存在するため、インポートが実行できません</p>
        @elseif($validationErrorMessage !== '')
            <p class="text-danger-600">{{$validationErrorMessage}}</p>
        @endif

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>
</x-filament::page>
