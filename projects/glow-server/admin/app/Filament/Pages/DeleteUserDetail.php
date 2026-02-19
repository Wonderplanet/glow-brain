<?php

namespace App\Filament\Pages;

use App\Constants\UserConstant;
use App\Constants\UserStatus;
use App\Domain\Resource\Mng\Models\MngDeletedMyId;
use App\Domain\Resource\Mst\Models\MstAdventBattle;
use App\Filament\Pages\DeleteUser\DeleteUserSearch;
use App\Models\Adm\AdmUserDeletionOperateHistory;
use App\Models\Mst\MstUnit;
use App\Models\Usr\SysPvpSeason;
use App\Models\Usr\UsrPvp;
use App\Models\Usr\UsrUser;
use App\Models\Usr\UsrUserProfile;
use App\Services\AdventBattleCacheService;
use App\Services\BnidService;
use App\Services\PvpCacheService;
use App\Traits\DatabaseTransactionTrait;
use App\Traits\MngCacheDeleteTrait;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class DeleteUserDetail extends Page
{
    use DatabaseTransactionTrait;
    use MngCacheDeleteTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.delete-user-detail';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'アカウント削除詳細';

    public string $userId = '';
    public int $status = 0;

    protected $queryString = [
        'userId',
    ];

    protected array $breadcrumbList = [];

    public function mount()
    {
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            DeleteUserSearch::getUrl() => 'アカウント削除',
            self::getUrl(['userId' => $this->userId]) => 'アカウント削除詳細',
        ]);

        $usrUser = UsrUser::query()
            ->where('id',$this->userId)
            ->first();

        if ($usrUser->status === UserStatus::DELETED->value) {
            $this->status = UserStatus::DELETED->value;
        }
    }

    public function userInfoList(): Infolist
    {
        $usrUser = UsrUser::query()
            ->where('id',$this->userId)
            ->first();

        $state = [
            'id'        => $usrUser->id,
            'status'    => $usrUser->getUserStatus(),
        ];

        $fieldset = Fieldset::make('ユーザー詳細')
            ->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('status')->label('ステータス'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function userProfilInfoList(): Infolist
    {
        $usrUserProfile = UsrUserProfile::query()
            ->where('usr_user_id',$this->userId)
            ->first();

        $state = [
            'my_id'                 => $usrUserProfile->my_id,
            'name'                  => $usrUserProfile->name,
            'mst_unit_id'           => $usrUserProfile->mst_unit_id,
            'mst_emblem_id'         => $usrUserProfile->mst_emblem_id,
        ];

        $fieldset = Fieldset::make('ユーザープロフィール詳細')
            ->schema([
                TextEntry::make('name')->label('名前'),
                TextEntry::make('my_id')->label('MY_ID'),
                TextEntry::make('mst_unit_id')->label('mst_unit_id'),
                TextEntry::make('mst_emblem_id')->label('mst_emblem_id'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(UsrUser::query())
            ->searchable(false)
            ->columns([
                TextColumn::make('id')
                    ->label('Id')
                    ->searchable()
                    ->sortable(),
            ]);
    }

    protected function getActions(): array
    {
        $usrUser = UsrUser::query()
            ->where('id',$this->userId)
            ->first();
        if ($usrUser->status !== UserStatus::DELETED->value) {
            return [
                Action::make('delete')
                    ->label('アカウント削除')
                    ->disabled(function () use ($usrUser){
                        return $usrUser->status === UserStatus::DELETED->value;
                    })
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('アカウント削除')
                    ->modalDescription(new HtmlString('対象アカウントを削除した場合、<br />元に戻す事ができなくなりますが実行しますか？'))
                    ->modalSubmitActionLabel('OK')
                    ->modalCancelActionLabel('キャンセル')
                    ->action(function() {
                        $this->update();
                    }),
            ];
        }
        return [];
    }

    public function update()
    {

        try {
            $this->transaction(function () {
                $now = CarbonImmutable::now();
                $defaultProfileMstUnitId = MstUnit::query()->first()?->id ?? '';

                $usrUser = UsrUser::query()
                    ->where('id',$this->userId)
                    ->first();

                $usrUserProfile = UsrUserProfile::query()
                    ->where('usr_user_id', $this->userId)
                    ->first();
                $myId = $usrUserProfile->my_id ?? '';

                $profileData = [
                    'name' => $usrUserProfile->name ?? '',
                    'mst_unit_id' => $usrUserProfile->mst_unit_id ?? '',
                    'mst_emblem_id' => $usrUserProfile->mst_emblem_id ?? '',
                    'my_id' => $usrUserProfile->my_id ?? '',
                ];

                $expiresAt = $now->addDays(UserConstant::DELETION_PROFILE_DATA_RETENTION_DAYS)->setTime(23, 59, 59);

                AdmUserDeletionOperateHistory::query()->insert([
                    'id' => Str::uuid(),
                    'usr_user_id' => $this->userId,
                    'status' => $usrUser->status,
                    'adm_user_id' => auth()->id(),
                    'profile_data' => json_encode($profileData),
                    'operated_at' => $now,
                    'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                UsrUser::query()
                    ->where('id', $this->userId)
                    ->update([
                        'status' => UserStatus::DELETED->value,
                        'bn_user_id' => null,
                    ]);

                $anonymizedMyId = $this->makeAnonymizedMyId($now);

                UsrUserProfile::query()
                    ->where('usr_user_id', $this->userId)
                    ->update([
                        'my_id' => $anonymizedMyId,
                        'name' => '無名のリーダー',
                        'mst_unit_id' => $defaultProfileMstUnitId,
                        'mst_emblem_id' => '',
                    ]);

                // 再登録防止のため、削除したMyIDを別テーブルに保存
                MngDeletedMyId::query()
                    ->insert([
                        'id' => Str::uuid(),
                        'my_id' => $myId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                $this->deleteMngDeletedMyIdCache();

                // BNID連携解除
                $bnidService = app(BnidService::class);
                $isUnlinked = $bnidService->unlinkBnid($this->userId);
                $unlinkBnidMsg = ($isUnlinked ? "<br>BNID連携解除に成功しました。" : '');

                // 降臨バトルランキングから削除
                $activeMstAdventBattle = MstAdventBattle::query()
                    ->where('start_at', '<=', $now->format('Y-m-d H:i:s'))
                    ->where('end_at', '>=', $now->format('Y-m-d H:i:s'))
                    ->get();
                if ($activeMstAdventBattle->isNotEmpty()) {
                    $adventBattleCacheService = app(AdventBattleCacheService::class);
                    foreach ($activeMstAdventBattle as $mstAdventBattle) {
                        $adventBattleCacheService->removeRanking($mstAdventBattle->id, $this->userId);
                    }
                }

                // ランクマッチランキングから削除
                $sysPvpSeason = SysPvpSeason::query()
                    ->where('start_at', '<=', $now->format('Y-m-d H:i:s'))
                    ->where('closed_at', '>', $now->format('Y-m-d H:i:s'))
                    ->first();

                if ($sysPvpSeason) {
                    $pvpCacheService = app(PvpCacheService::class);
                    $pvpCacheService->removeRanking($sysPvpSeason->id, $this->userId);

                    $usrPvp = UsrPvp::query()
                        ->where('usr_user_id', $this->userId)
                        ->where('sys_pvp_season_id', $sysPvpSeason->id)
                        ->first();
                    
                    // 対戦候補から削除
                    if ($usrPvp && $myId) {
                        $pvpCacheService->deleteOpponentCandidate(
                            $sysPvpSeason->id,
                            $myId,
                            $usrPvp->pvp_rank_class_type,
                            $usrPvp->pvp_rank_class_level,
                        );
                    }
                }

                Notification::make()
                    ->title("アカウントを削除しました。{$unlinkBnidMsg}")
                    ->success()
                    ->send();

                $this->redirect(
                    DeleteUserDetail::getUrl(['userId' => $this->userId]),
                );
            });
        } catch (\Exception $e) {
            Log::error('DeleteUser update Error', [$e]);
            Notification::make()
                    ->title('アカウントの削除に失敗しました。')
                    ->danger()
                    ->send();

                $this->redirect(
                    DeleteUserDetail::getUrl(['userId' => $this->userId]),
                );
        }
    }

    private function makeAnonymizedMyId(CarbonImmutable $now): string
    {
        return 'D' . $now->format('YmdHis');
    }
}
