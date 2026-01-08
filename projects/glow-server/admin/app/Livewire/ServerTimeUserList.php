<?php

namespace App\Livewire;

use App\Filament\Pages\ServerTimeUserSetting;
use App\Models\Usr\UsrUser;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class ServerTimeUserList extends Component implements HasForms, HasTable
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

    public function render()
    {
        return view('livewire.common.table');
    }

    public function table(Table $table): Table
    {
        $query = $this->buildQuery($this->userId, $this->myId, $this->name);
        return $table
            ->query($query)
            ->paginated()
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ユーザーID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('usr_user_profiles.my_id')
                    ->label('マイID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('usr_user_profiles.name')
                    ->label('ユーザー名')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('登録日')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('最終ログイン日')
                    ->searchable()
                    ->sortable(),
            ])
            ->actions([
                Action::make('setting')
                    ->label('変更')
                    ->button()
                    ->url(function (Model $record) {
                        return ServerTimeUserSetting::getUrl([
                            'userId' => $record->id,
                        ]);
                    }),
            ], position: ActionsPosition::BeforeColumns)
            ->filters(
                [],
            );
    }

    private function buildQuery(?string $userId, ?string $myId, ?string $name) : Builder
    {
        return UsrUser::query()->withWhereHas('usr_user_profiles', function ($query) use ($userId, $myId, $name) {
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
    }


    public function search(?string $userId, ?string $myId, ?string $name): void
    {
        $this->userId = $userId;
        $this->myId = $myId;
        $this->name = $name;

        $this->enableList = true;

        $table = $this->getTable();
        $this->table($table);
    }

    public static function getPages(): array
    {
        return [
            'setting' => ServerTimeUserSetting::route('/setting'),
        ];
    }
}
