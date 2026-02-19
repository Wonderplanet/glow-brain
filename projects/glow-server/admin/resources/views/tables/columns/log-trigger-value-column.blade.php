@php
    $logTrigger = $getState();
@endphp
@if($logTrigger)
    <div>
        @if ($logTrigger->getLink())
            <a href="{{ $logTrigger->getLink() }}" class="link py-6 text-sm">
                {{ $logTrigger->getName() }}
            </a>
        @else
            {{ $logTrigger->getName() }}
        @endif
    </div>
@endif
