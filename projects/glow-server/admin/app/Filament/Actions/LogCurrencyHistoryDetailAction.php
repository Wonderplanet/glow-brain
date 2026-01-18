<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Database\Eloquent\Model;

/**
 * 有償・無償一次通貨の詳細を表示するアクション
 *
 * TODO: 表示するためのレコードをID検索しているが、
 * log_currency_paidsとlog_currency_freesのレコードをUNIONしたテーブルから取得しているため、
 * 両方のテーブルに同じIDが存在する場合に、どちらのレコードが取得されるか不明。
 * IDはUUIDが割り当てられているため、重複する想定ではないが、システム上は同じIDを別テーブルに格納できるため発生する可能性がある。
 * 表示の段階で、log_currency_type=paidならlog_currency_paids、freeならlog_currency_freesから取得するように変更できるとよかったが、
 * そこまで細かい制御を行う方法が調べきれなかったため、いったんこの状態で実装した。
 */
class LogCurrencyHistoryDetailAction extends ViewAction
{
    public static function getDefaultName(): ?string
    {
        return 'detail';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->recordTitle('課金履歴詳細');

        $this->infolist(function (Infolist $infolist, Model $record): Infolist {
            return $infolist
                ->schema([
                    // ユーザーID
                    TextEntry::make('usr_user_id')
                        ->label('ユーザーID'),
                    // ユーザー名
                    TextEntry::make('user.usr_user_profiles.name')
                        ->label('ユーザー名'),
                    // 課金ID
                    TextEntry::make('id')
                        ->label('課金ID'),
                    // 一次通貨種類
                    TextEntry::make('log_currency_type')
                        ->label('一次通貨種類'),
                    // OS
                    TextEntry::make('os_platform')
                        ->label('OS'),
                    // 課金プラットフォーム
                    TextEntry::make('billing_platform')
                        ->label('課金プラットフォーム'),
                    // サンドボックス
                    TextEntry::make('is_sandbox')
                        ->label('サンドボックス')
                        ->getStateUsing(function (Model $row): string {
                            // paidでなければサンドボックス表示しない
                            if ($row->log_currency_type !== 'paid') {
                                return '-';
                            }
                            return $row->is_sandbox ? '◯' : '×';
                        }),
                    // 商品名
                    TextEntry::make('product_name')
                        ->label('商品名')
                        ->getStateUsing(function (Model $row): string {
                            // idとnameを結合して返す
                            $result = "";
                            if (!blank($row->trigger_id)) {
                                $result .= $row->trigger_id . ': ';
                            }
                            if (!blank($row->trigger_name)) {
                                $result .= $row->trigger_name;
                            }

                            return $result;
                        })
                        ->columnSpanFull(),
                    // 年齢設定
                    TextEntry::make('age')
                        ->label('年齢設定')
                        ->getStateUsing(function (Model $row): string {
                            // 値が入っていなければ表示しない
                            if (blank($row->age)) {
                                return '-';
                            }
                            return (string)$row->age;
                        })
                        ->alignCenter(),
                    // 金額
                    TextEntry::make('purchase_price')
                        ->label('金額')
                        ->money(function (Model $row): string {
                            return $row->currency_code;
                        }),
                    // 購入日時
                    TextEntry::make('created_at')
                        ->label('購入日時'),
                    // 有償一次通貨の単価
                    TextEntry::make('price_per_amount')
                        ->label('有償一次通貨の単価')
                        ->money(function (Model $row): string {
                            return $row->currency_code;
                        }),
                    // VIPポイント
                    TextEntry::make('vip_point')
                        ->label('VIPポイント')
                        ->numeric(
                            decimalPlaces: 0,
                            decimalSeparator: '.',
                            thousandsSeparator: ',',
                        ),
                    Fieldset::make('有償通貨残高')
                        ->schema([
                            // 有償購入前の残高
                            TextEntry::make('before_amount')
                                ->label('購入前の残高'),
                            // 有償購入後の残高
                            TextEntry::make('current_amount')
                                ->label('購入後の残高')
                                ->getStateUsing(function (Model $row): string {
                                    // 増減分を表示
                                    $result =  $row->current_amount .
                                        ' (' .
                                        ($row->current_amount > 0 ? '+' : '') .
                                        $row->change_amount .
                                        ')';

                                    return $result;
                                }),
                        ])
                        ->columns(2),
                    Fieldset::make('無償通貨残高')
                        ->schema([
                            // 無償購入前の残高
                            TextEntry::make('before_ingame_amount')
                                ->label('購入前の残高'),
                            // 無償購入後の残高
                            TextEntry::make('current_ingame_amount')
                                ->label('購入後の残高')
                                ->getStateUsing(function (Model $row): string {
                                    // 増減分を表示
                                    $result =  $row->current_ingame_amount .
                                        ' (' .
                                        ($row->current_ingame_amount > 0 ? '+' : '') .
                                        $row->change_ingame_amount .
                                        ')';

                                    return $result;
                                }),
                        ])
                        ->columns(2),
                ])
                ->columns(4);
        });
    }
}
