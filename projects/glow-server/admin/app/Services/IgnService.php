<?php

declare(strict_types=1);

namespace App\Services;
use App\Constants\Language;
use App\Constants\NoticeCategory;
use App\Entities\IgnPromotionEntity;
use App\Models\Adm\AdmInGameNotice;
use App\Models\Mng\MngInGameNotice;
use App\Models\Mng\MngInGameNoticeI18n;
use App\Operators\CloudFrontOperator;
use App\Operators\S3Operator;
use App\Traits\MngCacheDeleteTrait;
use App\Traits\NotificationTrait;
use App\Utils\StringUtil;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\Admin\Services\SendApiService;

class IgnService
{
    use NotificationTrait;
    use MngCacheDeleteTrait;

    public function __construct(
        private ConfigGetService $configGetService,
        private S3Operator $s3Operator,
        private CloudFrontOperator $cloudFrontOperator,
        private SendApiService $sendApiService,
    ) {
    }

    public function makeBannerPath(?string $bannerFileName): string
    {
        if (StringUtil::isNotSpecified($bannerFileName)) {
            return '';
        }

        return sprintf(
            '%s/%s',
            NoticeCategory::IGN->value,
            $bannerFileName,
        );
    }

    public function makeBannerUrl(?string $bannerFilePath): string
    {
        if (StringUtil::isNotSpecified($bannerFilePath)) {
            return '';
        }

        return sprintf(
            '%s/%s',
            $this->configGetService->getS3BannerUrl(),
            $bannerFilePath,
        );
    }

    public function createIgn(array $data): MngInGameNotice
    {
        // MngInGameNotice
        $mngInGameNotice = new MngInGameNotice();
        $mngInGameNotice->adm_promotion_tag_id = $data['adm_promotion_tag_id'];
        $mngInGameNotice->display_type = $data['display_type'];
        $mngInGameNotice->destination_type = $data['destination_type'];
        $mngInGameNotice->destination_path = $data['destination_path'] ?? '';
        $mngInGameNotice->destination_path_detail = $data['destination_path_detail'] ?? '';
        $mngInGameNotice->enable = 1;
        $mngInGameNotice->priority = $data['priority'];
        $mngInGameNotice->display_frequency_type = $data['display_frequency_type'];
        $mngInGameNotice->start_at = $data['start_at'];
        $mngInGameNotice->end_at = $data['end_at'];
        $mngInGameNotice->save();

        $mngInGameNoticeId = $mngInGameNotice->id;

        // MngInGameNoticeI18n
        $mngInGameNoticeI18n = new MngInGameNoticeI18n();
        $mngInGameNoticeI18n->mng_in_game_notice_id = $mngInGameNoticeId;
        $mngInGameNoticeI18n->language = Language::Ja->value;
        $mngInGameNoticeI18n->title = $data['title'];
        $mngInGameNoticeI18n->description = $data['description'];
        $mngInGameNoticeI18n->banner_url = $this->makeBannerPath($data['upload_image_file_name'] ?? '');
        $mngInGameNoticeI18n->button_title = $data['button_title'] ?? '';
        $mngInGameNoticeI18n->save();

        // AdmInGameNotice
        $user = Filament::auth()->user();
        $admInGameNotice = new AdmInGameNotice();
        $admInGameNotice->mng_in_game_notice_id = $mngInGameNoticeId;
        $admInGameNotice->status = 'Approved';
        $admInGameNotice->author_adm_user_id = $user->id;
        $admInGameNotice->approval_adm_user_id = $user->id;
        $admInGameNotice->save();

        // S3にバナー画像をアップロード
        $this->uploadBanner($data);

        return $mngInGameNotice;
    }

    public function updateIgn(MngInGameNotice $mngInGameNotice, array $data): MngInGameNotice
    {
        // MngInGameNotice
        $mngInGameNotice->adm_promotion_tag_id = $data['adm_promotion_tag_id'] ?? '';
        $mngInGameNotice->display_type = $data['display_type'];
        $mngInGameNotice->destination_type = $data['destination_type'];
        $mngInGameNotice->destination_path = $data['destination_path'] ?? '';
        $mngInGameNotice->destination_path_detail = $data['destination_path_detail'] ?? '';
        $mngInGameNotice->enable = $data['enable'] ?? 1;
        $mngInGameNotice->priority = $data['priority'];
        $mngInGameNotice->display_frequency_type = $data['display_frequency_type'];
        $mngInGameNotice->start_at = $data['start_at'];
        $mngInGameNotice->end_at = $data['end_at'];
        $mngInGameNotice->save();

        // MngInGameNoticeI18n
        $mngInGameNoticeI18n = $mngInGameNotice->mng_in_game_notice_i18n;

        $beforeBannerUrl = $mngInGameNoticeI18n->banner_url;

        // TODO: 多言語対応は後でやる
        $mngInGameNoticeI18n->title = $data['title'];
        $mngInGameNoticeI18n->description = $data['description'];
        $mngInGameNoticeI18n->button_title = $data['button_title'] ?? '';
        if (isset($data['upload_image_file_name'])) {
            $mngInGameNoticeI18n->banner_url = $this->makeBannerPath($data['upload_image_file_name'] ?? '');
        }
        $mngInGameNoticeI18n->save();

        // AdmInGameNotice
        $user = Filament::auth()->user();
        $admInGameNotice = $mngInGameNotice->adm_in_game_notice;
        $admInGameNotice->author_adm_user_id = $user->id;
        $admInGameNotice->approval_adm_user_id = $user->id;
        $admInGameNotice->save();

        // S3にバナー画像をアップロード
        $this->uploadBanner($data);

        // 他のIGNで未使用であればバナー画像を削除
        $this->deleteBanner($beforeBannerUrl);

        return $mngInGameNotice;
    }

    public function deleteIgn(MngInGameNotice $mngInGameNotice): void
    {
        // MngInGameNoticeI18nを削除
        $mngInGameNoticeI18n = $mngInGameNotice->mng_in_game_notice_i18n;
        $beforeBannerUrl = $mngInGameNoticeI18n->banner_url;
        $mngInGameNoticeI18n->delete();

        // AdmInGameNoticeを削除
        $admInGameNotice = $mngInGameNotice->adm_in_game_notice;
        if ($admInGameNotice !== null) {
            $admInGameNotice->delete();
        }

        // MngInGameNoticeを削除
        $mngInGameNotice->delete();

        // S3にバナー画像をアップロード
        $this->deleteBanner($beforeBannerUrl);

        // キャッシュ削除
        $this->deleteMngInGameNoticeCache();
    }

    public function uploadBanner(array $data): void
    {
        $localFilePath = $data['upload_image_local_file_path'] ?? null;
        $fileName = $data['upload_image_file_name'] ?? null;

        if (is_null($localFilePath) || is_null($fileName)) {
            return;
        }

        $this->s3Operator->putFromFile(
            $localFilePath,
            $this->makeBannerPath($fileName),
            $this->configGetService->getS3BannerBucket(),
        );
    }

    public function deleteBanner(string $bannerUrl): void
    {
        $bannerExists = MngInGameNoticeI18n::query()
            ->where('banner_url', $bannerUrl)
            ->exists();

        // 他のIGNで未使用であればバナー画像を削除
        if ($bannerExists) {
            return;
        }

        $this->s3Operator->deleteFile(
            $this->configGetService->getS3BannerBucket(),
            $bannerUrl,
        );
    }

    /**
     * コピー対象のIGNで使用するアセットをコピー元環境のバケットからコピーする
     *
     * @param string $environment コピー元環境名
     */
    public function copyAssetsBetweenEnvs(string $environment, IgnPromotionEntity $ignPromotionEntity): void
    {
        $mngInGameNoticeI18ns = $ignPromotionEntity->getMngInGameNoticeI18ns();

        // コピー対象のオブジェクトパスを集める
        $bannerPaths = [];

        // 各種アセットのパス情報を取得
        foreach ($mngInGameNoticeI18ns as $mngInGameNoticeI18n) {
            $bannerUrl = $mngInGameNoticeI18n->banner_url;
            if (StringUtil::isNotSpecified($bannerUrl)) {
                continue;
            }

            $bannerPaths[] = $bannerUrl;
        }

        // バケット間コピーの実行
        if (!empty($bannerPaths)) {
            $this->s3Operator->copyObjectsBetweenBuckets(
                $environment,
                $this->configGetService->getS3SourceBannerBucket($environment),
                $this->configGetService->getS3BannerBucket(),
                $bannerPaths
            );
        }
    }

    /**
     * 環境とタグの指定で、IGNデータを環境間コピーする
     *
     * @param string $environment コピー元環境名
     */
    public function import(string $environment, string $admPromotionTagId): void
    {
        try {
            // DBテーブルデータをコピー
            $ignPromotionEntity = $this->importMngInGameNotices($environment, $admPromotionTagId);
            if ($ignPromotionEntity === null) {
                Log::info('コピーするIGNのデータがありませんでした', [
                    'environment' => $environment,
                    'admPromotionTagId' => $admPromotionTagId,
                ]);

                $this->sendProcessCompletedNotification(
                    'コピーするIGNのデータがありませんでした',
                    "コピー元環境: {$environment}, タグ: {$admPromotionTagId}",
                );

                // コピー対象がないので終了
                return;
            }

            // バナー画像をS3バケット間でコピー
            $this->copyAssetsBetweenEnvs($environment, $ignPromotionEntity);

            // CloudFrontキャッシュを削除
            $this->deleteIgnCloudFrontCache();

            Log::info('IGNのコピーが完了しました', [
                'environment' => $environment,
                'admPromotionTagId' => $admPromotionTagId,
            ]);

            $this->sendProcessCompletedNotification(
                'IGNのコピーが完了しました',
                "コピー元環境: {$environment}, タグ: {$admPromotionTagId}",
            );
        } catch (\Exception $e) {
            Log::error('IGNのコピーに失敗しました', [
                'environment' => $environment,
                'admPromotionTagId' => $admPromotionTagId,
                'exception' => $e->getMessage(),
            ]);
            $this->sendDangerNotification(
                'IGNのコピーに失敗しました',
                "コピー元環境: {$environment}, タグ: {$admPromotionTagId}, " . $e->getMessage(),
            );
            throw $e;
        }
    }

    public function getIgnPromotionData(string $admPromotionTagId): array
    {
        $mngInGameNotices = MngInGameNotice::query()
            ->where('adm_promotion_tag_id', $admPromotionTagId)
            ->get();

        if ($mngInGameNotices->isEmpty()) {
            return [];
        }

        $mngInGameNoticeI18ns = $mngInGameNotices->map(function (MngInGameNotice $mngInGameNotice) {
            return $mngInGameNotice->mng_in_game_notice_i18n;
        })->filter(
            fn(?MngInGameNoticeI18n $mngInGameNoticeI18n) => !is_null($mngInGameNoticeI18n)
        );

        $ignPromotionEntity = new IgnPromotionEntity(
            $mngInGameNotices,
            $mngInGameNoticeI18ns,
        );

        return $ignPromotionEntity->formatToResponse();
    }

    public function getIgnPromotionDataFromEnvironment(
        string $environment,
        string $admPromotionTagId,
    ): ?IgnPromotionEntity {
        $domain = $this->configGetService->getAdminApiDomain($environment);
        if ($domain === null) {
            return null;
        }

        $endPoint = "get-ign-promotion-data/$admPromotionTagId";
        $response = $this->sendApiService->sendApiRequest($domain, $endPoint);

        $ignPromotionEntity = IgnPromotionEntity::createFromResponseArray($response);

        if ($ignPromotionEntity->isEmpty()) {
            return null;
        }
        return $ignPromotionEntity;
    }

    private function importMngInGameNotices(string $environment, string $admPromotionTagId): ?IgnPromotionEntity
    {
        $ignPromotionEntity = $this->getIgnPromotionDataFromEnvironment($environment, $admPromotionTagId);
        if ($ignPromotionEntity === null || $ignPromotionEntity->isEmpty()) {
            return null;
        }

        $mngInGameNotices = $ignPromotionEntity->getMngInGameNotices();
        MngInGameNotice::upsert(
            $mngInGameNotices->map(function (MngInGameNotice $mngInGameNotice) {
                return $mngInGameNotice->formatToInsertArray();
            })->all(),
            ['id']
        );

        $mngInGameNoticeI18ns = $ignPromotionEntity->getMngInGameNoticeI18ns();
        MngInGameNoticeI18n::upsert(
            $mngInGameNoticeI18ns->map(function (MngInGameNoticeI18n $mngInGameNoticeI18n) {
            return $mngInGameNoticeI18n->formatToInsertArray();
            })->all(),
            ['id']
        );

        // 昇格後にmngキャッシュを削除
        $this->deleteMngInGameNoticeCache();

        return $ignPromotionEntity;
    }

    /**
     * IGN機能のCloudFrontキャッシュを削除する
     */
    public function deleteIgnCloudFrontCache(): void
    {
        // バナーキャッシュを削除
        $this->cloudFrontOperator->deleteS3BannerCache([
            NoticeCategory::IGN->value . '/*'
        ]);
    }
}
