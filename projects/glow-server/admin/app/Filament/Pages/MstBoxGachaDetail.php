<?php

namespace App\Filament\Pages;

use App\Constants\BoxGachaLoopType;
use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Filament\Resources\MstBoxGachaResource;
use App\Models\Mst\MstBoxGacha;
use App\Traits\RewardInfoGetTrait;
use App\Utils\StringUtil;

class MstBoxGachaDetail extends MstDetailBasePage
{
    use RewardInfoGetTrait;
    protected static string $view = 'filament.pages.mst-box-gacha-detail';

    protected static ?string $title = 'BOXガシャ詳細';

    public string $mstBoxGachaId = '';

    protected $queryString = [
        'mstBoxGachaId',
    ];

    protected function getResourceClass(): ?string
    {
        return MstBoxGachaResource::class;
    }

    protected function getMstModelByQuery(): ?MstBoxGacha
    {
        return MstBoxGacha::query()->where('id', $this->mstBoxGachaId)?->first();
    }

    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return sprintf('BOXガシャID: %s', $this->mstBoxGachaId);
    }

    protected function getSubTitle(): string
    {
        $mstModel = $this->getMstModel();
        if ($mstModel === null) {
            return '';
        }

        return StringUtil::makeIdNameViewString(
            $this->mstBoxGachaId,
            $mstModel->getEventName(),
        );
    }

    public function getBasicInfo(): array
    {
        $mstBoxGacha = $this->getMstModel();
        if ($mstBoxGacha === null) {
            return [];
        }

        // コストアイテム情報を取得
        $costInfo = null;
        $costDtos = $mstBoxGacha->getCostDtos();
        if ($costDtos->isNotEmpty()) {
            $costInfos = $this->getRewardInfos($costDtos);
            $costInfo = $costInfos->first();
        }

        return [
            'BOXガシャID' => $mstBoxGacha->id,
            'イベントID' => $mstBoxGacha->mst_event_id,
            'イベント名' => $mstBoxGacha->getEventName(),
            'コストアイテム' => $costInfo ?? $mstBoxGacha->cost_id,
            'ループタイプ' => BoxGachaLoopType::toLabelWithDescription($mstBoxGacha->loop_type),
            'BOX数' => $mstBoxGacha->mst_box_gacha_groups()->count(),
        ];
    }

    public function getGroupTableRows(): array
    {
        $mstBoxGacha = $this->getMstModel();
        if ($mstBoxGacha === null) {
            return [];
        }

        $mstBoxGachaGroups = $mstBoxGacha->mst_box_gacha_groups()
            ->with('mst_box_gacha_prizes')
            ->get();

        // 全賞品のRewardDtoを一括収集
        $allRewardDtos = collect();
        foreach ($mstBoxGachaGroups as $group) {
            foreach ($group->mst_box_gacha_prizes as $prize) {
                $allRewardDtos->push($prize->getRewardDto());
            }
        }

        // RewardInfoを一括取得
        $rewardInfos = $allRewardDtos->isNotEmpty()
            ? $this->getRewardInfos($allRewardDtos)
            : collect();

        $result = [];
        foreach ($mstBoxGachaGroups as $group) {
            $prizes = [];
            foreach ($group->mst_box_gacha_prizes as $prize) {
                $rewardInfo = $rewardInfos->get($prize->id);
                $prizes[] = [
                    '賞品ID' => $prize->id,
                    '報酬' => $rewardInfo ?? ($prize->resource_id ?? $prize->resource_type),
                    '在庫数' => $prize->stock,
                    'ピックアップ' => $prize->is_pickup ? '✓' : '',
                ];
            }

            $totalStock = $group->mst_box_gacha_prizes->sum('stock');

            $result[] = [
                'title' => sprintf('BOXレベル %d (賞品数: %d, 総在庫: %d)', $group->box_level, count($prizes), $totalStock),
                'prizes' => $prizes,
            ];
        }

        return $result;
    }
}
