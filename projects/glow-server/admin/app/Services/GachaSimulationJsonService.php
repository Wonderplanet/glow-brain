<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Common\Entities\Clock;
use App\Services\ConfigGetService;
use App\Operators\S3Operator;
use App\Models\Mst\OprGacha;
use App\Models\Mst\OprGachaPrize;
use App\Models\Mst\MstUnit;
use App\Models\Mst\MstItem;
use App\Models\Adm\AdmGachaSimulationLog;
use App\Constants\RarityType;
use App\Services\GachaSimulatorService;
use Illuminate\Support\Facades\Log;

class GachaSimulationJsonService
{

    // /**
    //  * シミュレーションの結果をJsonに変換しS3にアップロード
    //  */
    // public function drawResultJson($oprGachaId, $drawResults)
    // {

    //     $s3DiskName = 's3';
    //     /** @var ConfigGetService $configGetService */
    //     $configGetService = app()->make(ConfigGetService::class);
    //     $appId = $configGetService->getAdminAppId();

    //     /** @var Clock $clock */
    //     $clock = app()->make(Clock::class);
    //     /** @var S3Operator $s3Operator */
    //     $s3Operator = app()->make(S3Operator::class);
    //     $targetDate = $clock->now()->subDay();

    //     $results = [];
    //     foreach ($drawResults as $drawnItem) {
    //         $result = [
    //             'app_id' => $appId,
    //             'gasha_id' => $oprGachaId,
    //             // BOXガシャの場合は変更が必要
    //             'is_full_box' => 0,
    //             'exec_id' => $oprGachaId,
    //             'timestamp' => $targetDate,
    //             'user_id' => auth()->id(),
    //             'item_id' => $drawnItem->getResourceId(),
    //             'item_num' => $drawnItem->getResourceAmount(),
    //             'promised' => '',
    //             'selected_sp' => []
    //         ];
    //         $results[$oprGachaId][] = json_encode($result);
    //     }
    //     $formattedDate = $targetDate->format('Ymd');
    //     $identifier = 1;
    //     // TODO: ここでoprGachaId複数想定になっている意図がわからない。意味なくただコードの複雑度を上げてるだけになっていそう
    //     foreach ($results as $oprGachaId => $jsonList) {
    //         $content = implode(",\n", $jsonList);
    //         $s3Operator->put($s3DiskName, config('admin.gachaBucketDir') . "$appId/$oprGachaId/logs/$formattedDate/$identifier.json.gz", gzencode($content));
    //         $identifier++;
    //     }
    // }

    // /**
    //  * TODO: そもそもこれいらないのではないか
    //  * マスタの情報をjsonに変換しS3にアップロード
    //  * @param string $oprGachaId ガチャID
    //  */
    // public function generateMstGachaJson(string $oprGachaId)
    // {
    //     $s3DiskName = 's3';

    //     /** @var ConfigGetService $configGetService */
    //     $configGetService = app()->make(ConfigGetService::class);
    //     $gachaSimulatorService = app(GachaSimulatorService::class);
    //     $appId = $configGetService->getAdminAppId();
    //     $appName = $configGetService->getAdminAppName();

    //     /** @var S3Operator $s3Operator */
    //     $s3Operator = app()->make(S3Operator::class);

    //     /** @var Clock $clock */
    //     $clock = app()->make(Clock::class);
    //     $targetDate = $clock->now()->subDay();

    //     //対象のガシャ
    //     $oprGacha = OprGacha::query()
    //         ->with([
    //             'opr_gacha_i18n',
    //             'opr_gacha_uppers'
    //         ])
    //         ->where('id', $oprGachaId)
    //         ->first();

    //     $oprGachaPrizes = OprGachaPrize::query()
    //         ->where('group_id', $oprGacha->prize_group_id)
    //         ->orderBy('id', 'asc')
    //         ->get();

    //     // TODO: リファクタ。リソース追加されたらバグの温床になる
    //     $units = $oprGachaPrizes->filter(function ($value) {
    //         return $value->resource_type->value == 'Unit'; // TODO: Enum化
    //     })->pluck('resource_id')->toArray();

    //     $items = $oprGachaPrizes->filter(function ($value) {
    //         return $value->resource_type->value == 'Item'; // TODO: Enum化
    //     })->pluck('resource_id')->toArray();

    //     $mstUnits = MstUnit::query()
    //         ->with('mst_unit_i18n')
    //         ->whereIn('id', $units)
    //         ->get()
    //         ->keyBy('id');

    //     $mstItems = MstItem::query()
    //         ->with('mst_item_i18n')
    //         ->whereIn('id', $items)
    //         ->get()
    //         ->keyBy('id');

    //     $gachaMaster = [];
    //     $totalWeight = $oprGachaPrizes->sum('weight');

    //     // 通常枠
    //     $normalItems = $this->oprGachaPrizesToArray($oprGachaPrizes, $totalWeight);
    //     $sets = [
    //         [
    //             'set_name' => '通常',
    //             'items' => $normalItems,
    //         ]
    //     ];

    //     // 10連確定枠
    //     $multiFixedPrizeDataHash = null;
    //     if ($oprGacha->multi_fixed_prize_count) {

    //         $oprGachaMultiFixedPrize = OprGachaPrize::query()
    //             ->where('group_id', $oprGacha->fixed_prize_group_id)
    //             ->get()
    //             ->sortBy('id');
    //         $multiFixedTotalWeight = $oprGachaMultiFixedPrize->sum('weight');;

    //         $oprGachaPrizes = $oprGachaPrizes->concat($oprGachaMultiFixedPrize)->sortBy('id');

    //         $fixedItems = $this->oprGachaPrizesToArray($oprGachaMultiFixedPrize, $multiFixedTotalWeight);
    //         $sets[] = [
    //             'set_name' => '10連確定',
    //             'items' => $fixedItems,
    //         ];
    //     }

    //     // 天井がある場合は追加
    //     $oprGachaUppers = $oprGacha->opr_gacha_uppers;
    //     if (!empty($oprGachaUppers)) {
    //         foreach ($oprGachaUppers as $oprGachaUpper) {
    //             $upperTable = collect();
    //             foreach ($oprGachaPrizes as $oprGachaPrize) {
    //                 /** @var GachaBoxInterface $gachaTableItem */
    //                 if ($oprGachaPrize->resource_type === 'Unit') { // TODO: Enum化。リファクタしないとやばすぎる
    //                     $rarity = $mstUnits[$oprGachaPrize->resource_id]?->rarity;
    //                 } elseif ($oprGachaPrize->resource_type === 'Item') {
    //                     $rarity = $mstItems[$oprGachaPrize->resource_id]?->rarity;
    //                 } else {
    //                     continue;
    //                 }
    //                 if ($rarity === RarityType::SSR->value) {
    //                     if ($oprGachaUpper->upper_type->value == 'MaxRarity' || ($oprGachaUpper->upper_type->value == 'Pickup' && $oprGachaPrize->pickup == 1)) {
    //                         $upperTable->add($oprGachaPrize);
    //                     }
    //                 }
    //             }
    //             $maxRarityTableTotalWeight = $upperTable->sum(fn($entity) => $entity->getWeight());
    //             $items = $this->oprGachaPrizesToArray($upperTable, $maxRarityTableTotalWeight);
    //             $setName = $oprGachaUpper->upper_type->value ? 'ピックアップ確定' : '最高レア確定';
    //             $sets[] = [
    //                 'set_name' => $setName,
    //                 'items' => $items
    //             ];
    //         }
    //     }

    //     $gachaMaster[$oprGacha->id][] = [
    //         'app_id' => $appId,
    //         'app_name' => $appName,
    //         'gasha_id' => $oprGacha->id,
    //         'gasha_name' => $oprGachas?->opr_gacha_i18n?->name ?? '',
    //         // BOXガチャや選択式ガチャが実装される場合は変更が必要
    //         'gasha_type' => 0,
    //         'gasha_group_id' => '',
    //         'open_time' => $oprGacha->start_at->getTimestamp(),
    //         'close_time' => $oprGacha->end_at->getTimestamp(),
    //         'steps' => [
    //             'sets' => $sets,
    //         ]
    //     ];

    //     $formattedDate = $targetDate->format('Ymd');
    //     foreach ($gachaMaster as $oprGachaId => $master) {
    //         $gachaJson = json_encode($master);
    //         $s3Operator->put($s3DiskName, config('admin.gachaBucketDir') . "$appId/$oprGachaId/gasha_$formattedDate.json", $gachaJson);
    //     }

    //     $mstGachaDataHash = $gachaSimulatorService->mstDataHash($oprGachaPrizes);

    //     // TODO: ここでmst_gacha_data_hashセットしてるーーー
    //     // TODO: mst_gacha_data_hashを作るロジックを切り出すべし
    //     AdmGachaSimulationLog::query()
    //         ->where('opr_gacha_id', $oprGachaId)
    //         ->update([
    //             'mst_gacha_data_hash' => $mstGachaDataHash,
    //         ]);

    //     // アイテムマスタJSONを生成する
    //     $itemMaster = ['app_id' => $appId, 'items' => []];
    //     foreach ($oprGachaPrizes as $oprGachaPrize) {

    //         if ($oprGachaPrize->resource_type->value === 'Unit') {
    //             $name = $mstUnits[$oprGachaPrize->resource_id]->mst_unit_i18n->name;
    //             $rarity = $mstUnits[$oprGachaPrize->resource_id]->rarity;
    //         } else if ($oprGachaPrize->resource_type->value === 'Item') {
    //             $name = $mstItems[$oprGachaPrize->resource_id]->mst_item_i18n->name;
    //             $rarity = $mstItems[$oprGachaPrize->resource_id]->rarity;
    //         } else {
    //             continue;
    //         }

    //         $itemMaster['items'][] = [
    //             'id' => $oprGachaPrize['resource_id'],
    //             'name' => $name,
    //             'rarity' => $rarity,
    //         ];
    //     }

    //     $itemJson = json_encode($itemMaster);
    //     $s3Operator->put($s3DiskName, config('admin.gachaBucketDir') . "$appId/items_$formattedDate.json", $itemJson);
    // }

    // /**
    //  * json出力用の配列に整形する
    //  * @param Collection $oprGachaPrizes
    //  * @param int        $totalWeight
    //  * @return array
    //  */
    // private function oprGachaPrizesToArray($oprGachaPrizes, int $totalWeight): array
    // {
    //     $items = [];
    //     foreach ($oprGachaPrizes as $oprGachaPrize) {
    //         /** @var GachaBoxInterface $oprGachaPrize */
    //         $items[] = [
    //             'id' => $oprGachaPrize->id,
    //             'item_num' => $oprGachaPrize->resource_amount,
    //             // キー名はweightだが、実際はパーセンテージ
    //             'weight' => $this->calcPercentage($oprGachaPrize->weight, $totalWeight),
    //             'weight_sp' => 0,
    //         ];
    //     }
    //     return $items;
    // }

    // /**
    //  * TODO: api側とロジックを揃えないといけないのに、こういう処理作っちゃうのはカオスの始まり
    //  * weightをパーセンテージに変換する
    //  * @param int $weight
    //  * @param int $totalWeight
    //  * @return float
    //  */
    // private function calcPercentage(int $weight, int $totalWeight): float
    // {
    //     if ($totalWeight === 0) {
    //         // 0除算回避
    //         return 0;
    //     }
    //     return $weight / $totalWeight * 100;
    // }
}
