<?php

declare(strict_types=1);

namespace App\Services;
use App\Constants\AdmPromotionTagFunctionName;
use App\Constants\JumpPlusRewardConstant;
use App\Entities\JumpPlusRewardPromotionEntity;
use App\Models\Mng\MngJumpPlusReward;
use App\Models\Mng\MngJumpPlusRewardSchedule;
use App\Operators\LambdaOperator;
use App\Operators\LocalFileOperator;
use App\Operators\S3Operator;
use App\Traits\MngCacheDeleteTrait;
use App\Traits\NotificationTrait;
use App\Utils\StringUtil;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\Admin\Services\SendApiService;

class JumpPlusRewardService
{
    use NotificationTrait;
    use MngCacheDeleteTrait;

    public function __construct(
        private ConfigGetService $configGetService,
        private S3Operator $s3Operator,
        private SendApiService $sendApiService,
        private LocalFileOperator $localFileOperator,
        private LambdaOperator $lambdaOperator,
    ) {
    }

    /**
     * @param MngJumpPlusRewardSchedule|null $mngSchedule null: 新規作成, not null: 更新
     * @param array<mixed> $data フォームデータ
     * @return MngJumpPlusRewardSchedule
     */
    public function createOrUpdateMngJumpPlusRewardSchedule(
        ?MngJumpPlusRewardSchedule $mngSchedule,
        array $data,
    ): MngJumpPlusRewardSchedule {
        if ($mngSchedule === null) {
            $mngSchedule = new MngJumpPlusRewardSchedule();
            $mngSchedule->id = $data['id'] ?? StringUtil::generateUniqueId();
        }

        $groupId = $mngSchedule->id; // スケジュールごとに報酬設定を別々にするため

        $mngSchedule->adm_promotion_tag_id = $data['adm_promotion_tag_id'] ?? null; // 昇格タグIDを設定
        $mngSchedule->group_id = $groupId;
        $mngSchedule->start_at = $data['start_at'];
        $mngSchedule->end_at = $data['end_at'];
        $mngSchedule->save();

        // 報酬データを保存
        $mngRewards = collect();
        if (isset($data['rewards']) && is_array($data['rewards'])) {
            foreach ($data['rewards'] as $rewardData) {
                if (!isset($rewardData['resource_type'])) {
                    continue; // resource_typeがない場合はスキップ
                }
                $mngReward = new MngJumpPlusReward();
                $mngReward->id = $rewardData['id'] ?? StringUtil::generateUniqueId();
                $mngReward->group_id = $groupId;
                $mngReward->resource_type = $rewardData['resource_type'];
                $mngReward->resource_id = $rewardData['resource_id'] ?? null;
                $mngReward->resource_amount = $rewardData['resource_amount'] ?? 0;

                $mngRewards->put($mngReward->id, $mngReward);
            }
        }

        if ($mngRewards->isNotEmpty()) {
            MngJumpPlusReward::where('group_id', $groupId)->delete();

            MngJumpPlusReward::insert(
                $mngRewards->map(function (MngJumpPlusReward $mngJumpPlusReward) {
                    return $mngJumpPlusReward->formatToInsertArray();
                })->all(),
            );
        }

        return $mngSchedule;
    }

    public function deleteMngJumpPlusRewardSchedule(MngJumpPlusRewardSchedule $mngSchedule): void
    {
        // スケジュールに紐づく報酬を削除
        MngJumpPlusReward::where('group_id', $mngSchedule->group_id)->delete();

        // スケジュール自体を削除
        $mngSchedule->delete();

        // キャッシュ削除
        $this->deleteMngJumpPlusRewardCache();

        // s3に最新のマスタjsonをアップロード
        $jumpPlusRewardService = app(JumpPlusRewardService::class);
        $jumpPlusRewardService->createAndUploadValidationMaster();
    }

    /**
     * 環境とタグの指定で、ジャンプ+連携報酬データを環境間コピーする
     *
     * @param string $environment コピー元環境名
     */
    public function import(string $environment, string $admPromotionTagId): void
    {
        $functionLabel = AdmPromotionTagFunctionName::JUMP_PLUS_REWARD->label();

        try {
            // DBテーブルデータをコピー
            $jumpPlusRewardPromotionEntity = $this->importMngJumpPlusRewards($environment, $admPromotionTagId);
            if ($jumpPlusRewardPromotionEntity === null || $jumpPlusRewardPromotionEntity->isEmpty()) {
                Log::info('コピーするジャンプ+連携報酬のデータがありませんでした', [
                    'environment' => $environment,
                    'admPromotionTagId' => $admPromotionTagId,
                ]);

                $this->sendProcessCompletedNotification(
                    'コピーするジャンプ+連携報酬のデータがありませんでした',
                    "コピー元環境: {$environment}, タグ: {$admPromotionTagId}",
                );

                return;
            }

            Log::info("{$functionLabel}のコピーが完了しました", [
                'environment' => $environment,
                'admPromotionTagId' => $admPromotionTagId,
            ]);

            $this->sendProcessCompletedNotification(
                "{$functionLabel}のコピーが完了しました",
                "コピー元環境: {$environment}, タグ: {$admPromotionTagId}",
            );
        } catch (\Exception $e) {
            Log::error("{$functionLabel}のコピーに失敗しました", [
                'environment' => $environment,
                'admPromotionTagId' => $admPromotionTagId,
                'exception' => $e->getMessage(),
            ]);
            $this->sendDangerNotification(
                "{$functionLabel}のコピーに失敗しました",
                "コピー元環境: {$environment}, タグ: {$admPromotionTagId}, " . $e->getMessage(),
            );
            throw $e;
        }
    }

    public function getJumpPlusRewardPromotionData(string $admPromotionTagId): array
    {
        $mngJumpPlusRewardSchedules = MngJumpPlusRewardSchedule::query()
            ->where('adm_promotion_tag_id', $admPromotionTagId)
            ->get();

        if (empty($mngJumpPlusRewardSchedules)) {
            return [];
        }

        $mngJumpPlusRewards = collect();
        foreach ($mngJumpPlusRewardSchedules as $mngJumpPlusRewardSchedule) {
            $rewards = $mngJumpPlusRewardSchedule->mng_jump_plus_rewards;
            if ($rewards === null) {
                continue;
            }
            foreach ($rewards as $reward) {
                /** @var MngJumpPlusReward $reward */
                $mngJumpPlusRewards->put($reward->id, $reward);
            }
        }

        $jumpPlusRewardPromotionEntity = new JumpPlusRewardPromotionEntity(
            $mngJumpPlusRewardSchedules,
            $mngJumpPlusRewards,
        );

        return $jumpPlusRewardPromotionEntity->formatToResponse();
    }

    public function getJumpPlusRewardPromotionDataFromEnvironment(
        string $environment,
        string $admPromotionTagId,
    ): ?JumpPlusRewardPromotionEntity {
        $domain = $this->configGetService->getAdminApiDomain($environment);
        if ($domain === null) {
            return null;
        }

        $endPoint = "get-jumpplusreward-promotion-data/$admPromotionTagId";
        $response = $this->sendApiService->sendApiRequest($domain, $endPoint);

        return JumpPlusRewardPromotionEntity::createFromResponseArray($response);
    }

    private function importMngJumpPlusRewards(string $environment, string $admPromotionTagId): ?JumpPlusRewardPromotionEntity
    {
        $jumpPlusRewardPromotionEntity = $this->getJumpPlusRewardPromotionDataFromEnvironment($environment, $admPromotionTagId);
        if ($jumpPlusRewardPromotionEntity === null || $jumpPlusRewardPromotionEntity->isEmpty()) {
            return null;
        }

        $mngJumpPlusRewardSchedules = $jumpPlusRewardPromotionEntity->getMngJumpPlusRewardSchedules();
        $groupIds = collect();
        if ($mngJumpPlusRewardSchedules->isNotEmpty()) {
            MngJumpPlusRewardSchedule::upsert(
                $mngJumpPlusRewardSchedules->map(function (MngJumpPlusRewardSchedule $mngJumpPlusRewardSchedule) {
                    return $mngJumpPlusRewardSchedule->formatToInsertArray();
                })->all(),
                ['id'],
            );

            $groupIds = $mngJumpPlusRewardSchedules->pluck('group_id');
        }

        $mngJumpPlusRewards = $jumpPlusRewardPromotionEntity->getMngJumpPlusRewards();

        if ($mngJumpPlusRewards->isNotEmpty()) {
            // groupIdにスケジュールIdを使用しているので、他のスケジュールの報酬を削除してしまうことはない前提で削除
            MngJumpPlusReward::whereIn('group_id', $groupIds)->delete();

            MngJumpPlusReward::insert(
                $mngJumpPlusRewards->map(function (MngJumpPlusReward $mngJumpPlusReward) {
                    return $mngJumpPlusReward->formatToInsertArray();
                })->all(),
            );
        }

        // キャッシュ削除
        $this->deleteMngJumpPlusRewardCache();

        // S3に最新のマスタjsonをアップロード
        $this->createAndUploadValidationMaster();

        return $jumpPlusRewardPromotionEntity;
    }

    /**
     * S3上にアップロードするマスタjsonファイル名を生成する
     * @param string $admPromotionTagId
     * @return string
     */
    private function getS3RewardMasterJsonFileName(): string
    {
        $timestamp = CarbonImmutable::now()->format('YmdHis');
        return sprintf(
            JumpPlusRewardConstant::S3_OBJECT_REWARD_MASTER_JSON_FILE_NAME_FORMAT,
            $timestamp,
        );
    }

    /**
     * Lambdaで実装したジャンプ+連携報酬登録APIにてリクエストパラメータの報酬IDが有効なマスタデータのIDかをチェックするために
     * S3のmasterバケットにjsonファイルとしてマスタデータをアップロードする
     */
    public function createAndUploadValidationMaster(): void {
        $fileName = $this->getS3RewardMasterJsonFileName();

        $mngJumpPlusRewardSchedules = MngJumpPlusRewardSchedule::all();
        $encodedJson = $this->makeValidationMasterJson($mngJumpPlusRewardSchedules);

        // ローカルに保存
        $localFilePath = StringUtil::joinPath(
            $this->configGetService->getAdminJumpPlusRewardDir(),
            $fileName,
        );
        $this->localFileOperator->putWithCreateDir($localFilePath, $encodedJson);

        // S3のmasterバケットにアップロード
        $this->s3Operator->putFromFile(
            $localFilePath,
            $fileName,
            $this->configGetService->getS3MasterBucket()
        );

        // Lambdaの環境変数にファイル名をセットして、変更を反映するために再デプロイ
        $this->lambdaOperator->updateEnvironmentVariableAndRedeploy(
            $this->configGetService->getLambdaJumpPlusReward(),
            JumpPlusRewardConstant::LAMBDA_FUNCTION_ENV_REWARD_MASTER_JSON_PATH,
            $fileName,
        );
    }

    /**
     * ジャンプ+連携報酬マスタデータをバリデーション用のJSON形式に変換
     * @param Collection $mngJumpPlusRewardSchedules
     * @return string encodeされたJSON文字列
     * @throws \RuntimeException
     */
    private function makeValidationMasterJson(Collection $mngJumpPlusRewardSchedules): string
    {
        $validationMaster = $mngJumpPlusRewardSchedules->map(function (MngJumpPlusRewardSchedule $schedule) {
            return $schedule->formatToValidationMaster();
        })->values()->all();

        $json = json_encode(
            [
                // lambda関数上のコードでこのキーを参照している
                'oprJumpPlusRewardSchedule' => $validationMaster,
            ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE,
        );

        if ($json === false) {
            Log::error('JSONエンコードに失敗しました', [
                'error' => json_last_error_msg(),
            ]);
            throw new \RuntimeException('ジャンプ+連携報酬マスタデータのJSONエンコードに失敗しました: ' . json_last_error_msg());
        }

        return $json;
    }
}
