<?php

namespace App\Console\Commands;

use App\Models\Adm\AdmUserDeletionOperateHistory;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class AdmUserDeletionOperateHistoriesLogDeleteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:adm-user-deletion-operate-histories-log-delete-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '一定期間が過ぎたadm_user_deletion_operate_historiesのprofile_dataをnullに更新する';

    /**
     * 一定期間が過ぎたadm_user_deletion_operate_historiesのprofile_dataをnullに更新する
     */
    public function handle()
    {
        // 毎日0時に期限切れデータを削除
        $admUserDeletionOperateHistories = AdmUserDeletionOperateHistory::query()
            ->where('expires_at', '<=', CarbonImmutable::now())
            ->whereNotNull('profile_data')
            ->get();

        // 対象データがない場合は早期終了
        if ($admUserDeletionOperateHistories->isEmpty()) {
            return;
        }

        $ids = $admUserDeletionOperateHistories->pluck('id')->toArray();

        AdmUserDeletionOperateHistory::query()
            ->whereIn('id', $ids)
            ->update(['profile_data' => null]);
    }
}
