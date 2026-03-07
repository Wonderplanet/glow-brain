<?php

namespace App\Filament\Resources;

use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Filament\Resources\AdmPromotionTagResource\Pages;
use App\Models\Adm\AdmPromotionTag;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdmPromotionTagResource extends Resource
{
    use Authorizable;

    protected static ?string $model = AdmPromotionTag::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $label = "昇格タグ";
    protected static ?string $navigationGroup = NavigationGroups::OTHER->value;
    protected static bool $shouldRegisterNavigation = true;
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('タグ情報')->schema([
                TextInput::make('id')->label('タグ')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->disabled(fn($record) => filled($record)), // 作成時のみ入力可、編集時は変更不可

                Textarea::make('description')->label('メモ')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('タグ')
                    ->searchable()
                    ->tooltip('クリックでコピー')
                    ->copyable(),

                Tables\Columns\TextColumn::make('description')->label('メモ')
                    ->searchable(),
            ])
            ->filters([
                Filter::make('id')->label('タグ')
                    ->form([
                        TextInput::make('id')->label('タグ')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['id'])) {
                            return $query;
                        }
                        return $query->where('id', 'like', "%{$data['id']}%");
                    }),

                Filter::make('description')->label('メモ')
                    ->form([
                        TextInput::make('description')->label('メモ')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['description'])) {
                            return $query;
                        }
                        return $query->where('description', 'like', "%{$data['description']}%");
                    }),
            ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ], position: ActionsPosition::BeforeColumns)
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdmPromotionTags::route('/'),
            'create' => Pages\CreateAdmPromotionTag::route('/create'),
            'edit' => Pages\EditAdmPromotionTag::route('/{record}/edit'),
        ];
    }
}
