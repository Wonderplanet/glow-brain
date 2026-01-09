<?php

namespace App\Filament\Resources\AdmEnvSettingResource\Pages;

use App\Filament\Resources\AdmEnvSettingResource;
use App\Models\Adm\AdmEnvSetting;
use App\Services\EnvSettingService;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Config;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\SerializedDataFileOperator;
use WonderPlanet\Util\Cryptography\AesRequestEncryptor;

class CreateAdmEnvSetting extends CreateRecord
{
    protected static string $resource = AdmEnvSettingResource::class;

    protected static bool $canCreateAnother = false;

    public function form(Form $form): Form
    {
        return $form->schema([
            Grid::make()->schema([
                TextInput::make('version')
                    ->label('バージョン')
                    ->required(),
            ]),
        ]);
    }

    protected function handleRecordCreation(array $data): AdmEnvSetting
    {
        // client_version_hashの更新
        $salt = Config::get('wp_encryption.env_data_salt');
        $version = $data['version'];
        $convertedVersion = str_replace('.', '_', $data['version']);
        $source = $version . "_" . $salt;
        $hash = md5($convertedVersion . '_' . md5($source));
        $data['client_version_hash'] = $convertedVersion . '_' . $hash;

        // env_status_stringの更新
        $data['env_status_string'] = '審査中';

        $model = $this->getModel()::create($data);

        $jsonFileName = match (config('app.env')) {
            'production' => 'env_review.json',
            'staging' => 'env_staging.json',
            default => 'env_dev.json',
        };

        $fileOperator = new SerializedDataFileOperator(new AesRequestEncryptor());
        $envSettingService = new EnvSettingService($fileOperator);
        $encryptedFilePath = $envSettingService->encryptEnvFile($jsonFileName, $data['client_version_hash'], $hash);

        $envSettingService->uploadS3($encryptedFilePath, $data['client_version_hash']);

        return $model;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
