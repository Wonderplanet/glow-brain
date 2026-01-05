<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Log\LogCurrencyRevertHistory;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class LogCurrencyRevertHistoryList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    /**
     * dispatchで受け取るlistener
     *
     * @var array
     */
    protected $listeners = [
        'searchUpdated' => 'onSearchUpdated',
    ];

    /**
     * ユーザーID検索条件
     *
     * @var string|null
     */
    public ?string $userId = '';

    /**
     * 開始日の検索条件
     *
     * @var string|null
     */
    public ?string $startDate = '';

    /**
     * 終了日の検索条件
     *
     * @var string|null
     */
    public ?string $endDate = '';

    /**
     * ページのレンダリング
     *
     * @return void
     */
    public function render()
    {
        return view('livewire.log-currency-revert-history-list');
    }

    /**
     * テーブル表示情報を作成
     *
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        $query = LogCurrencyRevertHistory::query();
        $query = $this->buildQuery($query);

        return $table->query($query)
            ->columns([
                // コンテンツ消費日時
                TextColumn::make('log_created_at')
                    ->label('コンテンツ消費日時')
                    ->sortable(),
                // 消費コンテンツ名
                TextColumn::make('log_trigger_name')
                    ->label('消費コンテンツ名')
                    ->getStateUsing(function (Model $record): string {
                        // 名前とIDを表示
                        return $record->log_trigger_type . '/' . $record->log_trigger_id . ': ' . $record->log_trigger_name;
                    })
                    ->sortable(),
                // 消費有償一次通貨
                TextColumn::make('log_change_paid_amount')
                    ->label('消費有償一次通貨')
                    ->sortable(),
                // 消費無償一次通貨
                TextColumn::make('log_change_free_amount')
                    ->label('消費無償一次通貨')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('detail')
                    ->label('詳細')
                    ->icon('heroicon-o-information-circle')
                    ->infolist(function (Infolist $infolist, Model $record) {
                        return $infolist
                            ->state([
                                'record' => $record,
                                'paidLog' => $record->paidLog,
                                'freeLog' => $record->freeLog,
                                'paidLogIds' => $record->paidLogIds,
                                'freeLogIds' => $record->freeLogIds,
                            ])
                            ->schema([
                                TextEntry::make('record.usr_user_id')
                                    ->label('ユーザーID'),
                                TextEntry::make('record.log_trigger_type')
                                    ->label('消費コンテンツ種別'),
                                TextEntry::make('record.log_trigger_id')
                                    ->label('消費コンテンツID'),
                                TextEntry::make('record.log_trigger_name')
                                    ->label('消費コンテンツ名'),
                                TextEntry::make('record.log_change_paid_amount')
                                    ->label('消費有償一次通貨'),
                                TextEntry::make('record.log_change_free_amount')
                                    ->label('消費無償一次通貨'),
                                TextEntry::make('record.log_created_at')
                                    ->label('消費日時'),
                                TextEntry::make('record.log_request_id')
                                    ->label('リクエストID'),
                                TextEntry::make('record.comment')
                                    ->label('コメント')
                                    ->columnSpanFull(),

                                // 返却した時に記録した有償一次通貨の変動ログ
                                RepeatableEntry::make('paidLog')
                                    ->label('返却実行時の有償一次通貨ログ')
                                    ->schema([
                                        TextEntry::make('id')
                                            ->label('ID'),
                                        TextEntry::make('seq_no')
                                            ->label('シーケンス番号'),
                                        TextEntry::make('change_amount')
                                            ->label('変動量'),
                                        TextEntry::make('created_at')
                                            ->label('実行日時'),
                                    ])
                                    ->columnSpanFull()
                                    ->columns(5),

                                // 返却した時に記録した無償一次通貨の変動ログ
                                RepeatableEntry::make('freeLog')
                                    ->label('返却実行時の無償一次通貨ログ')
                                    ->schema([
                                        TextEntry::make('id')
                                            ->label('ID'),
                                        TextEntry::make('change_ingame_amount')
                                            ->label('ingame 変動量'),
                                        TextEntry::make('change_bonus_amount')
                                            ->label('bonus 変動量'),
                                        TextEntry::make('change_reward_amount')
                                            ->label('reward 変動量'),
                                        TextEntry::make('created_at')
                                            ->label('実行日時'),
                                    ])
                                    ->columnSpanFull()
                                    ->columns(5),

                                // 情報として記載
                                //   返却対象になった有償一次通貨の変動ログ
                                RepeatableEntry::make('paidLogIds')
                                    ->label('返却対象になった有償一次通貨ログ')
                                    ->schema([
                                        TextEntry::make('revert_log_currency_paid_id')
                                            ->label('返却対象としたログID'),
                                    ])
                                    ->columnSpanFull(),
                                //   返却対象になった無償一次通貨の変動ログ
                                RepeatableEntry::make('freeLogIds')
                                    ->label('返却対象になった無償一次通貨ログ')
                                    ->schema([
                                        TextEntry::make('revert_log_currency_free_id')
                                            ->label('返却対象としたログID'),
                                    ])
                                    ->columnSpanFull(),

                            ])
                            ->columns(3);
                    })
                    // 閉じるボタンのみ表示
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('閉じる'),

            ], position: ActionsPosition::BeforeColumns);
    }

    /**
     * 検索条件を更新する
     *
     * @param string $userId
     * @param string $startDate
     * @param string $endDate
     * @return void
     */
    public function onSearchUpdated(string $userId, string $startDate, string $endDate)
    {
        $this->userId = $userId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * 検索条件からクエリビルダを生成する
     *
     * @param Builder $query
     * @return Builder
     */
    private function buildQuery(Builder $query): Builder
    {
        // user_idがない場合は空振りになるので、そのまま返す
        $query->where('usr_user_id', $this->userId);

        if ($this->startDate !== '') {
            $query->where('created_at', '>=', $this->startDate);
        }
        if ($this->endDate !== '') {
            $query->where('created_at', '<=', $this->endDate);
        }

        return $query;
    }
}
