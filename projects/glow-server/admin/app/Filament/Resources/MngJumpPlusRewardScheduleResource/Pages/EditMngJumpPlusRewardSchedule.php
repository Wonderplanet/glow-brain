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
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class EditMngJumpPlusRewardSchedule extends EditRecord
{
    use DatabaseTransactionTrait;
    use MngCacheDeleteTrait;

    protected static string $resource = MngJumpPlusRewardScheduleResource::class;

    /**
     * レコード読み込み時に報酬データもフォームにセットする
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $rewards = $this->record->mng_jump_plus_rewards->map(function ($reward) {
            return [
                'id' => $reward->id,
                'resource_type' => $reward->resource_type,
                'resource_id' => $reward->resource_id,
                'resource_amount' => $reward->resource_amount,
            ];
        })->toArray();

        $data['rewards'] = $rewards;

        return $data;
    }

    /**
     * 更新処理をオーバーライドして報酬データも同時に更新する
     */
    protected function handleRecordUpdate(Model $mngSchedule, array $data): Model
    {
        $jumpPlusRewardService = app(JumpPlusRewardService::class);

        /** @var MngJumpPlusRewardSchedule $mngSchedule */

        return $this->transaction(function () use ($mngSchedule, $data, $jumpPlusRewardService) {
            $mngSchedule = $jumpPlusRewardService->createOrUpdateMngJumpPlusRewardSchedule(
                $mngSchedule, // 既存のスケジュールを更新
                $data,
            );

            // 更新完了通知
            Notification::make()
                ->title('ジャンプ+報酬スケジュールを更新しました')
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
     * 更新時にエラーが発生した場合の処理
     */
    protected function onEditRecordException(\Throwable $exception): void
    {
        Notification::make()
            ->title('エラーが発生しました')
            ->body($exception->getMessage())
            ->danger()
            ->send();

        Log::error('ジャンプ+報酬スケジュール更新エラー', [
            'exception' => $exception,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
