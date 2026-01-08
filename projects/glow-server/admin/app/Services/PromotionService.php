<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\AdmPromotionTagFunctionName;
use App\Entities\TagPromotionEntity;
use App\Models\Adm\AdmPromotionTag;
use App\Traits\NotificationTrait;
use Filament\Forms\Get;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\Admin\Services\SendApiService;

class PromotionService
{
    use NotificationTrait;

    public function __construct(
        private ConfigGetService $configGetService,
        private SendApiService $sendApiService,
    ) {
    }

    /**
     * 昇格先(=昇格元環境の設定がある)の環境かどうか
     *
     * @return bool true: 昇格先の環境, false: 昇格先の環境でない
     */
    public function isPromotionDestinationEnvironment(): bool
    {
        $sourceEnvList = $this->configGetService->getSourceEnvList();

        return count($sourceEnvList) > 0;
    }

    public function getSelectableEnvironmentOptions(): array
    {
        $sourceEnvList = $this->configGetService->getSourceEnvList() ?? [];
        return array_combine($sourceEnvList, array_map(function ($env) {
            return $env;
        }, $sourceEnvList));
    }

    /**
     * @param callable(string $environment, string $admPromotionTagId): void $importCallback
     * @return array<Action|ActionGroup>
     */
    public function getHeaderActions(
        AdmPromotionTagFunctionName $functionName,
        callable $importCallback,
    ): array {
        $isPromotionDestinationEnvironment = $this->isPromotionDestinationEnvironment();

        $functionLabel = $functionName->label();

        return [
            $this->getUpdateTagBulkAction()
                ->visible(!$isPromotionDestinationEnvironment),

            Action::make('import')
                ->label('タグからコピー')
                ->icon('heroicon-s-cloud-arrow-down')
                ->color('info')
                ->visible($isPromotionDestinationEnvironment)
                ->form([
                    \Filament\Forms\Components\Select::make('environment')
                        ->label('コピー元環境')
                        ->options($this->getSelectableEnvironmentOptions())
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $set('adm_promotion_tag_id', null);

                            if (!empty($state)) {
                                $admPromotionTagIds = $this->getPromotionTagFromEnvironment($state);
                                $set('adm_promotion_tag_ids', $admPromotionTagIds);
                            } else {
                                $set('adm_promotion_tag_ids', []);
                            }
                        })
                        ->required(),

                    \Filament\Forms\Components\Select::make('adm_promotion_tag_id')
                        ->label('コピーするタグ')
                        ->options(fn(Get $get) => $get('adm_promotion_tag_ids') ?? [])
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->modalHeading($functionLabel . 'データをコピー')
                ->modalDescription('選択した環境とタグから' . $functionLabel . 'データをコピーします。')
                ->modalSubmitActionLabel('コピー実行')
                ->action(function (array $data) use ($importCallback) {
                    $this->importAdmPromotionTag($data['environment'], $data['adm_promotion_tag_id']);
                    $importCallback($data['environment'], $data['adm_promotion_tag_id']);
                }),
        ];
    }

    public function getTagSelectFilter(): \Filament\Tables\Filters\SelectFilter
    {
        return \Filament\Tables\Filters\SelectFilter::make('adm_promotion_tag_id')
            ->label('昇格タグ')
            ->options(function () {
                return $this->getPromotionTag() + ['NULL' => '未指定'];
            })
            ->searchable()
            ->query(function (Builder $query, $data): Builder {
                if (blank($data['value'])) {
                    return $query;
                }

                if ($data['value'] === 'NULL') {
                    return $query->where(function ($query) {
                        $query->whereNull('adm_promotion_tag_id')
                            ->orWhere('adm_promotion_tag_id', '');
                    });
                }
                return $query->where(
                    'adm_promotion_tag_id',
                    '=',
                    $data['value'],
                );
            });
    }

    public function getTagSelectForm(
        string $name = 'adm_promotion_tag_id',
        string $label = '昇格タグ',
    ): \Filament\Forms\Components\Select {
        return \Filament\Forms\Components\Select::make($name)
            ->label($label)
            ->options(function () {
                return $this->getPromotionTag();
            })
            ->searchable()
            ->preload()
            ->reactive();
    }

    public function getUpdateTagBulkAction(): BulkAction
    {
        return BulkAction::make('bulkUpdateTag')
            ->label('タグを更新')
            ->icon('heroicon-s-pencil-square')
            ->color('primary')
            ->form([
                $this->getTagSelectForm('adm_promotion_tag_id', '更新後のタグ'),
            ])
            ->action(function (Collection $records, array $data) {
                $admPromotionTagId = $data['adm_promotion_tag_id'] ?? null;

                if (empty($admPromotionTagId)) {
                    return;
                }

                foreach ($records as $record) {
                    $record->adm_promotion_tag_id = $admPromotionTagId;
                    $record->save();
                }
                $addedCount = count($records);

                $this->sendProcessCompletedNotification(
                    'タグの更新が完了',
                    $addedCount . '件のタグを更新しました',
                );
            });
    }

    public function getPromotionTag(int $limit = 10): array
    {
        return AdmPromotionTag::getLatestTagOptions($limit);
    }

    public function getPromotionTagFromEnvironment(
        string $environment,
    ): Collection {
        $domain = $this->configGetService->getAdminApiDomain($environment);
        if ($domain === null) {
            return collect([]);
        }

        $response = $this->sendApiService->sendApiRequest($domain, 'get-promotion-tag/');
        return collect($response);
    }

    public function getTagPromotionData(string $admPromotionTagId): array
    {
        $admPromotionTag = AdmPromotionTag::find($admPromotionTagId);

        $tapPromotionEntity = new TagPromotionEntity(
            $admPromotionTag,
        );

        return $tapPromotionEntity->formatToResponse();
    }

    public function getTagPromotionDataFromEnvironment(
        string $environment,
        string $admPromotionTagId,
    ): ?TagPromotionEntity {
        $domain = $this->configGetService->getAdminApiDomain($environment);
        if ($domain === null) {
            return null;
        }

        $response = $this->sendApiService->sendApiRequest(
            $domain,
            'get-tag-promotion-data/' . $admPromotionTagId,
        );

        return TagPromotionEntity::createFromResponseArray($response);
    }

    public function importAdmPromotionTag(
        string $environment,
        string $admPromotionTagId,
    ): void {
        $tagPromotionEntity = $this->getTagPromotionDataFromEnvironment(
            $environment,
            $admPromotionTagId,
        );

        if ($tagPromotionEntity === null) {
            return;
        }

        // adm_promotion_tagsのインポート
        $admPromotionTag = $tagPromotionEntity->getAdmPromotionTag();
        AdmPromotionTag::updateOrCreate(
            ['id' => $admPromotionTag->id],
            $admPromotionTag->toArray(),
        );
    }
}
