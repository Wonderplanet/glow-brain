<?php

namespace App\Filament\Resources\MngClientVersionResource\Pages;

use App\Filament\Resources\MngClientVersionResource;
use App\Models\Mng\MngClientVersion;
use App\Traits\DatabaseTransactionTrait;
use App\Traits\MngCacheDeleteTrait;
use App\Traits\NotificationTrait;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Constants\MasterAssetReleaseConstants;

class CreateMngClientVersion extends CreateRecord
{
    use DatabaseTransactionTrait;
    use MngCacheDeleteTrait;
    use NotificationTrait;

    protected static string $resource = MngClientVersionResource::class;

    /**
     * 作成ボタン実行
     *
     * @param bool $another
     * @return void
     * @throws \Filament\Support\Exceptions\Halt
     * @throws \Throwable
     */
    public function create(bool $another = false): void
    {
        $formData = $this->form->getState();
        $platform = (int) $formData['platform'];

        if ($platform === MasterAssetReleaseConstants::PLATFORM_ALL) {
            $platforms = [PlatformConstant::PLATFORM_IOS, PlatformConstant::PLATFORM_ANDROID];
        } else {
            $platforms = [$platform];
        }

        // 全プラットフォームを選択した場合はそれぞれ登録する
        foreach ($platforms as $platform) {
            MngClientVersion::query()->create([
                'platform' => $platform,
                'client_version' => $formData['client_version'],
                'is_force_update' => $formData['is_force_update'] ?? false,
            ]);
        }

        // キャッシュ削除
        $this->deleteMngClientVersionCache();

        $this->getCreatedNotification()?->send();
        $this->redirect($this->getRedirectUrl());
    }

    /**
     * 作成時にエラーが発生した場合の処理
     */
    protected function onCreateRecordException(\Throwable $exception): void
    {
        $this->sendDangerNotification(
            'エラーが発生しました',
            $exception->getMessage(),
        );

        Log::error('クライアントバージョン管理データ作成エラー', [
            'exception' => $exception,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
