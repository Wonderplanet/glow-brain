<?php

declare(strict_types=1);

namespace App\Traits;

use App\Constants\RewardType;
use App\Models\Mst\MstEmblem;
use App\Models\Mst\MstItem;
use App\Models\Mst\MstUnit;
use App\Services\Reward\RewardInfoGetHandleService;
use App\Utils\StringUtil;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

trait RewardInfoGetTrait
{
    /**
     * @return Collection<string, \App\Entities\RewardInfo>
     */
    public static function getRewardInfos(Collection $rewardDtos): Collection
    {
        /** @var RewardInfoGetHandleService $rewardInfoGetHandleService */
        $rewardInfoGetHandleService = app(RewardInfoGetHandleService::class);
        return $rewardInfoGetHandleService->build($rewardDtos)->getRewardInfos();
    }

    /**
     * テーブル表示のページネーションで取得されるレコードデータに、報酬情報を追加する。
     *
     * ページネートで取得したレコードの報酬情報だけを効率的に取得できるようにするため。
     *
     * @param string $rewardKey モデルクラスで報酬情報を取得する際の属性名を指定する
     * @param string $rewardInfoKey テーブル表示時に報酬情報を識別するための属性名を指定する
     */
    public function addRewardInfoToPaginatedRecords(
        Paginator $paginator,
        string $rewardKey = 'reward',
        string $rewardInfoKey = 'reward_info',
    ): void {
        $collection = $paginator->getCollection();

        $rewardDtos = $collection->map(function ($record) use ($rewardKey) {
            return $record->{$rewardKey};
        });

        $rewardInfos = $this->getRewardInfos($rewardDtos);

        $collection = $collection->map(function ($record) use ($rewardInfos, $rewardInfoKey) {
            $record->{$rewardInfoKey} = $rewardInfos->get($record->id);
            return $record;
        });

        $paginator->setCollection($collection);
    }

    /**
     * 複数の報酬情報をページネーションレコードに追加する汎用メソッド
     *
     * 各レコードが複数のRewardDtoを持つ場合に使用する
     *
     * @param Paginator $paginator
     * @param string $rewardDtosMethodName レコードから複数のRewardDtoを取得するメソッド名（例: 'getRewardsDtos', 'getCostsDtos'）
     * @param string $rewardInfoKey テーブル表示時に報酬情報を識別するための属性名（例: 'reward_info', 'cost_info'）
     */
    public function addMultipleRewardInfosToPaginatedRecords(
        Paginator $paginator,
        string $rewardDtosMethodName,
        string $rewardInfoKey = 'reward_info',
    ): void {
        $collection = $paginator->getCollection();

        // 全レコードの全報酬/コストを収集
        $allRewardDtos = collect();
        foreach ($collection as $record) {
            $rewardDtos = $record->{$rewardDtosMethodName}();
            $allRewardDtos = $allRewardDtos->merge($rewardDtos);
        }

        // 報酬情報を一括取得
        $rewardInfos = $this->getRewardInfos($allRewardDtos);

        // 各レコードに報酬情報を設定
        $collection = $collection->map(function ($record) use ($rewardInfos, $rewardDtosMethodName, $rewardInfoKey) {
            $rewardDtos = $record->{$rewardDtosMethodName}();
            $recordRewardInfos = collect();

            foreach ($rewardDtos as $rewardDto) {
                $rewardInfo = $rewardInfos->get($rewardDto->getId());
                if ($rewardInfo) {
                    $recordRewardInfos->push($rewardInfo);
                }
            }

            $record->{$rewardInfoKey} = $recordRewardInfos;
            return $record;
        });

        $paginator->setCollection($collection);
    }

    public function getSelectableRewardFormOptions(RewardType $rewardType): array
    {
        switch ($rewardType) {
            case RewardType::UNIT:
                $units = MstUnit::all();
                return $units->mapWithKeys(function (MstUnit $row) {
                    $name = $row?->mst_unit_i18n?->name ?? '';
                    $label = "[$row->id] $name";
                    return [$row->id => $label];
                })->toArray();
            case RewardType::ITEM:
                $items = MstItem::all();
                return $items->mapWithKeys(function (MstItem $row) {
                    $name = $row?->mst_item_i18n?->name ?? '';
                    $label = "[$row->id] $name";
                    return [$row->id => $label];
                })->toArray();
            case RewardType::EMBLEM:
                $emblems = MstEmblem::all();
                return $emblems->mapWithKeys(function (MstEmblem $row) {
                    $name = $row?->mst_emblem_i18n?->name ?? '';
                    $label = "[$row->id] $name";
                    return [$row->id => $label];
                })->toArray();
            default:
                return [];
        }
    }

    /**
     * 選択した配布アイテムタイプを元に、配布アイテムIDのオプションを生成する
     * @param string|null $distributionType
     *
     * @return array<int|string, string>
     */
    private static function getRewardResourceIds(?string $rewardType): array
    {
        switch ($rewardType) {
            case RewardType::UNIT->value:
                // mst_unitsからオプションデータを生成
                return MstUnit::all()->mapWithKeys(function (MstUnit $row) {
                    return [
                        $row->id => StringUtil::makeIdNameViewString(
                            $row->id,
                            $row->mst_unit_i18n?->name ?? ''
                        ),
                    ];
                })->toArray();
            case RewardType::ITEM->value:
                return MstItem::all()->mapWithKeys(function (MstItem $row) {
                    return [
                        $row->id => StringUtil::makeIdNameViewString(
                            $row->id,
                            $row->mst_item_i18n?->name ?? ''
                        ),
                    ];
                })->toArray();
            case RewardType::EMBLEM->value:
                return MstEmblem::all()->mapWithKeys(function (MstEmblem $row) {
                    return [
                        $row->id => StringUtil::makeIdNameViewString(
                            $row->id,
                            $row->mst_emblem_i18n?->name ?? ''
                        ),
                    ];
                })->toArray();
            default:
                return [];
        }
    }

    /**
     * 配布報酬設定フォームのスキーマ配列
     *
     * @param array<string, string> $selectableRewardTypes 機能ごとに配布可能な報酬タイプを指定する
     *   key:報酬タイプ, value:表示名
     * @return array<Select|TextInput>
     */
    private static function getSendRewardSchema(
        array $selectableRewardTypes,
        string $resourceTypeFieldName = 'resource_type',
        string $resourceIdFieldName = 'resource_id',
        string $resourceAmountFieldName = 'resource_amount'
    ): array {
        return [
                Select::make($resourceTypeFieldName)
                    ->label('報酬タイプ')
                    ->placeholder('報酬タイプを選択')
                    ->options(fn () => $selectableRewardTypes)
                    ->reactive(),
                Select::make($resourceIdFieldName)
                    ->label('リソースID')
                    ->placeholder('IDを選択')
                    ->searchable()
                    ->disabled(function (callable $get) use ($resourceTypeFieldName) {
                        return self::disableResourceIdRewardType($get($resourceTypeFieldName));
                    })
                    ->options(function (callable $get) use ($resourceTypeFieldName) {
                        return self::getRewardResourceIds($get($resourceTypeFieldName));
                    })
                    ->reactive(),
                TextInput::make($resourceAmountFieldName)
                    ->label('個数')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->reactive(),
        ];
    }

    private static function disableResourceIdRewardType(?string $rewardType): bool
    {
        if ($rewardType === null) {
            return true;
        }

        $enum = RewardType::tryFrom($rewardType);
        if ($enum === null) {
            return false;
        }
        return !$enum->hasResourceId();
    }
}
