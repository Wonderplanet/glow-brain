<?php

namespace App\Livewire;

use App\Filament\Pages\SuspendedUser;
use App\Models\Usr\UsrUser;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Filament\Tables\Columns\TextColumn;
use App\Constants\UserStatus;
use App\Models\Adm\AdmUserBanOperateHistory;
use Illuminate\Support\Facades\DB;

class SuspendedUserList extends Component implements HasForms, HasTable
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
    public ?int $status = 0;

    public function render()
    {
        return view('livewire.common.table');
    }

    public function table(Table $table): Table
    {
        $query = $this->buildQuery($this->userId, $this->myId, $this->name, $this->status);

        $admUserBanOperateHistories = AdmUserBanOperateHistory::select('usr_user_id', DB::raw('COUNT(ban_status) as total_ban_status_count'))
            ->where('ban_status', '<>', UserStatus::NORMAL->value)
            ->groupBy('usr_user_id')
            ->get()
            ->keyBy('usr_user_id');

        return $table
            ->query($query)
            ->paginated()
            ->columns([
                TextColumn::make('id')
                    ->label('プレイヤーID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('usr_user_profiles.my_id')
                    ->label('マイID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('usr_user_profiles.name')
                    ->label('名前')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('ステータス')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(
                        function ($record) {
                            return $record?->getUserStatus();
                        }
                    ),
                TextColumn::make('ban_total')
                    ->label('アカウント停止回数')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(
                        function ($record) use ($admUserBanOperateHistories) {
                            if ($admUserBanOperateHistories->has($record?->id)) {
                                return $admUserBanOperateHistories[$record?->id]['total_ban_status_count'];
                            }
                            return 0;
                        }
                    ),
                TextColumn::make('suspend_end_at')
                    ->label('アカウント停止解除予定日時')
                    ->searchable()
                    ->sortable(),
            ])
            ->actions([
                Action::make('detail')
                    ->label('詳細')
                    ->button()
                    ->url(function (Model $record) {
                        return SuspendedUser::getUrl([
                            'userId' => $record->id,
                            'status' => $record->status,
                        ]);
                    }),
            ], position: ActionsPosition::BeforeColumns)
            ->filters(
                [],
            );
    }

    private function buildQuery(?string $userId, ?string $myId, ?string $name, ?int $status) : Builder
    {
        $query = UsrUser::query()->withWhereHas('usr_user_profiles', function ($query) use ($userId, $myId, $name) {
            if (!empty($userId)) {
                $query->where('usr_user_id', $userId);
            }
            if (!empty($myId)) {
                $query->where('my_id', $myId);
            }
            if (!empty($name)) {
                $query->where('name', 'like', "%$name%");
            }
        });
        if ($status !== null) {
            $query->where('status', $status);
        }
        return $query;
    }


    public function search(?string $userId, ?string $myId, ?string $name, ?int $status): void
    {
        $this->userId = $userId;
        $this->myId = $myId;
        $this->name = $name;
        $this->status = $status;

        $this->enableList = true;

        $table = $this->getTable();
        $this->table($table);
    }

    public static function getPages(): array
    {
        return [
            'detail' => SuspendedUser::route('/detail'),
        ];
    }
}
