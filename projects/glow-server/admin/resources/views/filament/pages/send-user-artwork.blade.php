<x-filament-panels::page>
    未所持の原画を付与することができます
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
                @foreach($this->getArtworkList() as $artwork)
                    <tr>
                        <td><input type="checkbox" class="checkbox" wire:model="artworkIds" value="{{$artwork['id']}}"></td>
                        <td>{{$artwork['id']}}</td>
                        <td>{{$artwork['name']}}</td>
                        <td>
                            <x-asset-image :assetPath="$artwork['assetPath']" :bgPath="$artwork['bgPath']" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <x-filament::button type="submit" class="mt-3">選択した原画を付与</x-filament::button>
    </form>
</x-filament-panels::page>
