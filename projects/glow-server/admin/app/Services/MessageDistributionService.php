<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\MessageDistributionPromotionEntity;
use App\Models\Adm\AdmMessageDistributionInput;
use App\Models\Adm\Enums\AdmMessageCreateStatuses;
use App\Traits\NotificationTrait;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\Admin\Services\SendApiService;

class MessageDistributionService
{
    use NotificationTrait;

    public function __construct(
        private ConfigGetService $configGetService,
        private SendApiService $sendApiService,
    ) {
    }

    /**
     * 環境とタグの指定で、メッセージ配布データを環境間コピーする
     *
     * @param string $environment コピー元環境名
     */
    public function import(string $environment, string $admPromotionTagId): void
    {
        try {
            // DBテーブルデータをコピー
            $messageDistributionPromotionEntity = $this->importAdmMessageDistributionInputs($environment, $admPromotionTagId);
            if ($messageDistributionPromotionEntity === null || $messageDistributionPromotionEntity->isEmpty()) {
                Log::info('コピーするメッセージ配布のデータがありませんでした', [
                    'environment' => $environment,
                    'admPromotionTagId' => $admPromotionTagId,
                ]);

                $this->sendProcessCompletedNotification(
                    'コピーするメッセージ配布のデータがありませんでした',
                    "コピー元環境: {$environment}, タグ: {$admPromotionTagId}",
                );

                // コピー対象がないので終了
                return;
            }

            Log::info('メッセージ配布のコピーが完了しました', [
                'environment' => $environment,
                'admPromotionTagId' => $admPromotionTagId,
            ]);

            $copyCount = $messageDistributionPromotionEntity->getAdmMessageDistributionInputs()->count();
            $this->sendProcessCompletedNotification(
                "メッセージ配布 {$copyCount}件 のコピーが完了しました",
                "コピー元環境: {$environment}, タグ: {$admPromotionTagId}",
            );
        } catch (\Exception $e) {
            Log::error('メッセージ配布のコピーに失敗しました', [
                'environment' => $environment,
                'admPromotionTagId' => $admPromotionTagId,
                'exception' => $e->getMessage(),
            ]);
            $this->sendDangerNotification(
                'メッセージ配布のコピーに失敗しました',
                "コピー元環境: {$environment}, タグ: {$admPromotionTagId}, " . $e->getMessage(),
            );
            throw $e;
        }
    }

    public function getMessageDistributionPromotionData(string $admPromotionTagId): array
    {
        $admMessageDistributionInputs = AdmMessageDistributionInput::query()
            ->where('adm_promotion_tag_id', $admPromotionTagId)
            // 承認済みで配布済みで確認済みのデータのみを昇格対象とする
            ->where('create_status', AdmMessageCreateStatuses::Approved)
            ->get();

        if ($admMessageDistributionInputs->isEmpty()) {
            return [];
        }

        $messageDistributionPromotionEntity = new MessageDistributionPromotionEntity(
            $admMessageDistributionInputs,
        );

        return $messageDistributionPromotionEntity->formatToResponse();
    }

    public function getMessageDistributionPromotionDataFromEnvironment(
        string $environment,
        string $admPromotionTagId,
    ): ?MessageDistributionPromotionEntity {
        $domain = $this->configGetService->getAdminApiDomain($environment);
        if ($domain === null) {
            return null;
        }

        $endPoint = "get-message-distribution-promotion-data/$admPromotionTagId";
        $response = $this->sendApiService->sendApiRequest($domain, $endPoint);

        $messageDistributionPromotionEntity = MessageDistributionPromotionEntity::createFromResponseArray($response);

        if ($messageDistributionPromotionEntity->isEmpty()) {
            return null;
        }
        return $messageDistributionPromotionEntity;
    }

    /**
     * メッセージの配布実行ができるステータスでインポートする。
     * すでに配布済みのデータの場合は、絶対に変更しない。
     */
    private function importAdmMessageDistributionInputs(string $environment, string $admPromotionTagId): ?MessageDistributionPromotionEntity
    {
        $messageDistributionPromotionEntity = $this->getMessageDistributionPromotionDataFromEnvironment($environment, $admPromotionTagId);
        if ($messageDistributionPromotionEntity === null) {
            return null;
        }

        $admMessageDistributionInputs = $messageDistributionPromotionEntity->getAdmMessageDistributionInputs();

        $mngMessageIds = $admMessageDistributionInputs->pluck('mng_message_id')->all();

        // 昇格先ですでに配布済みで変更禁止のデータ
        $now = CarbonImmutable::now();
        $existingAdmMessageDistributionInputs = AdmMessageDistributionInput::query()
            ->whereIn('mng_message_id', $mngMessageIds)
            ->get()
            ->keyBy('mng_message_id');

        $insertArrayList = collect();
        $targetAdmMessageDistributionInputs = collect();
        foreach ($admMessageDistributionInputs as $admMessageDistributionInput) {
            $existingAdmMessageDistributionInput = $existingAdmMessageDistributionInputs->get(
                $admMessageDistributionInput->getMngMessageId(),
            );

            if (
                // すでに配布済みのデータは変更禁止
                $existingAdmMessageDistributionInput?->alreadyDistributed($now)
                // 変更がないデータはスキップ
                || $existingAdmMessageDistributionInput?->isSameAsForPromotion($admMessageDistributionInput)
            ) {

                continue;
            }

            // 昇格したデータは、配布実行可能なステータスに変更
            $admMessageDistributionInput->revertToUndistributed();

            $insertArrayList->push($admMessageDistributionInput->formatToInsertArray());
            $targetAdmMessageDistributionInputs->push($admMessageDistributionInput);
        }

        if ($insertArrayList->isNotEmpty()) {
            AdmMessageDistributionInput::upsert($insertArrayList->all(), ['mng_message_id']);
        }

        return new MessageDistributionPromotionEntity(
            $targetAdmMessageDistributionInputs,
        );
    }
}
