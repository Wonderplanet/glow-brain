<div>
    @if($this->features)
        <x-filament::button wire:click='update' class="mt-2">更新</x-filament::button>
        <div class="flex flex-col">
            <table class="table">
                <thead>
                <tr>
                    <th>ページ名</th>
                    <th>権限ON/OFF</th>
                </tr>
                </thead>
                <tbody>
                @foreach($this->features as $feature)
                    <tr>
                        <td>{{$feature}}</td>
                        <td>
                            <input type="checkbox" class="toggle toggle-success" id="{{$feature}}" name="{{$feature}}"
                                   wire:model="permissionFeatures.{{$feature}}"
                            />
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
    <style>
        [type=checkbox]:checked:focus {
            background-color: var(--fallback-su, oklch(var(--su) / var(--tw-bg-opacity)));
        }

        [type=checkbox]:focus {
            box-shadow: var(--handleoffsetcalculator) 0 0 2px var(--tglbg) inset, 0 0 0 2px var(--tglbg) inset, var(--togglehandleborder);
        }
    </style>
</div>

