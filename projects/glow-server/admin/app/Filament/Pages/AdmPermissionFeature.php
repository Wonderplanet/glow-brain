<?php

namespace App\Filament\Pages;

use App\Constants\NavigationGroups;
use App\Models\Adm\AdmPermission;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class AdmPermissionFeature extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.adm-permission-feature';

    protected static ?string $navigationGroup = NavigationGroups::ADMIN->value;
    protected static ?string $title = 'ページ権限設定';

    protected static bool $shouldRegisterNavigation = false;

    public string $permissionId;

    public Collection $permissions;

    public function mount()
    {
        // AdministratorAccess以外のpermissionを取得
        $this->permissions = AdmPermission::query()->where('name', '!=', 'AdministratorAccess')->get();
    }

    public function fetchPermissionFeatures(): void
    {
        $this->dispatch(
            'fetch',
            permissionId: $this->permissionId,
        )->to('SelectPermissionFeature');
    }
}
