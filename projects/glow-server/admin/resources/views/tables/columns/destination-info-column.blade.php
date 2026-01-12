@php
    $record = $getRecord();

    $type = $record->destination_type;
    $path = $record->destination_path;
    $pathDetail = $record->destination_path_detail;
    $buttonTitle = $record?->mng_in_game_notice_i18n?->button_title;

    use App\Constants\DestinationType;
@endphp
<div class="text-sm leading-6 px-3 py-4">
    @switch($type)
        @case(DestinationType::IN_GAME->value)
            <span>
                {{ $buttonTitle }}
                <br>
                {{ DestinationType::IN_GAME->label() }}
                <br>
                &nbsp;&nbsp;&nbsp;&nbsp;{{ $showInGamePath() }}
                @if ($existPathDetail())
                    <br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <a href="{{ $getInGamePathDetailLink() }}" class="link">
                        <span class="text-sm">
                            {{ $showInGamePathDetail() }}
                        </span>
                    </a>
                @endif
            </span>
            @break
        @case(DestinationType::WEB->value)
            <span>
                {{ $buttonTitle }}
                <br>
                {{ DestinationType::WEB->label() }} /
                <br>
                <a href="{{ $path }}" class="link">
                    <span class="text-sm">
                        {{ $path }}
                    </span>
                </a>
            </span>
            @break
        @default
            {{-- 遷移先なし --}}
            <span class="text-sm">
                なし
            </span>

    @endswitch
</div>
