<?php

namespace App\Filament\Resources;

use App\Constants\AdmPromotionTagFunctionName;
use App\Constants\NavigationGroups;
use App\Models\Adm\AdmS3Object;
use App\Models\Adm\AdmS3BucketScope;
use App\Filament\Resources\AdmS3ObjectResource\Pages;
use App\Filament\Authorizable;
use App\Facades\Promotion;
use App\Services\AdmS3ObjectService;
use App\Tables\Columns\AssetPreviewColumn;
use App\Traits\NotificationTrait;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Collection;

class AdmS3ObjectResource extends Resource
{
    use Authorizable;
    use NotificationTrait;

    protected static ?string $model = AdmS3Object::class;
    protected static ?string $navigationIcon = 'heroicon-o-cloud';
    protected static ?string $label = 'S3アセット管理';
    protected static ?string $navigationGroup = NavigationGroups::CLIENT_ASSET->value;

    public static function canCreate(): bool
    {
        return !Promotion::isPromotionDestinationEnvironment();
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        $admS3ObjectService = app(AdmS3ObjectService::class);

        return $table
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->defaultSort('last_modified_at', 'desc')
            ->columns([
                TextColumn::make('adm_promotion_tag_id')
                    ->label('昇格タグID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bucket')
                    ->label('バケット名')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('object_directory')->label('フォルダ')->limit(60),
                TextColumn::make('object_name')->label('ファイル名')->limit(60),
                AssetPreviewColumn::make('asset_preview')->label('アセット')
                    ->assetWidth(300),
                TextColumn::make('size')
                    ->label('サイズ')
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state !== null ? number_format($state / 1024, 2) . ' KB' : null),
                TextColumn::make('content_type')->label('コンテンツタイプ')->sortable(),
                TextColumn::make('upload_adm_user_name')->label('アップロードユーザー')->searchable(),
                TextColumn::make('last_modified_at')->label('最終更新日時')->sortable(),
            ])
            ->filters([
                Filter::make('key')
                    ->form([
                        TextInput::make('key')
                            ->label('ファイルパス（部分一致）')
                            ->placeholder('パス'),
                    ])
                    ->query(function ($query, array $data) {
                        if (blank($data['key'])) {
                            return $query;
                        }
                        return $query->where('key', 'like', "%{$data['key']}%");
                    }),
                Filter::make('prefix')
                    ->form([
                        Select::make('prefix')
                            ->label('フォルダ')
                            ->options(AdmS3BucketScope::getPrefixOptions())
                            ->placeholder('フォルダを選択'),
                    ])
                    ->query(function ($query, array $data) {
                        if (blank($data['prefix'])) {
                            return $query;
                        }
                        return $query->where('key', 'like', "{$data['prefix']}/%");
                    }),

                Promotion::getTagSelectFilter(),
            ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn(Action $action) => $action
                    ->label('適用'),
            )
            ->headerActions(
                Promotion::getHeaderActions(
                    AdmPromotionTagFunctionName::S3_OBJECT,
                    function (string $environment, string $admPromotionTagId) use ($admS3ObjectService) {
                        $admS3ObjectService->import(
                            $environment,
                            $admPromotionTagId,
                        );
                    }
                ),
            )
            ->actions(self::getActions(), position: ActionsPosition::BeforeColumns)
            ->bulkActions(array_merge(
                self::getBulkActions(),
                [
                    Promotion::getUpdateTagBulkAction(),
                ]
            ));
    }

    public static function getActions(): array
    {
        if (Promotion::isPromotionDestinationEnvironment()) {
            return [];
        }

        return [];
    }

    public static function getBulkActions(): array
    {
        if (Promotion::isPromotionDestinationEnvironment()) {
            return [];
        }

        return [
            Tables\Actions\BulkAction::make('bulkDelete')
                ->label('選択したアセットを削除')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('アセット削除')
                ->modalDescription('選択したアセットをS3とデータベースから完全に削除します。この操作は元に戻せません。')
                ->modalSubmitActionLabel('削除する')
                ->action(function (Collection $records) {
                    $admS3ObjectService = app(AdmS3ObjectService::class);
                    $admS3ObjectService->deleteS3Objects($records);
                }),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdmS3Object::route('/'),
        ];
    }
}
