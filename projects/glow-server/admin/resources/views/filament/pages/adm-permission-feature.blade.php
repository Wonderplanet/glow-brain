<x-filament-panels::page>
    <div>
        <select class="select select-bordered" wire:model="permissionId" id="permission" name="permission" wire:change="fetchPermissionFeatures">
            <option value="">権限を選択してください</option>
            @foreach($this->permissions as $permission)
                <option value="{{$permission->id}}">{{$permission->name}}</option>
            @endforeach
        </select>
    </div>
    <div>
        <livewire:select-permission-feature />
    </div>
</x-filament-panels::page>
