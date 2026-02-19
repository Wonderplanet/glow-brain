<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\NoticeCategory;
use App\Entities\GachaCautionPromotionEntity;
use App\Models\Adm\AdmGachaCaution;
use App\Operators\CloudFrontOperator;
use App\Operators\LocalFileOperator;
use App\Operators\S3Operator;
use App\Traits\NotificationTrait;
use App\Utils\StringUtil;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use WonderPlanet\Domain\Admin\Services\SendApiService;

class AdmGachaCautionService
{
    use NotificationTrait;

    public function __construct(
        private ConfigGetService $configGetService,
        private S3Operator $s3Operator,
        private LocalFileOperator $localFileOperator,
        private CloudFrontOperator $cloudFrontOperator,
        private SendApiService $sendApiService,
    ) {
    }

    /**
     * ガシャ注意事項の作成を行う。
     *
     * @param array<mixed> $data フォームデータ
     * @return AdmGachaCaution
     */
    public function createGachaCaution(
        array $data,
    ): AdmGachaCaution {
        $admGachaCaution = $this->createOrUpdateAdmGachaCaution(
            null,
            $data,
        );

        $this->createAndUploadHtmlByAdmGachaCaution($admGachaCaution);

        return $admGachaCaution;
    }

    public function updateGachaCaution(
        AdmGachaCaution $admGachaCaution,
        array $data,
    ): AdmGachaCaution {
        $admGachaCaution = $this->createOrUpdateAdmGachaCaution(
            $admGachaCaution,
            $data,
        );

        $this->createAndUploadHtmlByAdmGachaCaution($admGachaCaution);

        return $admGachaCaution;
    }

    public function deleteGachaCaution(
        AdmGachaCaution $admGachaCaution,
    ): void {
        $this->deleteHtmlByAdmGachaCaution($admGachaCaution);

        $admGachaCaution->delete();
    }

    /**
     * @param array<mixed> $data フォームデータ
     * @return bool|string
     */
    private function makeHtmlJsonByForm(array $data): string
    {
        $htmlJson = $data['html_json'] ?? '';
        // htmlタグを直接入力された際のエスケープ処理
        $htmlJson = html_entity_decode(
            tiptap_converter()->asHTML($htmlJson),
            ENT_QUOTES,
            'UTF-8',
        );
        $htmlJson = tiptap_converter()->asJSON($htmlJson, true);
        return json_encode($htmlJson);
    }

    /**
     * adm_gacha_cautions レコードの作成または更新を行う。
     *
     * @param AdmGachaCaution|null $admGachaCaution nullならinsertする。非nullならupdateする。
     * @param array<mixed> $data フォームデータ
     * @return AdmGachaCaution
     */
    private function createOrUpdateAdmGachaCaution(
        ?AdmGachaCaution $admGachaCaution,
        array $data,
    ): AdmGachaCaution {
        if ($admGachaCaution === null) {
            $admGachaCaution = new AdmGachaCaution();
            $admGachaCaution->id = Uuid::uuid4()->toString();
        }

        $admGachaCaution->fill($data);

        $admGachaCaution->html_json = $this->makeHtmlJsonByForm($data);

        $admGachaCaution->author_adm_user_id = Filament::auth()->user()->id;

        $admGachaCaution->save();

        return $admGachaCaution;
    }

    private function makeHtmlFileName(AdmGachaCaution $admGachaCaution): string
    {
        return $admGachaCaution->getId();
    }

    /**
     * S3アップロードするための一時的に作成するローカルのHTMLファイルパスを生成する
     */
    private function makeLocalHtmlPath(AdmGachaCaution $admGachaCaution): string
    {
        return StringUtil::joinPath(
            $this->configGetService->getAdminGachaCautionDir(),
            $this->makeHtmlFileName($admGachaCaution) . '.html',
        );
    }

    private function makeHtmlPath(AdmGachaCaution $admGachaCaution): string
    {
        return StringUtil::joinPath(
            NoticeCategory::GACHA_CAUTION->value,
            'html',
            $this->makeHtmlFileName($admGachaCaution) . '.html',
        );
    }

    public function makeHtmlUrl(AdmGachaCaution $admGachaCaution): string
    {
        return StringUtil::joinPath(
            $this->configGetService->getS3WebviewUrl(),
            $this->makeHtmlPath($admGachaCaution),
        );
    }

    /**
     * マークアップで入力したデータをHTMLに変換し、指定テンプレートに当て込み、S3にアップロードする
     *
     * @param \App\Models\Adm\AdmGachaCaution $admGachaCaution
     * @return void
     */
    private function createAndUploadHtmlByAdmGachaCaution(
        AdmGachaCaution $admGachaCaution,
    ) {
        $html = view('filament.templates.gacha_caution', [
            'text' => $admGachaCaution->getHtmlString(),
        ])->render();

        // ローカルにhtmlファイルを保存
        $localFilePath = $this->makeLocalHtmlPath($admGachaCaution);
        $this->localFileOperator->putWithCreateDir(
            $localFilePath,
            $html,
        );

        // S3にアップロード
        $this->s3Operator->putFromFile(
            $localFilePath,
            $this->makeHtmlPath($admGachaCaution),
            $this->configGetService->getS3WebviewBucket(),
        );

        // CloudFrontのキャッシュを削除
        $this->deleteCloudFrontCacheByAdmGachaCaution($admGachaCaution);

        // ローカルの一時ファイルを削除
        $this->localFileOperator->deleteFile($localFilePath);
    }

    private function deleteHtmlByAdmGachaCaution(AdmGachaCaution $admGachaCaution): void
    {
        $this->s3Operator->deleteFile(
            $this->configGetService->getS3WebviewBucket(),
            $this->makeHtmlPath($admGachaCaution),
        );

        $this->deleteCloudFrontCacheByAdmGachaCaution($admGachaCaution);
    }

    private function deleteCloudFrontCacheByAdmGachaCaution(AdmGachaCaution $admGachaCaution): void
    {
        $this->cloudFrontOperator->deleteS3WebviewCache([
            $this->makeHtmlPath($admGachaCaution),
        ]);
    }

    /**
     * 環境間インポートする際に使用するメソッド
     *
     * @param string $environment コピー元環境名
     */
    public function import(string $environment, string $admPromotionTagId): void
    {
        try {
            // DBテーブルデータをコピー
            $gachaCautionEntity = $this->importAdmGachaCaution($environment, $admPromotionTagId);
            if ($gachaCautionEntity === null) {
                Log::info('コピーするガシャ注意事項のデータがありませんでした', [
                    'environment' => $environment,
                    'admPromotionTagId' => $admPromotionTagId,
                ]);

                $this->sendProcessCompletedNotification(
                    'コピーするガシャ注意事項のデータがありませんでした',
                    "コピー元環境: {$environment}, タグ: {$admPromotionTagId}",
                );

                return;
            }

            // HTMLファイルをS3バケット間でコピー
            $this->copyAssetsBetweenEnvs($environment, $gachaCautionEntity);

            // CloudFrontキャッシュを削除
            $this->deleteGachaCautionCloudFrontCache();

            Log::info('ガシャ注意事項のコピーが完了しました', [
                'environment' => $environment,
                'admPromotionTagId' => $admPromotionTagId,
            ]);

            $this->sendProcessCompletedNotification(
                'ガシャ注意事項のコピーが完了しました',
                "コピー元環境: {$environment}, タグ: {$admPromotionTagId}",
            );
        } catch (\Exception $e) {
            Log::error('ガシャ注意事項のコピーに失敗しました', [
                'environment' => $environment,
                'admPromotionTagId' => $admPromotionTagId,
                'exception' => $e->getMessage(),
            ]);
            $this->sendDangerNotification(
                'ガシャ注意事項のコピーに失敗しました',
                "コピー元環境: {$environment}, タグ: {$admPromotionTagId}, " . $e->getMessage(),
            );
            throw $e;
        }
    }

    /**
     * コピー対象のガシャ注意事項で使用するアセットをコピー元環境のバケットからコピーする
     *
     * @param string $environment コピー元環境名
     */
    private function copyAssetsBetweenEnvs(string $environment, GachaCautionPromotionEntity $gachaCautionPromotionEntity): void
    {
        $admGachaCautions = $gachaCautionPromotionEntity->getAdmGachaCautions();

        $htmlPaths = [];
        foreach ($admGachaCautions as $admGachaCaution) {
            $htmlPaths[] = $this->makeHtmlPath($admGachaCaution);
        }

        $this->s3Operator->copyObjectsBetweenBuckets(
            $environment,
            $this->configGetService->getS3SourceWebviewBucket($environment),
            $this->configGetService->getS3WebviewBucket(),
            $htmlPaths
        );
    }

    /**
     * コピー先環境からコピー元のデータを取得するためのメソッド
     *
     * @return array<mixed>
     */
    public function getGachaCautionPromotionData(string $admPromotionTagId): array
    {
        $admGachaCautions = AdmGachaCaution::query()
            ->where('adm_promotion_tag_id', $admPromotionTagId)
            ->get();

        if ($admGachaCautions->isEmpty()) {
            return [];
        }

        $admGachaCautionPromotionEntity = new GachaCautionPromotionEntity(
            $admGachaCautions,
        );

        return $admGachaCautionPromotionEntity->formatToResponse();
    }

    /**
     * 指定環境からガシャ注意事項データを取得する
     *
     * @param string $environment
     * @param string $gachaCautionId
     * @return GachaCautionPromotionEntity|null
     */
    private function getGachaCautionDataFromEnvironment(
        string $environment,
        string $admPromotionTagId,
    ): ?GachaCautionPromotionEntity {
        $domain = $this->configGetService->getAdminApiDomain($environment);
        if ($domain === null) {
            return null;
        }

        $endPoint = "get-gacha-caution-promotion-data/$admPromotionTagId";
        $response = $this->sendApiService->sendApiRequest($domain, $endPoint);

        $gachaCautionPromotionEntity = GachaCautionPromotionEntity::createFromResponseArray($response);

        if ($gachaCautionPromotionEntity->isEmpty()) {
            return null;
        }

        return $gachaCautionPromotionEntity;
    }

    /**
     * ガシャ注意事項データをインポートする
     */
    private function importAdmGachaCaution(string $environment, string $admPromotionTagId): ?GachaCautionPromotionEntity
    {
        $gachaCautionPromotionEntity = $this->getGachaCautionDataFromEnvironment($environment, $admPromotionTagId);
        if ($gachaCautionPromotionEntity === null) {
            return null;
        }

        $admGachaCautions = $gachaCautionPromotionEntity->getAdmGachaCautions();
        AdmGachaCaution::upsert(
            $admGachaCautions->map(function (AdmGachaCaution $model) {
                return $model->formatToInsertArray();
            })->all(),
            ['id'],
        );

        return $gachaCautionPromotionEntity;
    }

    /**
     * ガシャ注意事項機能のCloudFrontキャッシュを削除する
     */
    public function deleteGachaCautionCloudFrontCache(): void
    {
        // Webviewキャッシュを削除
        $this->cloudFrontOperator->deleteS3WebviewCache([
            NoticeCategory::GACHA_CAUTION->value . '/*'
        ]);
    }
}
