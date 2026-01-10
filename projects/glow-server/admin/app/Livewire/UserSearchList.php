<?php

namespace App\Livewire;

use App\Filament\Pages\User\UserDetail;
use App\Models\Usr\UsrUser;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Filament\Tables\Columns\TextColumn;
use App\Constants\BillingStatus;
use App\Constants\UserStatus;
use App\Constants\OsType;
use App\Utils\TimeUtil;
use Illuminate\Support\Facades\Log;

class UserSearchList extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    /*
     * dispatchで受け取るlistener
     *
     * @var array
     */
    protected $listeners = [
        'search' => 'search',
    ];

    /**
     * 検索結果表示フラグ
     *
     * trueの場合にリスト表示する
     * @var boolean
     */
    public bool $enableList = false;

    public ?string $userId = '';
    public ?string $myId = '';
    public ?string $name = '';
    public ?string $nameMatch = '';
    public ?string $bnUserId = '';
    public ?string $osType = '';
    public ?array $level = [];
    public ?array $gameStartAt = [];
    public ?array $lastLoginAt = [];
    public ?string $billingStatus = '';
    public ?string $entry = '';

    public function render()
    {
        return view('livewire.common.table');
    }

    public function table(Table $table): Table
    {
        $query = $this->buildQuery(
            $this->userId,
            $this->myId,
            $this->name,
            $this->nameMatch,
            $this->bnUserId,
            $this->osType,
            $this->level,
            $this->gameStartAt,
            $this->lastLoginAt,
            $this->billingStatus
        );

        return $table
            ->query($query)
            ->paginated()
            ->columns([
                TextColumn::make('id')
                    ->label('プレイヤーID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('usr_user_profiles.name')
                    ->label('プレイヤー名')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('usr_store_product_history.purchase_price')
                    ->label('課金歴')
                    ->getStateUsing(
                        function ($record) {
                            if ($record->usr_store_product_history->isNotEmpty()) {
                                return BillingStatus::CHARGES_APPLY->label();
                            }
                            return BillingStatus::NO_CHARGE->label();
                        }
                    )
                    ->searchable()
                    ->sortable(),
                TextColumn::make('purchase_price')
                    ->label('総課金額（円）')
                    ->getStateUsing(
                        function ($record) {
                            if ($record->usr_store_product_history->isNotEmpty()) {
                                return $record->usr_store_product_history->where('currency_code', 'JPY')->sum('purchase_price') ?? 0;
                            }
                            return '-';
                        }
                    )
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bn_user_id')
                    ->label('引き継ぎID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('usr_user_parameter.level')
                    ->label('レベル')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('usr_user_login.last_login_at')
                    ->label('最終ログイン日')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('game_start_at')
                    ->label('アカウント作成日')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('稼働状況')
                    ->getStateUsing(
                        function ($record) {
                            return $record->getUserStatus();
                        }
                    )
                    ->searchable()
                    ->sortable(),
            ])
            ->actions([
                Action::make('detail')
                    ->label('詳細')
                    ->button()
                    ->url(function (Model $record) {
                        return UserDetail::getUrl([
                            'userId' => $record->id,
                            'entry' => $this->entry,
                        ]);
                    }),
            ])
            ->filters(
                [],
            );
    }

    private function buildQuery(
        ?string $userId,
        ?string $myId,
        ?string $name,
        ?string $nameMatch,
        ?string $bnUserId,
        ?string $osType,
        ?array $level,
        ?array $gameStartAt,
        ?array $lastLoginAt,
        ?string $billingStatus
    ) : Builder
    {
        $query = UsrUser::query();

        if (!empty($bnUserId) || !empty($gameStartAt['gameStartAtStart']) || !empty($gameStartAt['gameStartAtEnd'])) {
            $query->where(function($query) use ($bnUserId, $gameStartAt) {
                if (!empty($bnUserId)) {
                    $query->where('bn_user_id', $bnUserId);
                }

                if (!empty($gameStartAt['gameStartAtStart']) && !empty($gameStartAt['gameStartAtEnd'])) {
                    TimeUtil::addWhereBetweenByDayRange($query, 'game_start_at', 'gameStartAtStart', 'gameStartAtEnd', $gameStartAt);
                } else if (!empty($gameStartAt['gameStartAtStart']) ) {
                    $gameStartAtStart = date( "Y-m-d" , strtotime($gameStartAt['gameStartAtStart'])).' 00:00';
                    $query->where('game_start_at', '>=', $gameStartAtStart);
                } else if (!empty($gameStartAt['gameStartAtEnd'])) {
                    $gameStartAtEnd = date( "Y-m-d" , strtotime($gameStartAt['gameStartAtEnd'])).' 23:59';
                    $query->where('game_start_at', '<=', $gameStartAtEnd);
                }
            });
        }

        if ( !empty($userId) || !empty($myId) || !empty($name) ) {
            $query->WhereHas('usr_user_profiles', function ($query) use ($userId, $myId, $name, $nameMatch) {
                if (!empty($userId)) {
                    $query->where('usr_user_id', $userId);
                }
                if (!empty($myId)) {
                    $query->where('my_id', $myId);
                }
                if (!empty($name)) {
                    if (!empty($nameMatch)) {
                        switch ($nameMatch) {
                            case 'partialMatch':
                                $query->where('name', 'like', '%' . $name . '%');
                                break;
                            case 'prefixMatch':
                                $query->where('name', 'like', $name . '%');
                                break;
                            case 'suffixMatch':
                                $query->where('name', 'like', '%' . $name);
                                break;
                            case 'NoDistinction':
                                $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($name) . '%']);
                                break;
                        }
                    } else {
                        $query->where('name', $name);
                    }
                }
            });
        }

        if (!empty($osType)) {
            $query->WhereHas('usr_store_product_history', function ($query) use ($osType) {
                if ($osType === OsType::IOS->value) {
                    $query->where('os_platform', OsType::IOS->value);
                }
                if ($osType === OsType::ANDROID->value) {
                    $query->where('os_platform', OsType::ANDROID->value);
                }
                if ($osType === OsType::OTHERS->value) {
                    $query->whereNotIn('os_platform', [OsType::IOS->value, OsType::ANDROID->value]);
                }
                if ($osType === OsType::UNKNOWN->value) {
                    $query->whereNotIn('os_platform', [OsType::IOS->value, OsType::ANDROID->value, OsType::OTHERS->value]);
                }
            });
        }

        if (!empty($level['minLevel']) && !empty($level['maxLevel'])) {
            $query->WhereHas('usr_user_parameter', function ($query) use ($level) {
                if (!empty($level['minLevel']) && !empty($level['maxLevel']) ) {
                    $query->whereBetween('level', $level);
                } else if (!empty($level['minLevel']) ) {
                    $query->where('level', '>=', $level['minLevel']);
                } else if (!empty($level['maxLevel'])) {
                    $query->where('level', '<=', $level['maxLevel']);
                }
            });
        }

        if (!empty($lastLoginAt['lastLoginAtStart']) || !empty($lastLoginAt['lastLoginAtEnd'])) {
            $query->WhereHas('usr_user_login', function ($query) use ($lastLoginAt) {
                if (!empty($lastLoginAt['lastLoginAtStart']) && !empty($lastLoginAt['lastLoginAtEnd'])) {
                    TimeUtil::addWhereBetweenByDayRange($query, 'last_login_at', 'lastLoginAtStart', 'lastLoginAtEnd', $lastLoginAt);
                } else if (!empty($lastLoginAt['lastLoginAtStart']) ) {
                    $lastLoginAtStart = date( "Y-m-d" , strtotime($lastLoginAt['lastLoginAtStart'])).' 00:00';
                    $query->where('last_login_at', '>=', $lastLoginAtStart);
                } else if (!empty($lastLoginAt['lastLoginAtEnd'])) {
                    $lastLoginAtEnd = date( "Y-m-d" , strtotime($lastLoginAt['lastLoginAtEnd'])).' 23:59';
                    $query->where('last_login_at', '<=', $lastLoginAtEnd);
                }
            });
        }

        if (!empty($billingStatus) && $billingStatus !== BillingStatus::NO_CONDITIONS->value) {
            $query->when(!empty($billingStatus) && $billingStatus === BillingStatus::CHARGES_APPLY->value,
                function ($query) {
                    return $query->whereHas('usr_store_product_history');
                },
                function ($query) {
                    return $query->whereDoesntHave('usr_store_product_history');
                }
            );
        }

        return $query;
    }


    public function search(
        ?string $userId,
        ?string $myId,
        ?string $name,
        ?string $nameMatch,
        ?string $bnUserId,
        ?string $osType,
        ?array $level,
        ?array $gameStartAt,
        ?array $lastLoginAt,
        ?string $billingStatus,
        ?string $entry,
    ): void
    {
        $this->userId = $userId;
        $this->myId = $myId;
        $this->name = $name;
        $this->nameMatch = $nameMatch;
        $this->bnUserId = $bnUserId;
        $this->osType = $osType;
        $this->level = $level;
        $this->gameStartAt = $gameStartAt;
        $this->lastLoginAt = $lastLoginAt;
        $this->billingStatus = $billingStatus;
        $this->enableList = true;
        $this->entry = $entry;

        $table = $this->getTable();
        $this->table($table);
    }

    public static function getPages(): array
    {
        return [
            'detail' => UserDetail::route('/detail'),
        ];
    }
}
