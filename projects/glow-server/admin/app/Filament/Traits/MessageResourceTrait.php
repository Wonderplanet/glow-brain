<?php

namespace App\Filament\Traits;

use App\Constants\AdmPromotionTagFunctionName;
use App\Facades\Promotion;
use App\Filament\Tables\Filters\DateTimePeriodFilter;
use App\Models\Adm\Base\BaseAdmMessageDistributionInput;
use App\Models\Adm\Enums\AdmMessageCreateStatuses;
use App\Models\Adm\Enums\AdmMessageTargetIdInputTypes;
use App\Services\MessageDistributionService;
use Carbon\CarbonImmutable;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

trait MessageResourceTrait
{
    public static function getTable(Table $table, bool $isAll): Table
    {
        $headingHtml = $isAll
            ? '<p style="color: red; font-weight: bold;">※全体配布専用です<br>'
            . '※特定ユーザーに配布したい場合は「個別メッセージ配布」を使用してください</p>'
            : '<p style="color: red; font-weight: bold;">※個別配布専用です<br>'
            . '※ユーザー全体に配布したい場合は「全体メッセージ配布」を使用してください</p>';

        $displayTargetIdInputTypeOptions = [
            AdmMessageTargetIdInputTypes::All->value => '全体',
            AdmMessageTargetIdInputTypes::Input->value => '特定ユーザー',
            AdmMessageTargetIdInputTypes::Csv->value => '特定ユーザー(CSV)',
        ];
        if (!$isAll) {
            unset($displayTargetIdInputTypeOptions[AdmMessageTargetIdInputTypes::All->value]);
        }

        $nowUtc = CarbonImmutable::now();
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('create_status')
                    ->label('状態')
                    ->formatStateUsing(function (BaseAdmMessageDistributionInput $record) use ($nowUtc) {
                        $status = '';
                        switch ($record->create_status) {
                            case AdmMessageCreateStatuses::Editing:
                                $status = new HtmlString("<span style='color: blue'>作成中</span>");
                                break;
                            case AdmMessageCreateStatuses::Pending:
                                $status = new HtmlString("<span style='color: green'>申請中</span>");
                                break;
                            case AdmMessageCreateStatuses::Approved:
                                $status = new HtmlString("<span style='color: red'>配布中</span>");
                                if ($record->start_at > $nowUtc) {
                                    // 配布開始日時が過ぎてなければ配布待ちにする
                                    $status = new HtmlString("<span style='color: orange'>配布待ち</span>");
                                } elseif ($record->expired_at < $nowUtc) {
                                    // 配布終了日時が過ぎていれば配布終了にする
                                    $status = new HtmlString("<span style='color: gray'>配布終了</span>");

                                }
                                break;
                            default:
                                $status = '不明';
                                break;
                        }
                        return $status;
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('id')
                    ->label('Id')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('配布タイトル')
                    ->searchable(query: function ($query, $search) {
                        return $query->where('title', 'LIKE', "%{$search}%");
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('adm_promotion_tag_id')
                    ->label('昇格タグID')
                    ->searchable()
                    ->sortable()
                    ->visible($isAll),
                Tables\Columns\TextColumn::make('display_target_id_input_type')
                    ->label('配布対象')
                    ->formatStateUsing(function ($record) {
                        return match ($record->display_target_id_input_type) {
                            AdmMessageTargetIdInputTypes::All->value => '全体',
                            AdmMessageTargetIdInputTypes::Input->value => '特定ユーザー',
                            AdmMessageTargetIdInputTypes::Csv->value => '特定ユーザー(CSV)',
                            default => '不明',
                        };
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_at')
                    ->label('配布開始日時')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expired_at')
                    ->label('配布終了日時')
                    ->sortable(),
            ])
            ->heading(new HtmlString(
                $headingHtml
                . 'タイトルは部分一致で検索できます。検索フォームから検索を行ってください<br/>'
                . '配布対象、状態はフィルタでの絞り込みを行ってください'
            ))
            ->defaultSort('start_at', 'desc')
            ->deferFilters()
            ->hiddenFilterIndicators()
            ->filtersApplyAction(
                fn (Action $action) => $action
                    ->label('適用'),
            )
            ->filters(
                array_merge(
                    [
                        DateTimePeriodFilter::make('custom_period')
                            ->setFromColumnName('start_at')
                            ->setToColumnName('expired_at')
                            ->label('配布期間中'),
                        Tables\Filters\SelectFilter::make('display_target_id_input_type')
                            ->label('配布対象')
                            ->multiple()
                            ->options($displayTargetIdInputTypeOptions),
                        Tables\Filters\SelectFilter::make('create_status')
                            ->label('状態')
                            ->multiple()
                            ->options([
                                AdmMessageCreateStatuses::Editing->value => '作成中',
                                AdmMessageCreateStatuses::Pending->value => '申請中',
                                // TODO 「配布待ち」は「配布中かつ配布開始日時が未来」という状態を指しており、フィルタリング設定が難しく配布中と一緒に表示している(仕様相談済み)
                                //  簡単にフィルタリングできる方法があれば対応したい
                                AdmMessageCreateStatuses::Approved->value => '配布中or配布待ち',
                            ]),
                    ],
                    $isAll ? [Promotion::getTagSelectFilter()] : []
                )
                , FiltersLayout::AboveContent
            )
            ->headerActions(
                $isAll
                    ? Promotion::getHeaderActions(
                        AdmPromotionTagFunctionName::MESSAGE_DISTRIBUTION,
                        function (string $environment, string $admPromotionTagId) {
                            $messageDistributionService = app(MessageDistributionService::class);
                            $messageDistributionService->import(
                                $environment,
                                $admPromotionTagId,
                            );
                        }
                    )
                    : [],
            )
            ->actions(self::getActions($isAll), position: ActionsPosition::BeforeColumns)
            // 行のリンク化をさせないように制御
            ->recordUrl(null);
    }

    public static function getActions(bool $isAll): array
    {
        if ($isAll && Promotion::isPromotionDestinationEnvironment()) {
            return [
                Tables\Actions\EditAction::make()->label('配布')
                    ->visible(function (BaseAdmMessageDistributionInput $record) {
                        return $record->create_status === AdmMessageCreateStatuses::Pending;
                    }),
            ];
        }

        return [
            Tables\Actions\EditAction::make(),
        ];
    }
}
