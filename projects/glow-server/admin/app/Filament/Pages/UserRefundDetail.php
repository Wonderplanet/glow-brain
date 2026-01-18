<?php

namespace App\Filament\Pages;

use App\Constants\SystemConstants;
use App\Domain\AppStore\Models\LogAppStoreRefund;
use App\Domain\GooglePlay\Models\LogGooglePlayRefund;
use App\Filament\Resources\UsrRefundResource;
use App\Models\Opr\OprProduct;
use App\Models\Usr\UsrUser;
use App\Tables\Columns\OprProductInfoColumn;
use Filament\Actions\Action as FilamentAction;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UserRefundDetail extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.user-refund-detail';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = '課金キャンセル詳細';

    public string $userId = '';

    protected $queryString = [
        'userId',
    ];

    protected array $breadcrumbList = [];

    public function mount()
    {
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            UsrRefundResource::getUrl() => '課金キャンセル履歴',
            self::getUrl(['userId' => $this->userId]) => '課金キャンセル詳細',
        ]);
    }

    public function userInfoList(): Infolist
    {
        $usrUser = UsrUser::query()
            ->where('id', $this->userId)
            ->with([
                'usr_user_profiles'
            ])
            ->first();

        $usrUserProfile = $usrUser->usr_user_profiles;

        $totalRefund = LogGooglePlayRefund::query()
            ->select([
                'usr_store_product_histories.usr_user_id as usr_user_id',
                'usr_store_product_histories.purchase_price as purchase_price',
            ])
            ->join('usr_store_product_histories', function ($join) {
                $join->on('log_google_play_refunds.transaction_id', '=', 'usr_store_product_histories.receipt_unique_id');
            })
            ->where('usr_user_id', $this->userId)
            ->unionAll(LogAppStoreRefund::query()
                ->select([
                    'usr_store_product_histories.usr_user_id as usr_user_id',
                    'usr_store_product_histories.purchase_price as purchase_price',
                ])
                ->join('usr_store_product_histories', function ($join) {
                    $join->on('log_app_store_refunds.transaction_id', '=', 'usr_store_product_histories.receipt_unique_id');
                })
                ->where('usr_user_id', $this->userId)
            )->sum('purchase_price');

        $state = [
            'id'           => $usrUser->id,
            'my_id'        => $usrUserProfile->my_id,
            'name'         => $usrUserProfile->name,
            'total_refund' => $totalRefund,
            'multi_login'  => $usrUser->isAccountLinkingRestricted() ? '停止中' : '利用可能',
        ];
        $fieldset = Fieldset::make('ユーザー詳細')
            ->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('my_id')->label('MY_ID'),
                TextEntry::make('name')->label('名前'),
                TextEntry::make('total_refund')->label('課金キャンセル額合計'),
                TextEntry::make('multi_login')->label('マルチログイン'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function table(Table $table): Table
    {
        $query = LogGooglePlayRefund::query()
            ->select([
                'log_google_play_refunds.refunded_at as refunded_at',
                'usr_store_product_histories.id as id',
                'usr_store_product_histories.os_platform as os_platform',
                'usr_store_product_histories.usr_user_id as usr_user_id',
                'usr_store_product_histories.purchase_price as purchase_price',
                'usr_store_product_histories.product_sub_id as product_sub_id',
                'usr_store_product_histories.currency_code as currency_code',
                'usr_store_product_histories.is_sandbox as is_sandbox',
                'usr_store_product_histories.deleted_at as deleted_at',
            ])
            ->join('usr_store_product_histories', function ($join) {
                $join->on('log_google_play_refunds.transaction_id', '=', 'usr_store_product_histories.receipt_unique_id');
            })
            ->where('usr_user_id', $this->userId)
            ->unionAll(LogAppStoreRefund::query()
                ->select([
                    'log_app_store_refunds.refunded_at as refunded_at',
                    'usr_store_product_histories.id as id',
                    'usr_store_product_histories.os_platform as os_platform',
                    'usr_store_product_histories.usr_user_id as usr_user_id',
                    'usr_store_product_histories.purchase_price as purchase_price',
                    'usr_store_product_histories.product_sub_id as product_sub_id',
                    'usr_store_product_histories.currency_code as currency_code',
                    'usr_store_product_histories.is_sandbox as is_sandbox',
                    'usr_store_product_histories.deleted_at as deleted_at',
                ])
                ->join('usr_store_product_histories', function ($join) {
                    $join->on('log_app_store_refunds.transaction_id', '=', 'usr_store_product_histories.receipt_unique_id');
                })
                ->where('usr_user_id', $this->userId)
            );
        $productSubIds = $query->pluck('product_sub_id')->toArray();
        $oprProducts = OprProduct::query()
            ->whereIn('id', $productSubIds)
            ->get()
            ->keyBy('id');

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('refunded_at')
                    ->label('課金キャンセル日時')
                    ->timezone(SystemConstants::VIEW_TIMEZONE)
                    ->dateTime('Y/m/d H:i:s')
                    ->sortable(),
                TextColumn::make('os_platform')
                    ->label('OS'),
                TextColumn::make('purchase_price')
                    ->label('キャンセル課金額'),
                OprProductInfoColumn::make('opr_product_info')
                    ->label('商品情報')
                    ->searchable()
                    ->getStateUsing(function ($record) use ($oprProducts) {
                        return $oprProducts->get($record->product_sub_id);
                    }),
                TextColumn::make('currency_code')
                    ->label('通貨コード'),
                TextColumn::make('is_sandbox')
                    ->label('サンドボックス')
                    ->getStateUsing(function (Model $row): string {
                        return $row->is_sandbox ? '◯' : '×';
                    })->alignCenter(),
            ])
            ->deferFilters()
            ->defaultSort('refunded_at')
            ->filtersApplyAction(
                fn(Action $action) => $action
                    ->label('適用'),
            )
            ->description('課金キャンセル履歴');
    }

    protected function getActions(): array
    {
        $usrUser = UsrUser::query()
            ->where('id', $this->userId)
            ->first();

        if ($usrUser->isAccountLinkingRestricted()) {
            $label = 'マルチログイン停止解除';
            $param = false;
        } else {
            $label = 'マルチログイン停止';
            $param = true;
        }

        return [
            FilamentAction::make('restrict')
                ->label($label)
                ->requiresConfirmation()
                ->extraAttributes(['type' => 'button'])
                ->action(fn () => $this->updateIsAccountLinkingRestricted($param)),
        ];
    }

    private function updateIsAccountLinkingRestricted(bool $isAccountLinkingRestricted)
    {
        UsrUser::query()
            ->where('id', $this->userId)
            ->update([
                'is_account_linking_restricted' => (int)$isAccountLinkingRestricted,
            ]);

        if ($isAccountLinkingRestricted) {
            $title = 'マルチログインを停止しました。';
        } else {
            $title = 'マルチログイン停止を解除しました。';
        }

        Notification::make()
            ->title($title)
            ->success()
            ->send();
    }
}
