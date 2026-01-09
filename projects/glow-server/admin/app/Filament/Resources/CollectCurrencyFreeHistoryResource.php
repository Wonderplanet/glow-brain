<?php

namespace App\Filament\Resources;

use App\Constants\NavigationGroups;
use App\Constants\SystemConstants;
use App\Filament\Authorizable;
use App\Filament\Resources\CollectCurrencyFreeHistoryResource\Pages;
use App\Models\App\Models\CollectCurrencyFreeHistory;
use App\Models\Log\LogCurrencyFree;
use Filament\Forms\Components\Fieldset as ComponentsFieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use WonderPlanet\Domain\Currency\Entities\Trigger;

/**
 * 無償一次通貨の回収ログを表示するリソース
 *
 * ログはLogCurrencyFreeのレコードを参照する
 */
class CollectCurrencyFreeHistoryResource extends Resource
{
    use Authorizable;

    /**
     * 参照するモデルは無償一次通貨のログ
     * @var string|null
     */
    protected static ?string $model = LogCurrencyFree::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 110;
    protected static ?string $navigationGroup = NavigationGroups::CS->value;

    protected static ?string $modelLabel = '無償一次通貨回収ログ';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('usr_user_id')
                    ->label('ユーザーID')
                    ->sortable(),
                TextColumn::make('change_ingame_amount')
                    ->label('変更したゲーム内配布通貨数'),
                TextColumn::make('change_bonus_amount')
                    ->label('変更した購入ボーナス通貨数'),
                TextColumn::make('change_reward_amount')
                    ->label('変更した広告リワード通貨数'),
                TextColumn::make('trigger_detail')
                    ->label('コメント'),

                TextColumn::make('created_at')
                    ->timezone(SystemConstants::VIEW_TIMEZONE)
                    ->dateTime('Y/m/d H:i:s')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->timezone(SystemConstants::VIEW_TIMEZONE)
                    ->dateTime('Y/m/d H:i:s')
                    ->sortable(),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('usr_user_id')
                    ->form([
                        TextInput::make('usr_user_id')
                            ->label('ユーザーID')
                    ])
                    ->label('ユーザーID')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['usr_user_id'])) {
                            return $query;
                        }
                        return $query->where('usr_user_id', "{$data['usr_user_id']}");
                    }),
            ])
            ->actions([
                ViewAction::make(),
            ], position: ActionsPosition::BeforeColumns);
    }

    /**
     * 無償一次通貨回収のtrigger_typeのみを対象にする
     *
     * @return Builder
     */
    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()->where('trigger_type', Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_FREE_ADMIN);
    }

    /**
     * 詳細Viewの表示用
     *
     * @param Infolist $infolist
     * @return Infolist
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('id'),
                TextEntry::make('usr_user_id')
                    ->label('ユーザーID'),
                TextEntry::make('os_platform')
                    ->label('OS'),

                Fieldset::make('before')
                    ->label('変更前')
                    ->schema([
                        TextEntry::make('before_ingame_amount')
                            ->label('ゲーム内配布通貨数'),
                        TextEntry::make('before_bonus_amount')
                            ->label('購入ボーナス通貨数'),
                        TextEntry::make('before_reward_amount')
                            ->label('広告リワード通貨数'),
                    ])
                    ->columns(3),

                Fieldset::make('change')
                    ->label('変更した数')
                    ->schema([
                        TextEntry::make('change_ingame_amount')
                            ->label('ゲーム内配布通貨数'),
                        TextEntry::make('change_bonus_amount')
                            ->label('購入ボーナス通貨数'),
                        TextEntry::make('change_reward_amount')
                            ->label('広告リワード通貨数'),
                    ])
                    ->columns(3),

                Fieldset::make('current')
                    ->label('変更後')
                    ->schema([
                        TextEntry::make('current_ingame_amount')
                            ->label('ゲーム内配布通貨数'),
                        TextEntry::make('current_bonus_amount')
                            ->label('購入ボーナス通貨数'),
                        TextEntry::make('current_reward_amount')
                            ->label('広告リワード通貨数'),
                    ])
                    ->columns(3),

                Fieldset::make('trigger')
                    ->label('トリガー')
                    ->schema([
                        TextEntry::make('trigger_type')
                            ->label('トリガータイプ'),
                        TextEntry::make('trigger_id')
                            ->label('トリガーID'),
                        TextEntry::make('trigger_name')
                            ->label('トリガー名'),
                        TextEntry::make('trigger_detail')
                            ->label('トリガー詳細'),
                    ])
                    ->columns(4),

                TextEntry::make('trigger_detail')
                    ->label('コメント')
                    ->columnSpanFull(),

                TextEntry::make('created_at')
                    ->timezone(SystemConstants::VIEW_TIMEZONE)
                    ->dateTime('Y/m/d H:i:s'),
                TextEntry::make('updated_at')
                    ->timezone(SystemConstants::VIEW_TIMEZONE)
                    ->dateTime('Y/m/d H:i:s'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollectCurrencyFreeHistories::route('/'),
            'view' => Pages\ViewCollectCurrencyFreeHistory::route('/{record}'),
        ];
    }
}
