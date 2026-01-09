<?php

namespace App\Filament\Resources\AdmPermissionResource\Pages;

use App\Filament\Resources\AdmPermissionResource;
use App\Models\Adm\AdmPermission;
use App\Models\Adm\AdmPermissionFeature;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditAdmPermission extends EditRecord
{
    protected static string $resource = AdmPermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('保存')
                ->action('save')
                ->color('primary')
        ];
    }

    protected function handleRecordUpdate($record, array $data): AdmPermission
    {
        $model = $record->getModel();
        $permissionData = [
            'name' => $data['name'],
            'description' => $data['description'],
        ];

        $permissionFeatureData = array_diff_key($data, $permissionData);
        $permissionFeatureData = collect($permissionFeatureData)->flatten(1)->mapWithKeys(fn($v) => [$v => $v]);

        $features = AdmPermissionFeature::query()
            ->where('permission_id', $model->id)
            ->get()
            ->keyBy('feature_name');

        $upsertValues = [];
        foreach ($permissionFeatureData as $featureName) {
            $upsertValues[] = [
                'permission_id' => $model->id,
                'feature_name' => $featureName,
            ];
        }

        // $featuresにあって、$permissionFeatureDataにないものは削除
        $features = $features->diffKeys($permissionFeatureData);
        foreach ($features as $feature) {
            if (!isset($permissionFeatureData[$feature->feature_name])) {
                AdmPermissionFeature::query()
                    ->where('permission_id', $model->id)
                    ->where('feature_name', $feature->feature_name)
                    ->delete();
            }
        }

        if (!empty($upsertValues)) {
            AdmPermissionFeature::upsert($upsertValues, ['permission_id', 'feature_name'], ['feature_name']);
        }

        $model->update($permissionData);
        return $model;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
