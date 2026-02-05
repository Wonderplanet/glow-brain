<x-filament-panels::page>
    未所持のキャラを付与することができます
    <form method="POST" wire:submit="send">
        <table class="table">
            <thead>
                <tr>
                    <th></th>
                    <th>ID</th>
                    <th>名前</th>
                    <th>画像</th>
                </tr>
            </thead>
            <tbody>
                @foreach($this->getUnitList() as $unit)
                    <tr>
                        <td><input type="checkbox" class="checkbox" wire:model="unit_ids" value="{{$unit['id']}}"></td>
                        <td>{{$unit['id']}}</td>
                        <td>{{$unit['name']}}</td>
                        <td>
                            <x-asset-image :assetPath="$unit['assetPath']" :bgPath="$unit['bgPath']" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <x-filament::button type="submit" class="mt-3">選択したキャラを付与</x-filament::button>
    </form>
</x-filament-panels::page>
