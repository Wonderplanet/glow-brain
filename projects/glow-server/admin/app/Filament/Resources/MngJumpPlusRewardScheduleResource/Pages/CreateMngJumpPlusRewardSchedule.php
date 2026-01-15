<?php

namespace App\Filament\Resources\MngJumpPlusRewardScheduleResource\Pages;

use App\Constants\Database;
use App\Filament\Resources\MngJumpPlusRewardScheduleResource;
use App\Models\Mng\MngJumpPlusReward;
use App\Models\Mng\MngJumpPlusRewardSchedule;
use App\Services\JumpPlusRewardService;
use App\Traits\DatabaseTransactionTrait;
use App\Traits\MngCacheDeleteTrait;
use App\Utils\StringUtil;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class CreateMngJumpPlusRewardSchedule extends CreateRecord
{
    use DatabaseTransactionTrait;
    use MngCacheDeleteTrait;

    protected static string $resource = MngJumpPlusRewardScheduleResource::class;

    /**
     * 作成処理をオーバーライドして報酬データも同時に保存する
     */
    protected function handleRecordCreation(array $data): Model
    {
        $jumpPlusRewardService = app(JumpPlusRewardService::class);

        return $this->transaction(function () use ($data, $jumpPlusRewardService) {
            $mngSchedule = $jumpPlusRewardService->createOrUpdateMngJumpPlusRewardSchedule(
                null, // 新規作成なのでnull
                $data,
            );

            // 作成完了通知
            Notification::make()
                ->title('ジャンプ+報酬スケジュールを作成しました')
                ->success()
                ->send();

            // キャッシュ削除
            $this->deleteMngJumpPlusRewardCache();

            // s3に最新のマスタjsonをアップロード
            // DBとLambda処理で状態を揃えるために、ここで失敗したらrollbackする
            $jumpPlusRewardService->createAndUploadValidationMaster();

            return $mngSchedule;
        }, [Database::MANAGE_DATA_CONNECTION]);
    }

    /**
     * 作成時にエラーが発生した場合の処理
     */
    protected function onCreateRecordException(\Throwable $exception): void
    {
        Notification::make()
            ->title('エラーが発生しました')
            ->body($exception->getMessage())
            ->danger()
            ->send();

        Log::error('ジャンプ+報酬スケジュール作成エラー', [
            'exception' => $exception,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
