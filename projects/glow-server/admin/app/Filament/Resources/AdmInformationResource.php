<?php

namespace App\Filament\Resources;

use App\Constants\AdmPromotionTagFunctionName;
use App\Constants\InformationCategory;
use App\Constants\NavigationGroups;
use App\Constants\NoticeCategory;
use App\Constants\OsType;
use App\Constants\PublicationStatus;
use App\Constants\SystemConstants;
use App\Entities\Clock;
use App\Facades\Promotion;
use App\Filament\Authorizable;
use App\Filament\Resources\AdmInformationResource\Pages;
use App\Models\Adm\AdmInformation;
use App\Services\AdmInformationService;
use App\Services\ConfigGetService;
use App\Traits\ClockTrait;
use App\Traits\NotificationTrait;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use FilamentTiptapEditor\Enums\TiptapOutput;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AdmInformationResource extends Resource
{
    use Authorizable;
    use ClockTrait;
    use NotificationTrait;

    protected static ?string $model = AdmInformation::class;

    protected static ?string $navigationIcon = 'heroicon-o-information-circle';

    protected static ?string $label = "お知らせ";

    protected static ?string $navigationGroup = NavigationGroups::NOTICE->value;

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return !Promotion::isPromotionDestinationEnvironment() && Authorizable::canCreate();
    }

    public static function form(Form $form): Form
    {
        /** @var ConfigGetService $configGetService */
        $configGetService = app(ConfigGetService::class);

        /** @var AdmInformationService $admInformationService */
        $admInformationService = app(AdmInformationService::class);

        $defaultDateString = (new CarbonImmutable())
            ->setTimezone($configGetService->getTimezone())
            ->setHour(0)
            ->setMinute(0)
            ->setSecond(0)
            ->format(SystemConstants::VIEW_DATETIME_FORMAT);

        $livewire = $form->getLivewire();
        $bannerUrl = $admInformationService->getBannerUrl($livewire->record);

        return $form->schema([
            Section::make('基本情報の設定')->schema([
                TextInput::make('title')
                    ->label('タイトル')
                    ->required(),

                Select::make('category')
                    ->label('カテゴリ')
                    ->options(InformationCategory::labels()->toArray())
                    ->required(),

                Select::make('os_type')
                    ->label('配信するOSタイプ')
                    ->options(OsType::getFormSelectOptions())
                    ->default(OsType::ALL->value)
                    ->required(),

                Toggle::make('is_delete_banner')
                    ->label('バナー画像を削除')
                    ->visible(function ($livewire) {
                        /** @var AdmInformation $admInformation */
                        $admInformation = $livewire->record;
                        return $admInformation->hasBanner();
                    })
                    ->reactive()
                    ->hiddenOn(['create'])
                    ->columnSpan(2),

                ViewField::make('image_preview')
                    ->dehydrated(false)
                    ->view(
                        'components.image-preview',
                        [
                            'label' => '設定中のバナー画像',
                            'url' => $bannerUrl,
                        ],
                    )
                    ->visible(function ($livewire, Get $get) {
                        if ($get('is_delete_banner', false)) {
                            return false;
                        }

                        /** @var AdmInformation $admInformation */
                        $admInformation = $livewire->record;
                        return $admInformation->hasBanner();
                    })
                    ->hiddenOn(['create'])
                    ->columnSpan(1),

                FileUpload::make('image')->label('バナー画像')
                    ->image()
                    ->dehydrated(false)
                    ->saveUploadedFileUsing(function (UploadedFile $file, Set $set, $state) {
                        $fileName = $file?->getClientOriginalName() ?? '';
                        $set('banner_url', implode('/', [
                            NoticeCategory::INFORMATION->value,
                            $fileName,
                        ]));
                        $set('banner_local_file_path', $file?->getRealPath() ?? '');
                    })
                    ->columnSpan(1),
                Hidden::make('banner_url'),
                Hidden::make('banner_local_file_path'),

                DateTimePicker::make('pre_notice_start_at')
                    ->label('予告開始日時（JST）')
                    ->beforeOrEqual('start_at')
                    ->afterStateHydrated(
                        function (Set $set, $record) use ($defaultDateString) {
                            $set('pre_notice_start_at', $record?->formatted_pre_notice_start_at ?? $defaultDateString);
                        }
                    )
                    ->columnSpanFull()
                    ->required(),

                DateTimePicker::make('start_at')
                    ->label('掲載開始日時（JST）')
                    ->afterStateHydrated(
                        function (Set $set, $record) use ($defaultDateString) {
                            $set('start_at', $record?->formatted_start_at ?? $defaultDateString);
                        }
                    )
                    ->required(),
                DateTimePicker::make('end_at')
                    ->label('掲載終了日時（JST）')
                    ->after('start_at')
                    ->afterStateHydrated(
                        function (Set $set, $record) use ($defaultDateString) {
                            $set('end_at', $record?->formatted_end_at ?? $defaultDateString);
                        }
                    )
                    ->required(),

                DateTimePicker::make('post_notice_end_at')
                    ->label('終了日時（JST）')
                    ->afterOrEqual('end_at')
                    ->afterStateHydrated(
                        function (Set $set, $record) use ($defaultDateString) {
                            $set('post_notice_end_at', $record?->formatted_post_notice_end_at ?? $defaultDateString);
                        }
                    )
                    ->columnSpanFull()
                    ->required(),

                Select::make('enable')
                    ->label('公開状態')
                    ->options([
                        1 => '公開',
                        0 => '非公開',
                    ])
                    ->default(0)
                    ->required(),

                TextInput::make('priority')->label('表示優先度')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Promotion::getTagSelectForm(),

                TextInput::make('id')
                    ->label('お知らせID')
                    ->disabled(),

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
                        'media',
                        'table',
                    ])
                    ->bubbleMenuTools(['table']) // 表の列や行追加などの操作ツールを表示するために、tableのみ追加
                    ->disableFloatingMenus()
                    ->extraInputAttributes(['style' => 'min-height: 30rem;'])
                    ->required(),
            ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        /** @var Clock $clock */
        $clock = app(Clock::class);
        $now = $clock->now();
        $nowForQuery = $clock->applyTimezoneForQuery($now);

        /** @var AdmInformationService $admInformationService */
        $admInformationService = app(AdmInformationService::class);

        return $table
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->defaultSort('priority', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('publication_status')
                    ->label('公開ステータス')
                    ->searchable()
                    ->getStateUsing(
                        function (AdmInformation $admInformation) use ($now) {
                            return $admInformation->getDisplayStatus($now)->label();
                        }
                    )
                    ->badge(true)
                    ->color(function (AdmInformation $admInformation) use ($now) {
                        return $admInformation->getDisplayStatus($now)->badge();
                    }),

                Tables\Columns\TextColumn::make('adm_promotion_tag_id')
                    ->label('昇格タグID')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('カテゴリ')
                    ->sortable()
                    ->getStateUsing(function (AdmInformation $record): string {
                        return $record->category_label;
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('タイトル')
                    ->limit(50)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('os_type')
                    ->label('OSタイプ')
                    ->sortable()
                    ->getStateUsing(function (AdmInformation $record): string {
                        return OsType::tryFrom($record->os_type)?->label() ?? $record->os_type;
                    })
                    ->searchable(),
                Tables\Columns\ImageColumn::make('banner')->label('バナー画像')
                    ->getStateUsing(function (AdmInformation $record) use ($admInformationService): string {
                        /** @var AdmInformationService $admInformationService */
                        return $admInformationService->getBannerUrl($record);
                    })
                    ->extraImgAttributes(
                        ['style' => 'height: 5em; width: auto; object-fit: cover;']
                    ),
                Tables\Columns\TextColumn::make('pre_notice_start_at')->label('予告掲載開始日時(JST)')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_at')->label('掲載開始日時(JST)')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('end_at')->label('掲載終了日時(JST)')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('post_notice_end_at')->label('終了日時(JST)')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('priority')->label('表示優先度')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('id')
                    ->label('お知らせID')
                    ->sortable()
                    ->searchable()
                    ->size(TextColumnSize::ExtraSmall) // IDが長いので小さく表示して横幅を節約
                    ->tooltip('クリックでコピー')
                    ->copyable(),

                Tables\Columns\TextColumn::make('author_name')->label('作成者')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Filter::make('お知らせID')
                    ->form([
                        TextInput::make('id')
                            ->label('お知らせID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['id'])) {
                            return $query;
                        }
                        return $query->where('id', 'like', "%{$data['id']}%");
                    }),

                Filter::make('タイトル')
                    ->form([
                        TextInput::make('title')
                            ->label('タイトル')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['title'])) {
                            return $query;
                        }
                        return $query->where('title', 'like', "%{$data['title']}%");
                    }),

                SelectFilter::make('category')->label('カテゴリ')
                    ->options(InformationCategory::labels()->toArray()),

                SelectFilter::make('os_type')->label('OSタイプ')
                    ->options(OsType::getFormSelectOptions()),

                Promotion::getTagSelectFilter(),

                SelectFilter::make('publication_status')->label('公開ステータス')
                    ->options(PublicationStatus::labels()->toArray())
                    ->query(function (Builder $query, $data) use ($nowForQuery) {
                        if (blank($data['value'])) {
                            return $query;
                        }

                        $publicationStatus = PublicationStatus::tryFrom($data['value']);
                        if (is_null($publicationStatus)) {
                            return $query;
                        }

                        if ($publicationStatus === PublicationStatus::PRIVATE) {
                            return $query->where('enable', 0);
                        }

                        $query->where('enable', 1);
                        return match ($publicationStatus) {
                            PublicationStatus::BEFORE_PUB => $query->where('pre_notice_start_at', '>', $nowForQuery),
                            PublicationStatus::ANNOUNCING => $query->where('pre_notice_start_at', '<=', $nowForQuery)->where('start_at', '>', $nowForQuery),
                            PublicationStatus::PUBLISHING => $query->where('start_at', '<=', $nowForQuery)->where('end_at', '>=', $nowForQuery),
                            PublicationStatus::POST_PUB => $query->where('end_at', '<', $nowForQuery)->where('post_notice_end_at', '>=', $nowForQuery),
                            PublicationStatus::ENDED => $query->where('post_notice_end_at', '<', $nowForQuery),
                            default => $query,
                        };
                    }),

                SelectFilter::make('has_banner')->label('バナー有無')
                    ->options([
                        '1' => 'あり',
                        '0' => 'なし',
                    ])
                    ->query(function (Builder $query, $data) {
                        if (blank($data['value'])) {
                            return $query;
                        }

                        return $data['value'] === '1'
                            ? $query->whereNotNull('banner_url')
                            : $query->whereNull('banner_url');
                    }),

                Filter::make('check_at')
                    ->form([
                        DatePicker::make('check_at')
                            ->label('指定日に掲載中のお知らせを確認')
                    ])
                    ->query(function (Builder $query, array $data) use ($clock): Builder {
                        if (blank($data['check_at'])) {
                            return $query;
                        }

                        $checkAtForQuery = $clock->parseAndApplyTimezoneForQuery($data['check_at']);

                        return $query->where('start_at', '<=', $checkAtForQuery)
                            ->where('end_at', '>=', $checkAtForQuery);
                    }),
            ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn(Action $action) => $action
                    ->label('適用'),
            )
            ->headerActions(
                Promotion::getHeaderActions(
                    AdmPromotionTagFunctionName::INFORMATION,
                    function (string $environment, string $admPromotionTagId) use ($admInformationService) {
                        $admInformationService->import(
                            $environment,
                            $admPromotionTagId,
                        );
                    }
                ),
            )
            ->actions(self::getActions(), position: ActionsPosition::BeforeColumns);
    }

    private static function getActions(): array
    {
        $isPromotionDestinationEnvironment = Promotion::isPromotionDestinationEnvironment();

        /** @var AdmInformationService $admInformationService */
        $admInformationService = app(AdmInformationService::class);

        return [
            Tables\Actions\ViewAction::make()
                ->label('プレビュー'),
            Tables\Actions\EditAction::make()
                ->visible(!$isPromotionDestinationEnvironment),
            Tables\Actions\DeleteAction::make()
                ->visible(!$isPromotionDestinationEnvironment)
                ->using(function (AdmInformation $admInformation) use ($admInformationService) {
                    $admInformationService->deleteInformation($admInformation);
                }),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdmInformation::route('/'),
            'create' => Pages\CreateAdmInformation::route('/create'),
            'edit' => Pages\EditAdmInformation::route('/{record}/edit'),
            'view' => Pages\ViewAdmInformation::route('/{record}'),
        ];
    }
}
