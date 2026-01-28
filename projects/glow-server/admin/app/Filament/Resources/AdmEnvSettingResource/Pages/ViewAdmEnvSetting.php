<?php

namespace App\Filament\Resources\AdmEnvSettingResource\Pages;

use App\Filament\Resources\AdmEnvSettingResource;
use App\Services\EnvSettingService;
use App\Utils\TimeUtil;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\Admin\Operators\S3Operator;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\SerializedDataFileOperator;
use WonderPlanet\Util\Cryptography\AesRequestEncryptor;

class ViewAdmEnvSetting extends ViewRecord
{
    protected static string $resource = AdmEnvSettingResource::class;

    protected static string $view = 'filament.resources.adm-env-setting.view';

    /**
     * 詳細情報表示
     *
     * @param \Filament\Infolists\Infolist $infolist
     * @return \Filament\Infolists\Infolist
     */
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()->schema([
                    Grid::make(['default' => 3])
                        ->schema([
                            TextEntry::make('id')->label('ID'),
                            TextEntry::make('version')->label('バージョン'),
                            TextEntry::make('client_version_hash')->label('クライアントバージョンハッシュ'),
                            TextEntry::make('env_status_string')->label('ステータス'),
                            TextEntry::make('created_at')
                                ->label('作成日時(JST)')
                                ->formatStateUsing(fn(string $state): string => TimeUtil::formatJapanese($state)),
                            TextEntry::make('updated_at')
                                ->label('更新日時(JST)')
                                ->formatStateUsing(fn(string $state): string => TimeUtil::formatJapanese($state)),
                        ])
                ]),
            ]);
    }

    /**
     * 環境情報のinfolist
     *
     * @param \Filament\Infolists\Infolist $infolist
     * @return \Filament\Infolists\Infolist
     */
    public function envInfolist(Infolist $infolist): Infolist
    {
        $record = $this->getRecord();
        $clientVersionHash = $record->client_version_hash;

        $s3Operator = new S3Operator();
        $file = $s3Operator->getFileContent('s3_env_file', 'env/'. $clientVersionHash .'.data');

        $fileOperator = new SerializedDataFileOperator(new AesRequestEncryptor());
        $envSettingService = new EnvSettingService($fileOperator);
        $envInfo = $envSettingService->decryptEnvFile($file, $clientVersionHash);
        $envInfoSchema = [];
        foreach ($envInfo as $key => $value) {
            $envInfoSchema[] = TextEntry::make($key)->label($key);
        }

        return $infolist
            ->state($envInfo)
            ->schema([
                Section::make('環境情報')
                    ->schema([
                        Grid::make(['default' => 3])
                            ->schema($envInfoSchema)
                    ]),
            ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
