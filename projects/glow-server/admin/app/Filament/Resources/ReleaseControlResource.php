<?php

namespace App\Filament\Resources;

use App\Constants\MasterDataManagementDisplayOrder;
use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Filament\Resources\ReleaseControlResource\Pages;
use App\Models\Opr\OprMasterReleaseControl;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReleaseControlResource extends Resource
{
    use Authorizable;

    protected static ?string $model = OprMasterReleaseControl::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_MANAGEMENT->value;
    protected static ?int $navigationSort = MasterDataManagementDisplayOrder::RELEASE_KEY_DISPLAY_ORDER->value; // メニューの並び順

    protected static ?string $modelLabel = 'リリースキー管理';

    // Listで表示するレコードの調整を行う際の状態記憶
    private static ?OprMasterReleaseControl $applyingReleaseControl = null;

    // ナビゲーションパネルに表示しないようにする
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    /**
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        $applyId = self::getApplyStatusId();
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('id'),
                Tables\Columns\TextColumn::make('status')
                    ->label('status')
                    ->default('')
                    ->formatStateUsing(function ($record) use ($applyId): string {
                        // 取得したレコードそれぞれにstatusの追加処理
                        self::setReleaseControlStatus($record, $applyId);
                        return $record->getStatus() ?? "";
                    })
                    ->color(function ($record): string {
                        return $record->getColor();
                    }),
                Tables\Columns\TextColumn::make('release_key')->label('release_key'),
                Tables\Columns\TextColumn::make('client_data_hash')->label('client_data_hash'),
                Tables\Columns\TextColumn::make('git_revision')->label('git_revision'),
                Tables\Columns\TextColumn::make('release_at')->label('release_at'),
                Tables\Columns\TextColumn::make('created_at')->label('created_at'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * @return array<mixed>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReleaseControls::route('/'),
        ];
    }

    /**
     * @return bool
     */
    public static function canCreate(): bool
    {
        return false;
    }

    /**
     * $releaseControlにstatusカラムをセットする
     *
     * @param OprMasterReleaseControl $releaseControl
     * @param string $applyId
     * @return void
     */
    private static function setReleaseControlStatus(OprMasterReleaseControl $releaseControl, string $applyId): void
    {
        if (is_null(static::$applyingReleaseControl) && $releaseControl->getId() === $applyId) {
            // 適用対象のレコード
            $releaseControl->setStatus(OprMasterReleaseControl::STATUS_APPLYING);
            static::$applyingReleaseControl = $releaseControl;
        } else {
            $releaseControl->setStatus(OprMasterReleaseControl::STATUS_UNUSED);
        }
    }

    /**
     * created_atで降順にソートして一番最初のレコードのIDを返す
     *
     * @return string
     */
    private static function getApplyStatusId(): string
    {
        $model = static::getModel()::query()
            ->select('*')
            ->orderBy('created_at', 'desc')
            ->first();

        return $model->getId();
    }
}
