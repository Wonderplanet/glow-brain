<?php

namespace App\Filament\Resources\AdmEnvSettingResource\Pages;

use App\Filament\Resources\AdmEnvSettingResource;
use App\Operators\CloudFrontOperator;
use App\Services\ConfigGetService;
use App\Services\EnvSettingService;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\SerializedDataFileOperator;
use WonderPlanet\Util\Cryptography\AesRequestEncryptor;

class EditAdmEnvSetting extends EditRecord
{
    protected static string $resource = AdmEnvSettingResource::class;

    protected static string $view = 'filament.resources.adm-env-setting.edit';

    public function form(Form $form): Form
    {
        return $form->schema([
            Grid::make()->schema([
                TextInput::make('version')
                    ->label('変更するバージョン')
                    ->readOnly(),
                TextInput::make('env_status_string')
                    ->label('現在のステータス')
                    ->readOnly(),
                Hidden::make('client_version_hash'),
            ]),
        ]);
    }

    protected function getFormSchema(): array
    {
        return AdmEnvSettingResource::getFormSchema();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // ステータスの確認
        $isRelease = $data['env_status_string'] === 'リリース中';

        // env_status_stringの更新
        // リリース中の場合は審査中に、審査中の場合はリリース中に変更
        $data['env_status_string'] = ($isRelease) ? '審査中' : 'リリース中';

        // envファイルの暗号化
        $jsonFileName = match (config('app.env')) {
            'production' => ($isRelease) ? 'env_review.json' : 'env_production.json',
            'staging' => 'env_staging.json',
            default => 'env_dev.json',
        };
        
        // client_version_hashからハッシュ部分を抽出
        $clientVersionHash = $data['client_version_hash'];
        $hashParts = explode('_', $clientVersionHash);
        $hash = end($hashParts);
        
        $fileOperator = new SerializedDataFileOperator(new AesRequestEncryptor());
        $envSettingService = new EnvSettingService($fileOperator);
        $encryptedFilePath = $envSettingService->encryptEnvFile($jsonFileName, $data['client_version_hash'], $hash);

        $envSettingService->uploadS3($encryptedFilePath, $data['client_version_hash']);

        // キャッシュクリア
        $configGetService = new ConfigGetService();
        $cloudFrontOperator = new CloudFrontOperator($configGetService);
        $cloudFrontOperator->deleteEnvFileCache(['/env/' . $data['client_version_hash'] . '.data']);

        return parent::mutateFormDataBeforeSave($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
