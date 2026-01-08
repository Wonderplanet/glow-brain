<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Log\LogStore;
use App\Models\Usr\UsrStoreInfo;
use App\Models\Usr\UsrUser;
use App\Models\Usr\UsrUserProfile;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class LogCurrencyHistoryUserTotal extends Component implements HasForms, HasInfolists
{
    use InteractsWithInfolists;
    use InteractsWithForms;

    public string $userId = '';

    protected $listeners = [
        'orderIdUpdated' => 'onOrderIdUpdated',
        'userIdUpdated' => 'onUserIdUpdated',
    ];

    public function render()
    {
        return view('livewire.log-currency-history-user-total');
    }

    public function infoList(Infolist $infolist): Infolist
    {
        // ユーザー名をプロファイルから取得
        $userProfile = UsrUserProfile::query()
            ->where('usr_user_id', $this->userId)
            ->first();
        $userName = $userProfile ? $userProfile->name : '';

        // VIPポイントをショップ情報から取得
        $storeInfo = UsrStoreInfo::query()
            ->where('usr_user_id', $this->userId)
            ->first();
        // 取得できなかった場合はハイフンを表示
        $vipPoint = $storeInfo ? $storeInfo->total_vip_point : '-';

        return $infolist->state([
            'userId' => $this->userId,
            'userName' => $userName,
            'vipPoint' => $vipPoint,
        ])->schema([
            TextEntry::make('userId')
                ->label('ユーザーID'),
            TextEntry::make('userName')
                ->label('ユーザー名'),
            TextEntry::make('vipPoint')
                ->label('VIPポイント')
                ->numeric(
                    decimalPlaces: 0,
                    decimalSeparator: '.',
                    thousandsSeparator: ',',
                ),
        ])->columns(3);
    }

    /**
     * ユーザーIDの更新
     *
     * 検索はユーザーIDのみとする
     * ユーザー名は複数ユーザーで重複する可能性があるため、検索には使用しない
     *
     * @param string $userId
     * @return void
     */
    public function onUserIdUpdated(string $userId)
    {
        $this->userId = $userId;

        $infoList = $this->getInfolist('infoList');
        $this->infoList($infoList);
    }

    /**
     * 課金IDの更新
     *
     * @return void
     */
    public function onOrderIdUpdated()
    {
        // 課金IDでの特定では何もしないよう表示を消す
        $this->userId = '';
    }
}
