<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div>
        <x-event-info :mstEvent="$getState()" />
    </div>
</x-dynamic-component>
