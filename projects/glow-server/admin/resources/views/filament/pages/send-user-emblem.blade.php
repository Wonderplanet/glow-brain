<x-filament-panels::page>
    未所持のエンブレムを付与することができます
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
                @foreach($this->getEmblemList() as $emblem)
                    <tr>
                        <td><input type="checkbox" class="checkbox" wire:model="emblemIds" value="{{$emblem['id']}}"></td>
                        <td>{{$emblem['id']}}</td>
                        <td>{{$emblem['name']}}</td>
                        <td>
                            <x-asset-image :assetPath="$emblem['assetPath']" :bgPath="$emblem['bgPath']" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <x-filament::button type="submit" class="mt-3">選択したエンブレムを付与</x-filament::button>
    </form>
</x-filament-panels::page>
