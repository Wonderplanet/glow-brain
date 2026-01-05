@php
    $colors = [
        [
            ['name' => '', 'value' => '#303030'],
            ['name' => '', 'value' => '#165bda'],
            ['name' => '', 'value' => '#07ae7d'],
            ['name' => '', 'value' => '#ee3632'],
        ],
    ];
@endphp

<style>
    .color-picker {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .color-group {
        display: flex;
        gap: 0.5rem;
    }

    .color-button {
        width: 1.5rem;
        height: 1.5rem;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        border: none;
    }

</style>

<x-filament-tiptap-editor::dropdown-button
    label="文字色選択"
    active="color"
    icon="color"
    :list="false"
>
    <div
        x-data="{
            state: editor().getAttributes('textStyle').color || '#303030',

            init: function () {
                if (!(this.state === null || this.state === '')) {
                    this.setState(this.state)
                }

                this.$watch('state', (value) => {
                    if (! value.startsWith('#')) {
                        this.state = `#${value}`
                    }
                })
            },

            setState: function (value) {
                this.state = value
            }
        }"
        x-on:keydown.esc="isOpen() && $event.stopPropagation()"
        class="relative flex-1 p-1"
    >
        <div class="color-picker">

            <div class="flex gap-2 items-center">

                <x-filament::input.wrapper
                    class="mt-2"
                    style="width: 7.5rem;"
                >
                    <x-filament::input
                        type="text"
                        x-model="state"
                        size="sm"
                        class="items-center"
                    />
                </x-filament::input.wrapper>

                <x-filament::button
                    x-on:click="
                        editor().chain().focus().setColor(state).run();
                        $dispatch('close-panel');
                    "
                    size="sm"
                    class="color-button"
                    x-bind:style="{ 'background-color': state }"
                ></x-filament::button>

            </div>

            @foreach ($colors as $colorGroup)
                <div class="color-group mt-2">
                    @foreach ($colorGroup as $color)
                        <x-filament::button
                            x-bind:color="state"
                            x-on:click="editor().chain().focus().setColor('{{ $color['value'] }}').run(); $dispatch('close-panel')"
                            size="sm"
                            class="color-button"
                            style="background-color: {{ $color['value'] }}"
                        >{{ $color['name'] }}</x-filament::button>
                    @endforeach
                </div>
            @endforeach
        </div>

        <div class="w-full flex gap-2 mt-2">
            <x-filament::button
                x-on:click="editor().chain().focus().unsetColor().run(); $dispatch('close-panel')"
                size="sm"
                class="flex-1"
                style="background-color: gray"
            >
                文字色解除
            </x-filament::button>
        </div>
    </div>
</x-filament-tiptap-editor::dropdown-button>
