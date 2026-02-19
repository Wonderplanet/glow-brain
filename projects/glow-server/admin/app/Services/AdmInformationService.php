<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\NoticeCategory;
use App\Constants\OsType;
use App\Entities\Clock;
use App\Entities\TiptapContentEntity;
use App\Models\Adm\AdmInformation;
use App\Operators\CloudFrontOperator;
use App\Operators\LocalFileOperator;
use App\Operators\S3Operator;
use App\Traits\NotificationTrait;
use App\Utils\StringUtil;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\Admin\Services\SendApiService;

class AdmInformationService
{
    use NotificationTrait;

    public function __construct(
        private ConfigGetService $configGetService,
        private S3Operator $s3Operator,
        private CloudFrontOperator $cloudFrontOperator,
        private LocalFileOperator $localFileOperator,
        private Clock $clock,
        private SendApiService $sendApiService,
    ) {
    }

    /**
     * Path
     */

    /**
     * index.jsonを配置するS3バケット上でのパスを取得する
     *
     * @return string
     */
    public function getIndexJsonPath(): string
    {
        return StringUtil::joinPath(
            NoticeCategory::INFORMATION->value,
            'index.json',
        );
    }

    /**
     * お知らせhtmlを配置するS3バケット上でのパスを取得する
     *
     * @param string $fileName
     * @return string
     */
    public function getHtmlPath(string $fileName): string
    {
        return StringUtil::joinPath(
            NoticeCategory::INFORMATION->value,
            'html',
            $fileName . '.html',
        );
    }

    /**
     * お知らせ作成機能で作成したhtmlをローカルへ一時的に保存するパスを取得する
     *
     * @param string $fileName
     * @return string
     */
    public function getLocalHtmlPath(string $fileName): string
    {
        return StringUtil::joinPath(
            $this->configGetService->getAdminInformationDir(),
            $fileName . '.html',
        );
    }

    /**
     * index.jsonをローカルへ一時的に保存するパスを取得する
     *
     * @return string
     */
    public function getLocalIndexJsonPath(): string
    {
        return StringUtil::joinPath(
            $this->configGetService->getAdminInformationDir(),
            'index.json',
        );
    }

    /**
     * お知らせ作成機能でアップロードされたhtml内の画像が配置されているローカルパスを取得する
     *
     * @param string $fileName
     * @return string
     */
    public function getLocalHtmlImagePath(string $fileName): string
    {
        return public_path(
            StringUtil::joinPath(
                'storage',
                'images',
                $fileName,
            ),
        );
    }

    /**
     * お知らせ作成機能でアップロードされたhtml内の画像が配置されているS3バケット上でのパスを取得する
     *
     * @param string $fileName
     * @return string
     */
    public function getHtmlImageUploadPath(string $fileName): string
    {
        return StringUtil::joinPath(
            NoticeCategory::INFORMATION->value,
            'assets',
            'images',
            $fileName,
        );
    }

    public function getHtmlImagePreviewPath(string $fileName): string
    {
        return StringUtil::joinPath(
            $this->configGetService->getS3WebviewUrl(),
            NoticeCategory::INFORMATION->value,
            'assets',
            'images',
            $fileName,
        );
    }

    public function getHtmlUrl(string $admInformationId): string
    {
        return StringUtil::joinPath(
            $this->configGetService->getS3WebviewUrl(),
            NoticeCategory::INFORMATION->value,
            'html',
            $admInformationId . '.html',
        );
    }

    public function getBannerUrl(?AdmInformation $admInformation): string
    {
        if (is_null($admInformation) || !$admInformation->hasBanner()) {
            return '';
        }

        return StringUtil::joinPath(
            $this->configGetService->getS3BannerUrl(),
            rawurlencode($admInformation->banner_url),
        );
    }

    /**
     * Logic
     */

    /**
     * 本文のhtml内の画像パスをS3上で配置した際のパスに変換する
     *
     * @param mixed $content TipTapEditorに入力したマークアップデータをtiptap_converterで変換して得られたJSON
     * @return void
     */
    public function replaceImageSrcToS3Url(&$content)
    {
        foreach ($content as &$element) {
            if ($element['type'] === 'image') {
                if (isset($element['attrs']['src'])) {
                    $src = $element['attrs']['src'];
                    $src = str_replace('/storage/images', '../assets/images', $src);
                    $element['attrs']['src'] = $src;
                }
            }

            if (isset($element['content']) && is_array($element['content'])) {
                $this->replaceImageSrcToS3Url($element['content']);
            }
        }
    }

    /**
     * 本文のhtml内の画像パスを取得する
     *
     * @param mixed $content TipTapEditorに入力したマークアップデータをtiptap_converterで変換して得られたJSON
     * @return array
     */
    public function getImageSrc($content): array
    {
        $images = [];
        foreach ($content as $element) {
            if ($element['type'] === 'image') {
                $images[] = $element['attrs']['src'] ?? '';
            }

            if (isset($element['content']) && is_array($element['content'])) {
                $images = array_merge($images, $this->getImageSrc($element['content']));
            }
        }
        return $images;
    }

    /**
     * S3上のindex.jsonを取得し、デコードして配列として返す
     *
     * @return array
     */
    private function getIndexJson(): array
    {
        $result = [];

        try {
            $result = $this->s3Operator->getFile(
                $this->configGetService->getS3InformationBucket(),
                $this->getIndexJsonPath(),
            );
            $result = json_decode($result['Body']->getContents(), true);
        } catch (\Exception $e) {
            $result = [
                'topics' => [],
                'informations' => [],
            ];
        }

        return $result;
    }

    /**
     * S3上のindex.jsonに含まれる、有効なお知らせ情報を取得する
     *
     * @param array $indexJson
     * @param \Carbon\CarbonImmutable $now
     * @return \Illuminate\Support\Collection
     */
    private function getValidInformations(array $indexJson, CarbonImmutable $now): Collection
    {
        $informations = collect($indexJson['informations'])->keyBy('informationId');

        return $informations->filter(function ($information) use ($now) {
            return CarbonImmutable::parse($information['endAt']) > $now->subMonth()->startOfMonth();
        });
    }

    /**
     * マークアップで入力したデータをHTMLに変換し、指定テンプレートに当て込み、S3にアップロードする
     *
     * @param \App\Models\Adm\AdmInformation $admInformation
     * @param \Carbon\CarbonImmutable $now
     * @return void
     */
    public function uploadHtml(
        AdmInformation $admInformation,
        CarbonImmutable $now,
        bool $isCreate = false,
    ) {
        $fileName = $admInformation->id;

        $imgPath = '';
        if ($admInformation->hasBanner()) {
            $imgPath = '../assets/images/' . basename($admInformation->banner_url);
        }

        $isTextBanner = StringUtil::isNotSpecified($imgPath);

        $html = view('filament.templates.information', [
            'category' => $admInformation->category,
            'categoryName' => $admInformation->category_label,
            'date' => $admInformation->pre_notice_start_at->format('Y-m-d H:i'),
            'imgPath' => $imgPath,
            'text' => $admInformation->getHtmlString(),
            'title' => $admInformation->title,
            'isTextBanner' => $isTextBanner,
        ])->render();

        $localFilePath = $this->getLocalHtmlPath($fileName);

        $this->localFileOperator->putWithCreateDir(
            $localFilePath,
            $html,
        );

        $this->s3Operator->putFromFile(
            $localFilePath,
            $this->getHtmlPath($fileName),
            $this->configGetService->getS3WebviewBucket(),
        );

        // CloudFrontのキャッシュを削除
        if (!$isCreate) {
            // 作成時はキャッシュが存在しないため削除しない。実行するとエラーになる
            $this->cloudFrontOperator->deleteS3WebviewCache([
                $this->getHtmlPath($fileName),
            ]);
        }
    }

    public function deleteHtml(AdmInformation $admInformation): void
    {
        $fileName = $admInformation->id;

        $this->s3Operator->deleteFile(
            $this->configGetService->getS3WebviewBucket(),
            $this->getHtmlPath($fileName),
        );

        // CloudFrontのキャッシュを削除
        $this->cloudFrontOperator->deleteS3WebviewCache([
            $this->getHtmlPath($fileName),
        ]);
    }

    /**
     * index.jsonに含まれる、お知らせ情報を、追加・更新・削除する
     *
     * @param \App\Models\Adm\AdmInformation $admInformation
     * @param \Carbon\CarbonImmutable $now
     * @param bool $isForceDisable 強制無効化フラグ。主にお知らせを削除する際にtrueにして、index.jsonから除外するために使う
     * @return void
     */
    public function updateIndexJson(
        AdmInformation $admInformation,
        CarbonImmutable $now,
        bool $isForceDisable = false,
    ): void {
        $indexJson = $this->getIndexJson();
        $informations = $this->getValidInformations($indexJson, $now);

        $informationId = $admInformation->id;

        $enable = $admInformation->enable && !$isForceDisable;

        // 本掲載設定
        $mainNoticeStartAt = $admInformation->start_at;
        $mainNoticeEndAt = $admInformation->end_at;

        // 予告掲載設定
        $preNoticeStartAt = $admInformation->pre_notice_start_at;
        $preNoticeEndAt = $mainNoticeStartAt->subSecond();
        $preNoticeEndAt < $preNoticeStartAt && $preNoticeEndAt = $preNoticeStartAt;

        // 本掲載終了後の掲載設定
        $postNoticeStartAt = $mainNoticeEndAt->addSecond();
        $postNoticeEndAt = $admInformation->post_notice_end_at;
        $postNoticeEndAt < $postNoticeStartAt && $postNoticeStartAt = $postNoticeEndAt;

        $startAt = $preNoticeStartAt;
        $endAt = $mainNoticeEndAt;

        $information = $informations->get($informationId);

        // 公開フラグがfalseの場合はindexJsonから削除
        if ($enable) {
            $information = [
                'informationId' => $informationId,
                'title' => $admInformation->title,
                'priority' => $admInformation->priority,
                'contentsUrl' => $this->getHtmlPath($informationId),
                'bannerUrl' => $admInformation->banner_url,
                'category' => $admInformation->category,
                'osType' => $admInformation->os_type,
                'lastUpdatedAt' => $admInformation->content_change_at->format('Y-m-d H:i:sP'),
                'startAt' => $startAt->format('Y-m-d H:i:sP'),
                'endAt' => $endAt->format('Y-m-d H:i:sP'),
                'preNoticeStartAt' => $preNoticeStartAt->format('Y-m-d H:i:sP'),
                'preNoticeEndAt' => $preNoticeEndAt->format('Y-m-d H:i:sP'),
                'mainNoticeStartAt' => $mainNoticeStartAt->format('Y-m-d H:i:sP'),
                'mainNoticeEndAt' => $mainNoticeEndAt->format('Y-m-d H:i:sP'),
                'postNoticeStartAt' => $postNoticeStartAt->format('Y-m-d H:i:sP'),
                'postNoticeEndAt' => $postNoticeEndAt->format('Y-m-d H:i:sP'),
            ];

            $informations->put($informationId, $information);
        } else {
            $informations->forget($informationId);
        }

        $informations = $this->fillMissingKeysByInformations($informations);

        $informations = $informations->sortByDesc(fn($information) => $information['priority']);
        $indexJson['informations'] = $informations->values()->all();

        // 生成したJsonをローカルに保存
        $localFilePath = $this->getLocalIndexJsonPath();
        $this->localFileOperator->putWithCreateDir(
            $localFilePath,
            json_encode($indexJson, JSON_PRETTY_PRINT),
        );

        // S3にアップロード
        $this->s3Operator->putFromFile(
            $localFilePath,
            $this->getIndexJsonPath(),
            $this->configGetService->getS3InformationBucket(),
        );

        // CloudFrontのキャッシュを削除
        $this->cloudFrontOperator->deleteS3InformationCache([
            $this->getIndexJsonPath(),
        ]);
    }

    /**
     * 途中で追加したキーは、古いデータには存在せずエラーになるため、値を設定する。
     *
     * @param Collection<string, array<mixed>> $informations
     *  key: adm_informations.id, value: S3上にアップロードしたindex.jsonに含まれるお知らせ情報1つの配列
     * @return Collection<string, array<mixed>>
     *  引数の$informationsと同じフォーマット。
     */
    private function fillMissingKeysByInformations(
        Collection $informations,
    ): Collection {
        $s3InformationIds = $informations->keys();
        $admInformations = AdmInformation::query()
            ->whereIn('id', $s3InformationIds)
            ->get()
            ->keyBy('id');
        foreach ($informations as $admInformationId => $information) {
            $admInformation = $admInformations->get($admInformationId);

            if (!array_key_exists('priority', $information)) {
                $information['priority'] = $admInformation?->priority ?? 0;
            }

            if (!array_key_exists('osType', $information)) {
                $information['osType'] = $admInformation?->os_type ?? OsType::ALL->value;
            }

            $informations->put($admInformationId, $information);
        }

        return $informations;
    }

    public function createAndUploadIndexJsonByAdmInformations(
        Collection $admInformations,
    ): void {
        if ($admInformations->isEmpty()) {
            return;
        }

        $informations = collect();
        foreach ($admInformations as $admInformation) {
            if ($admInformation->isEnable() === false) {
                continue;
            }

            $informationId = $admInformation->id;

            // 本掲載設定
            $mainNoticeStartAt = $admInformation->start_at;
            $mainNoticeEndAt = $admInformation->end_at;
            // 予告掲載設定
            $preNoticeStartAt = $admInformation->pre_notice_start_at;
            $preNoticeEndAt = $mainNoticeStartAt->subSecond();
            ($preNoticeEndAt < $preNoticeStartAt) && $preNoticeEndAt = $preNoticeStartAt;
            // 本掲載終了後の掲載設定
            $postNoticeStartAt = $mainNoticeEndAt->addSecond();
            $postNoticeEndAt = $admInformation->post_notice_end_at;
            ($postNoticeEndAt < $postNoticeStartAt) && $postNoticeStartAt = $postNoticeEndAt;
            // 掲載期間
            $startAt = $preNoticeStartAt;
            $endAt = $mainNoticeEndAt;

            $datetimeFormat = 'Y-m-d H:i:sP';
            $informations->push([
                'informationId' => $informationId,
                'title' => $admInformation->title,
                'priority' => $admInformation->priority,
                'contentsUrl' => $this->getHtmlPath($informationId),
                'bannerUrl' => $admInformation->banner_url,
                'category' => $admInformation->category,
                'osType' => $admInformation->os_type ?? OsType::ALL->value,
                'lastUpdatedAt' => $admInformation->content_change_at?->format($datetimeFormat),
                'startAt' => $startAt->format($datetimeFormat),
                'endAt' => $endAt->format($datetimeFormat),
                'preNoticeStartAt' => $preNoticeStartAt->format($datetimeFormat),
                'preNoticeEndAt' => $preNoticeEndAt->format($datetimeFormat),
                'mainNoticeStartAt' => $mainNoticeStartAt->format($datetimeFormat),
                'mainNoticeEndAt' => $mainNoticeEndAt->format($datetimeFormat),
                'postNoticeStartAt' => $postNoticeStartAt->format($datetimeFormat),
                'postNoticeEndAt' => $postNoticeEndAt->format($datetimeFormat),
            ]);
        }

        $informations = $informations->sortByDesc(function ($information) {
            return $information['priority'] ?? 0;
        });

        $indexJson['informations'] = $informations->values()->toArray();

        // jsonファイルを作成してS3へアップロードする
        $localFilePath = $this->getLocalIndexJsonPath();
        $remoteFilePath = $this->getIndexJsonPath();
        $this->localFileOperator->putWithCreateDir(
            $localFilePath,
            json_encode($indexJson, JSON_PRETTY_PRINT),
        );
        // S3にアップロード
        $this->s3Operator->putFromFile(
            $localFilePath,
            $remoteFilePath,
            $this->configGetService->getS3InformationBucket(),
        );
    }

    /**
     * AdmInformationデータを作成または更新したモデルを返す（DB更新はまだ）
     *
     * @param mixed $admInformation
     * @param array $data
     * @return \App\Models\Adm\AdmInformation
     */
    public function createOrUpdateAdmInformation(?AdmInformation $admInformation, array $data): AdmInformation
    {
        if (is_null($admInformation)) {
            $admInformation = new AdmInformation();
        }

        $tiptapContentEntity = new TiptapContentEntity($data['html_json'] ?? '');
        $uploadElements = $tiptapContentEntity->getImageSrcReplacedElements(
            function ($src) {
                // お知らせhtmlファイルで参照する相対パスに変換
                return StringUtil::joinPath('..', 'assets', 'images', basename($src));
            }
        );
        $admInformation->html_json = json_encode($uploadElements);

        $isDeleteBanner = $data['is_delete_banner'] ?? false;
        if ($isDeleteBanner) {
            $admInformation->banner_url = null;
        } else {
            $admInformation->banner_url = $data['banner_url'] ?? null;
        }

        $admInformation->enable = $data['enable'] ?? false;
        $admInformation->os_type = $data['os_type'] ?? OsType::ALL->value;
        $admInformation->adm_promotion_tag_id = $data['adm_promotion_tag_id'] ?? null;
        $admInformation->priority = $data['priority'] ?? 0;
        $admInformation->category = $data['category'] ?? '';
        $admInformation->title = $data['title'] ?? '';
        $admInformation->pre_notice_start_at = $data['pre_notice_start_at'] ?? null;
        $admInformation->start_at = $data['start_at'] ?? null;
        $admInformation->end_at = $data['end_at'] ?? null;
        $admInformation->post_notice_end_at = $data['post_notice_end_at'] ?? null;

        if ($admInformation->needLastUpdatedAt()) {
            $admInformation->content_change_at = $this->clock->now()->toDateTimeString();
        }

        return $admInformation;
    }

    /**
     * お知らせを更新する
     *
     * @param \App\Models\Adm\AdmInformation $beforeAdmInformation
     * @param array $data
     * @return \App\Models\Adm\AdmInformation
     */
    public function updateInformation(AdmInformation $beforeAdmInformation, array $data): AdmInformation
    {
        $now = $this->clock->now();

        $beforeTiptapContentEntity = new TiptapContentEntity($beforeAdmInformation->html_json ?? '');
        $tiptapContentEntity = new TiptapContentEntity($data['html_json'] ?? '');

        $afterAdmInformation = clone $beforeAdmInformation;
        $afterAdmInformation = $this->createOrUpdateAdmInformation($afterAdmInformation, $data);
        $afterAdmInformation->save();

        $this->uploadHtmlImages($beforeTiptapContentEntity, $tiptapContentEntity);
        $this->uploadAndDeleteBanner($beforeAdmInformation, $afterAdmInformation, $data);
        $this->uploadHtml($afterAdmInformation, $now);
        $this->updateIndexJson($afterAdmInformation, $now);

        return $afterAdmInformation;
    }

    /**
     * お知らせを削除する
     */
    public function deleteInformation(AdmInformation $admInformation): void
    {
        $now = $this->clock->now();

        // index.jsonから削除
        $this->updateIndexJson($admInformation, $now, isForceDisable: true);

        // htmlファイルを削除
        $this->deleteHtml($admInformation);

        // バナー画像が他も使っていなくて不要なら削除
        $this->deleteBanner($admInformation);

        // 本文中の画像ファイルを削除する
        $this->deleteHtmlImages($admInformation);

        // レコードを削除
        $admInformation->delete();
    }

    /**
     * お知らせ本文内の全ての画像をS3にアップロードする
     * @param \App\Entities\TiptapContentEntity $tiptapContentEntity
     * @return void
     */
    public function uploadHtmlImages(
        TiptapContentEntity $beforeTiptapContentEntity,
        TiptapContentEntity $afterTiptapContentEntity,
    ): void {
        $needRemoteUploadImageFileNames = $afterTiptapContentEntity->getNeedRemoteUploadImageFileNames();

        foreach ($needRemoteUploadImageFileNames as $fileName) {
            $this->s3Operator->putFromFile(
                $this->getLocalHtmlImagePath($fileName),
                $this->getHtmlImageUploadPath($fileName),
                $this->configGetService->getS3WebviewBucket(),
            );
        }

        // 不要になった画像を削除する
        $beforeImageFileNames = collect($beforeTiptapContentEntity->getImageFileNames());
        $afterImageFileNames = collect($afterTiptapContentEntity->getImageFileNames());
        $needDeleteImageFileNames = $beforeImageFileNames->diff($afterImageFileNames);
        foreach ($needDeleteImageFileNames as $fileName) {
            $this->s3Operator->deleteFile(
                $this->configGetService->getS3WebviewBucket(),
                $this->getHtmlImageUploadPath($fileName),
            );
        }
    }

    /**
     * お知らせ本文内の全ての画像をS3にアップロードする
     * @param \App\Entities\TiptapContentEntity $tiptapContentEntity
     * @return void
     */
    public function deleteHtmlImages(
        AdmInformation $admInformation,
    ): void {
        $tiptapContentEntity = new TiptapContentEntity($admInformation->html_json ?? '');
        $needDeleteImageSrcList = $tiptapContentEntity->getAllImageSrcList();
        foreach ($needDeleteImageSrcList as $src) {
            $fileName = basename($src);
            $this->s3Operator->deleteFile(
                $this->configGetService->getS3WebviewBucket(),
                $this->getHtmlImageUploadPath($fileName),
            );
        }
    }

    /**
     * お知らせのバナー画像をアップロードし、不要になった変更前のバナー画像を削除する
     * @param \App\Models\Adm\AdmInformation $beforeAdmInformation
     * @param \App\Models\Adm\AdmInformation $afterAdmInformation
     * @param array $data
     * @return void
     */
    public function uploadAndDeleteBanner(
        AdmInformation $beforeAdmInformation,
        AdmInformation $afterAdmInformation,
        array $data,
    ): void {
        $beforeBannerUrl = $beforeAdmInformation->banner_url ?? '';
        $afterBannerUrl = $afterAdmInformation->banner_url ?? '';

        $bannerLocalFilePath = $data['banner_local_file_path'] ?? '';

        if (
            StringUtil::isSpecified($afterBannerUrl)
            && StringUtil::isSpecified($bannerLocalFilePath)
            && $beforeBannerUrl !== $afterBannerUrl
        ) {
            // 新規バナー画像をアップロード
            $this->s3Operator->putFromFile(
                $bannerLocalFilePath,
                $afterBannerUrl,
                $this->configGetService->getS3BannerBucket(),
            );

            // htmlファイル内での相対パスでバナー画像参照できるようにwebviewバケットにも保存する
            $this->s3Operator->putFromFile(
                $bannerLocalFilePath,
                $this->getHtmlImageUploadPath(basename($afterBannerUrl)),
                $this->configGetService->getS3WebviewBucket(),
            );

            // 不要になった変更前のバナー画像を削除
            $this->deleteBanner($beforeAdmInformation);
        }
    }

    /**
     * 不要になったバナー画像をリモートから削除する
     * @param \App\Models\Adm\AdmInformation $admInformation
     * @return void
     */
    public function deleteBanner(AdmInformation $admInformation): void
    {
        if (!$admInformation->hasBanner()) {
            return;
        }

        $bannerUrl = $admInformation->banner_url;

        $bannerExists = AdmInformation::query()
            ->where('banner_url', $bannerUrl)
            ->whereNot('id', $admInformation->id)
            ->exists();
        if ($bannerExists) {
            return;
        }

        $this->s3Operator->deleteFile(
            $this->configGetService->getS3BannerBucket(),
            $bannerUrl,
        );

        $this->s3Operator->deleteFile(
            $this->configGetService->getS3WebviewBucket(),
            $this->getHtmlImageUploadPath(basename($bannerUrl)),
        );
    }

    public function getInformationPromotionDataFromEnvironment(string $environment, string $admPromotionTagId): Collection
    {
        $domain = $this->configGetService->getAdminApiDomain($environment);
        if ($domain === null) {
            return collect([]);
        }

        $endPoint = "get-information-promotion-data/$admPromotionTagId";
        $response = $this->sendApiService->sendApiRequest($domain, $endPoint);
        return collect($response);
    }

    /**
     * @param string $environment
     * @param string $admPromotionTagId
     * @return Collection<AdmInformation> 他環境からインポートして値に変動があったadm_informationsレコード
     */
    public function importAdmInformations(string $environment, string $admPromotionTagId): Collection
    {
        $informationPromotionData = $this->getInformationPromotionDataFromEnvironment($environment, $admPromotionTagId);

        $admInformations = $informationPromotionData->map(function (array $responseArray) {
            return AdmInformation::createFromResponseArray($responseArray);
        });

        AdmInformation::upsert(
            $admInformations->map(function (AdmInformation $admInformation) {
                return $admInformation->formatToInsertArray();
            })->all(),
            ['id'],
        );

        return $admInformations;
    }

    /**
     * コピー対象のお知らせで使用するアセットをコピー元環境のバケットからコピーする
     *
     * @param string $environment コピー元環境名
     * @param Collection<AdmInformation> $admInformations コピー対象のお知らせ情報
     */
    public function copyAssetsBetweenEnvs(string $environment, Collection $admInformations): void
    {
        // コピー対象のオブジェクトパスを集める
        $imagePaths = [];
        $bannerPaths = [];
        $htmlPaths = [];

        // 各種アセットのパス情報を取得
        foreach ($admInformations as $admInformation) {
            // バナー画像のパスを追加
            if ($admInformation->hasBanner()) {
                $bannerPaths[] = $admInformation->banner_url;

                // HTMLファイル内での相対パス参照用も用意する
                $bannerFileName = basename($admInformation->banner_url);
                $imagePaths[] = $this->getHtmlImageUploadPath($bannerFileName);
            }

            // HTML内の画像パスを収集
            if (!empty($admInformation->html_json)) {
                $tiptapContentEntity = new TiptapContentEntity($admInformation->html_json);
                $imageFileNames = $tiptapContentEntity->getImageFileNames();

                foreach ($imageFileNames as $fileName) {
                    $imagePaths[] = $this->getHtmlImageUploadPath($fileName);
                }
            }

            // HTMLファイルのパスを追加
            $htmlPaths[] = $this->getHtmlPath($admInformation->id);
        }

        // バケット間コピーの実行
        if (!empty($imagePaths)) {
            $this->s3Operator->copyObjectsBetweenBuckets(
                $environment,
                $this->configGetService->getS3SourceWebviewBucket($environment),
                $this->configGetService->getS3WebviewBucket(),
                $imagePaths
            );
        }
        if (!empty($bannerPaths)) {
            $this->s3Operator->copyObjectsBetweenBuckets(
                $environment,
                $this->configGetService->getS3SourceBannerBucket($environment),
                $this->configGetService->getS3BannerBucket(),
                $bannerPaths
            );
        }

        $this->s3Operator->copyObjectsBetweenBuckets(
            $environment,
            $this->configGetService->getS3SourceWebviewBucket($environment),
            $this->configGetService->getS3WebviewBucket(),
            $htmlPaths
        );
    }

    /**
     * 環境とタグの指定で、お知らせデータを環境間コピーする
     *
     * @param string $environment コピー元環境名
     */
    public function import(string $environment, string $admPromotionTagId): void
    {
        try {
            // adm_informationsデータをコピー
            $admInformations = $this->importAdmInformations($environment, $admPromotionTagId);

            // バナーや本文中画像をS3バケット間でコピー
            $this->copyAssetsBetweenEnvs($environment, $admInformations);

            // index.jsonを生成してアップロード
            $this->createAndUploadIndexJsonByAdmInformations(
                AdmInformation::getActives(CarbonImmutable::now()),
            );

            // CloudFrontキャッシュを削除
            $this->deleteInformationCloudFrontCache();

            Log::info('お知らせのコピーが完了しました', [
                'environment' => $environment,
                'admPromotionTagId' => $admPromotionTagId,
            ]);

            $this->sendProcessCompletedNotification(
                'お知らせのコピーが完了しました',
                "コピー元環境: {$environment}, タグ: {$admPromotionTagId}",
            );
        } catch (\Exception $e) {
            Log::error('お知らせのコピーに失敗しました', [
                'environment' => $environment,
                'admPromotionTagId' => $admPromotionTagId,
                'exception' => $e->getMessage(),
            ]);
            $this->sendDangerNotification(
                'お知らせのコピーに失敗しました',
                "コピー元環境: {$environment}, タグ: {$admPromotionTagId}, " . $e->getMessage(),
            );
            throw $e;
        }
    }

    public function getInformationPromotionData(string $admPromotionTagId): Collection
    {
        $admInformations = AdmInformation::query()
            ->where('adm_promotion_tag_id', $admPromotionTagId)
            ->get();

        return $admInformations->map(function (AdmInformation $admInformation) {
            return $admInformation->formatToResponse();
        });
    }

    /**
     * お知らせ機能のCloudFrontキャッシュを削除する
     */
    public function deleteInformationCloudFrontCache(): void
    {
        // バナーキャッシュを削除
        $this->cloudFrontOperator->deleteS3BannerCache([
            NoticeCategory::INFORMATION->value . '/*'
        ]);

        // Webviewキャッシュを削除
        $this->cloudFrontOperator->deleteS3WebviewCache([
            NoticeCategory::INFORMATION->value . '/*'
        ]);

        // Informationキャッシュを削除
        $this->cloudFrontOperator->deleteS3InformationCache([
            NoticeCategory::INFORMATION->value . '/*'
        ]);
    }
}
