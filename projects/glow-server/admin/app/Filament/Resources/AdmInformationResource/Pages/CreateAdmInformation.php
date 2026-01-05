<?php

namespace App\Filament\Resources\AdmInformationResource\Pages;

use App\Entities\Clock;
use App\Filament\Resources\AdmInformationResource;
use App\Operators\S3Operator;
use App\Services\AdmInformationService;
use App\Services\ConfigGetService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class CreateAdmInformation extends CreateRecord
{
    protected static string $resource = AdmInformationResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('create')->label('作成')
                ->action('create'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function handleRecordCreation(array $data): Model
    {
        /** @var S3Operator $s3Operator */
        $s3Operator = app(S3Operator::class);

        /** @var ConfigGetService $configGetService */
        $configGetService = app(ConfigGetService::class);

        /** @var AdmInformationService $admInformationService */
        $admInformationService = app(AdmInformationService::class);

        /** @var Clock $clock */
        $clock = app(Clock::class);

        $now = $clock->now();

        $user = Filament::auth()->user();
        $data['author_adm_user_id'] = $user->id;
        $data['approval_adm_user_id'] = null;

        $data['html'] = '';
        $htmlJson = $data['html_json'];
        // htmlタグを直接入力された際のエスケープ処理
        $htmlJson = html_entity_decode(
            tiptap_converter()->asHTML($htmlJson),
            ENT_QUOTES,
            'UTF-8',
        );
        $htmlJson = tiptap_converter()->asJSON($htmlJson, true);
        $imageSrcList = $admInformationService->getImageSrc($htmlJson['content']);
        $admInformationService->replaceImageSrcToS3Url($htmlJson['content']);
        $data['html_json'] = json_encode($htmlJson);

        $bannerLocalFilePath = $data['banner_local_file_path'] ?? '';
        unset($data['banner_local_file_path']);
        $bannerUrl = $data['banner_url'] ?? '';

        $data['content_change_at'] = $now->toDateTimeString();

        $data['id'] = Uuid::uuid4()->toString();
        $admInformation = static::getModel()::create($data);

        // html内の画像をアップロード
        foreach ($imageSrcList as $imageSrc) {
            $fileName = basename($imageSrc);
            $s3Operator->putFromFile(
                $admInformationService->getLocalHtmlImagePath($fileName),
                $admInformationService->getHtmlImageUploadPath($fileName),
                $configGetService->getS3WebviewBucket(),
            );
        }

        // バナー画像アップロード
        if ($admInformation->hasBanner()) {
            $s3Operator->putFromFile(
                $bannerLocalFilePath,
                $bannerUrl,
                $configGetService->getS3BannerBucket(),
            );

            // htmlファイル内での相対パスでバナー画像参照できるようにwebviewバケットにも保存する
            $s3Operator->putFromFile(
                $bannerLocalFilePath,
                $admInformationService->getHtmlImageUploadPath(basename($bannerUrl)),
                $configGetService->getS3WebviewBucket(),
            );
        }

        // Htmlファイルアップロード
        $admInformationService->uploadHtml($admInformation, $now, isCreate: true);

        // index.json更新
        $admInformationService->updateIndexJson($admInformation, $now);

        return $admInformation;
    }
}
