<?php

namespace App\Console\Commands;

use App\Constants\GachaSetName;
use App\Domain\Common\Entities\Clock;
use App\Domain\Gacha\Enums\GachaPrizeType;
use App\Domain\Gacha\Models\LogGacha;
use App\Operators\S3Operator;
use App\Repositories\Log\LogGachaRepository;
use App\Services\AdmGachaLogAggregationProgressService;
use App\Services\ConfigGetService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateGachaLogJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-gacha-log-json {--date= : 実行対象日時 (YYYY-MM-DD format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate gacha log json file for post-analysis of gacha.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // --dateオプションが指定された場合はそれを使用、なければ前日
        $dateOption = $this->option('date');
        if ($dateOption) {
            try {
                $targetDate = CarbonImmutable::parse($dateOption);
            } catch (\Exception $e) {
                $this->error('Invalid date format. Please use YYYY-MM-DD format.');
                Log::error('GenerateGachaLogJson: Invalid date format provided', ['date' => $dateOption]);
                return 1;
            }
        } else {
            /** @var Clock $clock */
            $clock = app()->make(Clock::class);
            $targetDate = $clock->now()->subDay();
        }

        $this->generateGachaLogJson($targetDate);
    }

    private function generateGachaLogJson(CarbonImmutable $targetDate)
    {
        // 1度に取得するログの件数
        $limit = 1000;
        $s3DiskName = 's3_bne';

        /** @var ConfigGetService $configGetService */
        $configGetService = app()->make(ConfigGetService::class);
        $appId = $configGetService->getAdminAppId();

        $pathPrefix = 'data/600';

        /** @var LogGachaRepository $logGachaRepository */
        $logGachaRepository = app()->make(LogGachaRepository::class);

        /** @var S3Operator $s3Operator */
        $s3Operator = app()->make(S3Operator::class);

        $formattedDate = $targetDate->format('Ymd');

        /** @var AdmGachaLogAggregationProgressService $admGachaLogAggregationProgressService */
        $admGachaLogAggregationProgressService = app()->make(AdmGachaLogAggregationProgressService::class);
        $admGachaLogAggregationProgress = $admGachaLogAggregationProgressService->getOrCreate($formattedDate);
        if ($admGachaLogAggregationProgress->isCompleted()) {
            // 既に処理済みの場合は何もしない
            return;
        }

        $offset = $admGachaLogAggregationProgress->getProgress();
        $identifier = 1;
        while (true) {
            $logGachas = $logGachaRepository->getByDateWithPagination($targetDate, $limit, $offset);

            \Log::info("Generate gacha log json. date=$formattedDate, offset=$offset, count=" . count($logGachas));

            $results = [];
            foreach ($logGachas as $logGacha) {
                /** @var LogGacha $logGacha */

                foreach ($logGacha->getResult() as $drawnItem) {
                    $oprGachaId = $logGacha->getOprGachaId();
                    $result = [
                        'app_id' => $appId,
                        'gasha_id' => $oprGachaId,
                        // BOXガシャの場合は変更が必要
                        'is_full_box' => 0,
                        'exec_id' => $logGacha->getId(),
                        // ステップアップガチャでは変更が必要
                        'step_no' => $logGacha->getStepNumber() ? $logGacha->getStepNumber() - 1 : 0,
                        'set_name' => $this->getSetName($drawnItem['prize_type']),
                        'timestamp' => $logGacha->getCreatedAt()->getTimestamp(),
                        'user_id' => $logGacha->getUsrUserId(),
                        'item_id' => $drawnItem['resource_id'],
                        'item_num' => $drawnItem['resource_amount'],
                        'promised' => $this->calcPromised($drawnItem['prize_type']),
                        // 当選アイテム選択式が車の場合は変更が必要
                        'selected_sp' => []
                    ];
                    $results[$oprGachaId][] = json_encode($result);
                }
            }

            foreach ($results as $oprGachaId => $jsonList) {
                $content = implode(",\n", $jsonList);
                $s3Operator->put($s3DiskName, "$pathPrefix/$oprGachaId/logs/$formattedDate/$identifier.json.gz", gzencode($content));
                $identifier++;
            }

            $admGachaLogAggregationProgress->addProgress($logGachas->count());
            $admGachaLogAggregationProgress->save();

            if ($logGachas->count() < $limit) {
                // 取得件数がlimitを下回ったので終了
                break;
            }
            $offset += $limit;
        }
        $admGachaLogAggregationProgress->complete();
        $admGachaLogAggregationProgress->save();
    }

    /**
     * ガチャのセット名を取得
     * @param string $prizeType
     * @return string
     */
    private function getSetName(string $prizeType): string
    {
        return match ($prizeType) {
            GachaPrizeType::REGULAR->value => GachaSetName::REGULAR->value,
            GachaPrizeType::FIXED->value => GachaSetName::FIXED->value,
            GachaPrizeType::MAX_RARITY->value => GachaSetName::MAX_RARITY->value,
            GachaPrizeType::PICKUP->value => GachaSetName::PICKUP->value,
            default => '',
        };
    }

    /**
     * 確定枠かどうか 1:確定枠 0:それ以外
     * @param string $prizeType
     * @return int
     */
    private function calcPromised(string $prizeType): int
    {
        return match ($prizeType) {
            GachaPrizeType::MAX_RARITY->value, GachaPrizeType::PICKUP->value => 1,
            default => 0,
        };
    }
}
