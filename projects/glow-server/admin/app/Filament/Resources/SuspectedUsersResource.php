<?php

namespace App\Filament\Resources;

use App\Constants\NavigationGroups;
use App\Constants\UserStatus;
use App\Filament\Authorizable;
use App\Filament\Pages\SuspectedUserDetail;
use App\Filament\Resources\UnauthorizedUserResource\Pages;
use App\Models\Log\LogSuspectedUser;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SuspectedUsersResource extends Resource
{
    use Authorizable;

    protected static ?string $model = LogSuspectedUser::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = NavigationGroups::BAN->value;
    protected static ?string $modelLabel = '不正ユーザー';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        $query = LogSuspectedUser::query()
            ->whereIn('id', function($query) {
                $query->select('id')
                    ->from('log_suspected_users as log_suspected_user')
                    ->whereRaw('log_suspected_user.usr_user_id = log_suspected_users.usr_user_id')
                    ->orderBy('created_at', 'desc')
                    ->limit(1);
            })
            ->with(
                'usr_user',
                'usr_user_profile'
            );

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('usr_user_id')
                    ->label('ユーザーID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('usr_user_profile.my_id')
                    ->label('MY_ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('usr_user_profile.name')
                    ->label('名前')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('usr_user.status')
                    ->label('ステータス')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(
                        function ($record) {
                            return $record?->usr_user?->getUserStatus();
                        }
                    ),
            ])
            ->filters([
                Filter::make('usr_user_id')
                    ->form([
                        TextInput::make('usr_user_id')
                            ->label('ユーザーID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['usr_user_id'])) {
                            return $query;
                        }
                        return $query->where('usr_user_id', 'like', "%{$data['usr_user_id']}%");
                    }),
                Filter::make('my_id')
                    ->form([
                        TextInput::make('my_id')
                            ->label('MY_ID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['my_id'])) {
                            return $query;
                        }
                        return $query
                            ->whereHas('usr_user_profile', function ($query) use ($data) {
                                $query->where('my_id', 'like', "%{$data['my_id']}%");
                        });
                    }),
                Filter::make('name')
                    ->form([
                        TextInput::make('name')
                            ->label('名前')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['name'])) {
                            return $query;
                        }
                        return $query
                            ->whereHas('usr_user_profile', function ($query) use ($data) {
                                $query->where('name', 'like', "%{$data['name']}%");
                        });
                    }),
                SelectFilter::make('status')
                    ->options(UserStatus::labels()->toArray())
                    ->query(function (Builder $query, $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query
                            ->whereHas('usr_user', function ($query) use ($data) {
                                $query->where('status', $data['value']);
                            });
                    })
                    ->label('ステータス'),
            ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn (Action $action) => $action
                    ->label('適用'),
            )
            ->actions([
                Action::make('detail')
                    ->label('詳細')
                    ->button()
                    ->url(function (LogSuspectedUser $record) {
                        return SuspectedUserDetail::getUrl([
                            'userId' => $record->usr_user_id,
                        ]);
                    }),
            ]);
        ;

    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuspectedUsers::route('/'),
        ];
    }
}
