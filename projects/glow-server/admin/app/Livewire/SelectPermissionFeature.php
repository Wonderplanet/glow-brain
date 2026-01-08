<?php

namespace App\Livewire;

use App\Models\Adm\AdmPermissionFeature;
use App\Services\AdminCacheService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class SelectPermissionFeature extends Component
{
    public string $permissionId;
    public array $features = [];
    public array $permissionFeatures = [];

    protected $listeners = [
        'fetch' => 'fetch',
    ];

    public function render()
    {
        return view('livewire.select-permission-feature');
    }

    public function fetch($permissionId)
    {
        $this->features = [];
        $this->permissionFeatures = [];
        $this->permissionId = $permissionId;
        $resourceFiles = [];
        $pageFiles = glob(base_path('app/Filament/Pages') . '/*.php');
        $subPageFiles = glob(base_path('app/Filament/Pages') . '/*/*.php');
        $files = array_merge($resourceFiles, $pageFiles, $subPageFiles);
        foreach ($files as $file) {
            $tmp = str_replace('Resource', '', basename($file));
            $tmp = str_replace('.php', '', $tmp);
            $this->features[] = $tmp;
        }

        $permissionFeatures = AdmPermissionFeature::query()
            ->where('permission_id', $permissionId)
            ->get();
        foreach ($permissionFeatures as $permissionFeature) {
            $this->permissionFeatures[$permissionFeature['feature_name']] = true;
        }
    }

    public function update()
    {
        $upsertValues = [];
        foreach ($this->permissionFeatures as $feature => $enabled) {
            if ($enabled) {
                $upsertValues[] = [
                    'permission_id' => $this->permissionId,
                    'feature_name' => $feature,
                ];
            } else {
                AdmPermissionFeature::query()
                    ->where('permission_id', $this->permissionId)
                    ->where('feature_name', $feature)
                    ->delete();
            }
        }

        if (!empty($upsertValues)) {
            AdmPermissionFeature::upsert($upsertValues, ['permission_id', 'feature_name'], ['feature_name']);
        }

        /** @var AdminCacheService $adminCacheService */
        $adminCacheService = app(AdminCacheService::class);
        $adminCacheService->deleteAdmPermissionFeature($this->permissionId);

        Notification::make()
            ->title('更新しました')
            ->success()
            ->send();
    }
}
