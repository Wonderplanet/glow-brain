<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;
use WonderPlanet\Domain\Admin\Filament\Tables\Columns\DateTimeColumn;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Enums\ReleaseStatus;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngAssetReleaseResource\Pages;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngAssetRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngAssetReleaseService;

/**
 * アセットリリース管理画面リソースクラス
 */
class MngAssetReleaseResource extends Resource
{
    protected static ?string $model = MngAssetRelease::class;
    protected static ?string $navigationIcon = 'heroicon-m-list-bullet';
    protected static ?int $navigationSort = -998;
    protected static ?string $navigationGroup = 'v2 マスター・アセット管理';
    protected static ?string $navigationLabel = 'アセット配信管理';
    protected static ?string $modelLabel = 'アセット配信管理ダッシュボード';

    /**
     * テーブル作成
     *
     * @param Table $table
     *
     * @return Table
     */
    public static function table(Table $table): Table
    {
        /** @var MngAssetReleaseService $service */
        $service = app()->make(MngAssetReleaseService::class);

        // 最新のリリース済みMngAssetReleaseを取得
        $allPlatformApplyMngAssetReleases = $service->getAllPlatformApplyMngAssetReleases();
        $latestMngAssetReleaseIos = $service->getAllPlatformLatestReleasedMngAssetReleases()
            ->first(fn (MngAssetRelease $assetRelease) => $assetRelease->platform === PlatformConstant::PLATFORM_IOS);
        $latestMngAssetReleaseAndroid = $service->getAllPlatformLatestReleasedMngAssetReleases()
            ->first(fn (MngAssetRelease $assetRelease) => $assetRelease->platform === PlatformConstant::PLATFORM_ANDROID);

        // last import at(created_at)を取得する為にadm_asset_import_historiesのデータを取得する
        // MEMO データ量が多くなると画面が重くなる可能性があるので、その場合は別途取得する方法を検討する
        $admAssetImportHistories = $service->getLastImportAtMap();

        // プラットフォームを文字列に変換するためのリスト
        $platformList = PlatformConstant::PLATFORM_STRING_LIST;

        return $table
            ->header(view('view-master-asset-admin::filament.resources.mng-asset-release-resource.pages.list-mng-assert-release'))
            ->columns([
                Tables\Columns\TextColumn::make('enabled')
                    ->label('Status')
                    ->formatStateUsing(function ($record) use (
                        $service,
                        $allPlatformApplyMngAssetReleases,
                        $latestMngAssetReleaseIos,
                        $latestMngAssetReleaseAndroid
                    ) {
                        if ($record->platform === PlatformConstant::PLATFORM_ANDROID) {
                            $latestReleaseKey = $latestMngAssetReleaseAndroid?->release_key;
                        } else if ($record->platform === PlatformConstant::PLATFORM_IOS) {
                            $latestReleaseKey = $latestMngAssetReleaseIos?->release_key;
                        }

                        $status = $service->getReleaseStatus($record, $latestReleaseKey, $allPlatformApplyMngAssetReleases);
                        switch ($status) {
                            case ReleaseStatus::RELEASE_STATUS_APPLYING:
                                $status = new HtmlString("<span style='color: deeppink'>配信中</span>");
                                break;
                            case ReleaseStatus::RELEASE_STATUS_PENDING:
                                $status = new HtmlString("<span style='color: darkolivegreen'>配信準備中</span>");
                                break;
                            default:
                                $status = new HtmlString("<span style='color: gray'>配信終了</span>");
                                break;
                        }

                        return $status;
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('platform')
                    ->label('Platform')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state) use ($platformList) {
                        return $platformList[$state];
                    }),
                Tables\Columns\TextColumn::make('release_key')
                    ->label('Release Key')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client_compatibility_version')
                    ->label('クライアント互換性バージョン')
                    ->searchable()
                    ->prefix(function ($record) {
                        // 値が未入力なら接頭辞を非表示にする
                        return blank($record->client_compatibility_version) ? null : '>= ';
                    })
                    ->state(function ($record) {
                        // 値が未入力ならメッセージを表示
                        return $record->client_compatibility_version ?? '未入力です！';
                    })
                    ->icon(function ($record) {
                        // 値が未入力なら警告アイコンを表示
                        return is_null($record->client_compatibility_version) ? 'heroicon-o-exclamation-circle' : '';
                    })
                    ->color(function ($record) {
                        // 値が未入力ならアイコンと文字を赤色に表示
                        return is_null($record->client_compatibility_version) ? 'danger' : '';
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('メモ欄')
                    ->extraAttributes(['style' => 'width:16rem'])
                    ->wrap(),
                DateTimeColumn::make('start_at')
                    ->label('開始日時')
                    ->sortable(),
                Tables\Columns\TextColumn::make('mngAssetReleaseVersion.git_revision')
                    ->label('git revision')
                    ->limit(16), // 先頭の16文字を表示
                Tables\Columns\TextColumn::make('mngAssetReleaseVersion.catalog_hash')
                    ->label('catalog hash')
                    ->limit(8),// 先頭の8文字を表示
                Tables\Columns\TextColumn::make('mngAssetReleaseVersion.asset_total_byte_size')
                    ->label('total bytes')
                    ->formatStateUsing(function ($state) {
                        return Number::fileSize($state);
                    }),
                DateTimeColumn::make('custom_import_at')
                    ->label('last import at')
                    ->getStateUsing(function ($record) use ($admAssetImportHistories) {
                        // mng_asset_releases.target_release_version_idをもとに履歴テーブルからインポート最新日を取得する
                        if (is_null($record->target_release_version_id) || empty($admAssetImportHistories)) {
                            // recordのtarget_release_version_idがnull または $admAssetImportHistories空だった場合は何も表示しない
                            return null;
                        }
                        // 該当target_release_version_idのcreated_atがあれば日付を、なければnullを返す
                        return $admAssetImportHistories[$record->target_release_version_id] ?? null;
                    })
                    ->sortable(),
            ])
            ->defaultSort('release_key', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('詳細'),
            ])
            ->searchable(false)
            // 行のリンク化をさせないように制御
            ->recordUrl(null);
    }

    /**
     * ページ設定
     *
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMngAssetReleases::route('/'),
            'create' => Pages\CreateMngAssetRelease::route('/create'),
            'import' => MngAssetReleaseImportResource\Pages\ImportMngAssetRelease::route('/import'),
            'view' => Pages\ViewMngAssetRelease::route('/{record}'),
        ];
    }
}
