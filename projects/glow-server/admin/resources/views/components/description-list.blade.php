@props([
'title' => '',
'list' => [],
])

<x-filament::card>
    @unless (blank($title))
        <div class="mb-4">
            <h3 class="font-bold">{{ $title }}</h3>
        </div>
    @endif
    <div class="mt-6 border-t border-gray-100">
        <dl class="divide-y divide-gray-100">
            @foreach($list as $name => $value)
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">{{$name}}</dt>
                    @if (strpos($value,'.png'))
                        <x-asset-banner-image assetPath="{{$value}}" />
                    @else
                        <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                            {!! nl2br(e($value)) !!}
                        </dd>
                    @endif
                </div>
            @endforeach
        </dl>
    </div>
</x-filament::card>
