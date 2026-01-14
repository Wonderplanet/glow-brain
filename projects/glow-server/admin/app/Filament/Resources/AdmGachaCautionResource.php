<?php

namespace App\Filament\Resources;

use App\Constants\AdmPromotionTagFunctionName;
use App\Constants\NavigationGroups;
use App\Facades\Promotion;
use App\Filament\Authorizable;
use App\Filament\Resources\AdmGachaCautionResource\Pages;
use App\Models\Adm\AdmGachaCaution;
use App\Services\AdmGachaCautionService;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use FilamentTiptapEditor\Enums\TiptapOutput;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Database\Eloquent\Builder;

class AdmGachaCautionResource extends Resource
{
    use Authorizable;

    protected static ?string $model = AdmGachaCaution::class;

    protected static ?string $navigationIcon = 'heroicon-o-information-circle';

    protected static ?string $label = "ガシャ注意事項";

    protected static ?string $navigationGroup = NavigationGroups::NOTICE->value;

    protected static ?int $navigationSort = 100;

    public static function canCreate(): bool
    {
        return !Promotion::isPromotionDestinationEnvironment();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('基本情報の設定')->schema([
                TextInput::make('memo')
                    ->label('管理用メモ')
                    ->columnSpanFull()
                    ->helperText('管理用のメモです。ガシャ注意事項の内容には影響しません。'),

                TextInput::make('id')
                    ->label('ガシャ注意事項ID')
                    ->disabled(),

                Promotion::getTagSelectForm(),
            ])->columns(2),

            Section::make('本文の設定')->schema([
                TiptapEditor::make('html_json')
                    ->label('本文')
                    ->columnSpanFull()
                    ->output(TiptapOutput::Json)
                    ->tools([
                        'heading',
                        'bullet-list',
                        'ordered-list',
                        'checked-list',
                        'hr',
                        '|',
                        'color',
                        'bold',
                        'italic',
                        'strike',
                        'underline',
                        'align-left',
                        'align-center',
                        'align-right',
                        '|',
                        'link',
                        'table',
                    ])
                    ->bubbleMenuTools(['table']) // 表の列や行追加などの操作ツールを表示するために、tableのみ追加
                    ->disableBubbleMenus()
                    ->disableFloatingMenus()
                    ->extraInputAttributes(['style' => 'min-height: 30rem;'])
                    ->required(),
            ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordAction(null)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ガシャ注意事項ID')
                    ->sortable()
                    ->searchable()
                    ->size(TextColumnSize::ExtraSmall)
                    ->tooltip('クリックでコピー')
                    ->copyable(),

                Tables\Columns\TextColumn::make('memo')
                    ->label('管理用メモ')
                    ->wrap(),

                Tables\Columns\TextColumn::make('adm_promotion_tag_id')
                    ->label('昇格タグID')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('author_name')
                    ->label('作成者')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Filter::make('ガシャ注意事項ID')
                    ->form([
                        TextInput::make('id')
                            ->label('ガシャ注意事項ID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['id'])) {
                            return $query;
                        }
                        return $query->where('id', 'like', "%{$data['id']}%");
                    }),
                Filter::make('管理用メモ')
                    ->form([
                        TextInput::make('memo')
                            ->label('管理用メモ')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['memo'])) {
                            return $query;
                        }
                        return $query->where('memo', 'like', "%{$data['memo']}%");
                    }),

                Promotion::getTagSelectFilter(),
            ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->actions(self::getActions(), position: ActionsPosition::BeforeColumns)
            ->headerActions(
                Promotion::getHeaderActions(
                    AdmPromotionTagFunctionName::GACHA_CAUTION,
                    function (string $environment, string $admPromotionTagId) {
                        /** @var AdmGachaCautionService $admGachaCautionService */
                        $admGachaCautionService = app(AdmGachaCautionService::class);
                        $admGachaCautionService->import(
                            $environment,
                            $admPromotionTagId,
                        );
                    }
                ),
            );
    }

    private static function getActions(): array
    {
        $isPromotionDestinationEnvironment = Promotion::isPromotionDestinationEnvironment();

        /** @var AdmGachaCautionService $admGachaCautionService */
        $admGachaCautionService = app(AdmGachaCautionService::class);

        return [
            Tables\Actions\ViewAction::make()
                ->label('プレビュー'),
            Tables\Actions\EditAction::make()
                ->visible(!$isPromotionDestinationEnvironment),
            Tables\Actions\DeleteAction::make()
                ->visible(!$isPromotionDestinationEnvironment)
                ->using(function (AdmGachaCaution $admGachaCaution) use ($admGachaCautionService) {
                    $admGachaCautionService->deleteGachaCaution($admGachaCaution);
                }),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdmGachaCaution::route('/'),
            'create' => Pages\CreateAdmGachaCaution::route('/create'),
            'edit' => Pages\EditAdmGachaCaution::route('/{record}/edit'),
            'view' => Pages\ViewAdmGachaCaution::route('/{record}'),
        ];
    }
}
