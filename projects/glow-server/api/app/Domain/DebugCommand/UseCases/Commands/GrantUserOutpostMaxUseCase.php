<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Outpost\Models\UsrOutpostEnhancement;
use App\Domain\Resource\Mst\Models\MstOutpostEnhancement;
use App\Domain\Resource\Mst\Models\MstOutpostEnhancementLevel;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class GrantUserOutpostMaxUseCase extends BaseCommands
{
    protected string $name = 'ユーザーの所持のゲートレベルMAX';
    protected string $description = 'ユーザーの所持ゲートレベルをMAXにします';

    public function __construct(
        private Clock $clock,
    ) {
    }

    /**
     * デバッグ機能: MstOutpostEnhancementに設定されている最大LVでゲートレベルをMAXにする
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        // MstOutpostEnhancementLevel mst_outpost_enhancement_idごとに最大レベルを取得
        $mstOutpostEnhancementLevel = MstOutpostEnhancementLevel::select(
            DB::raw('mst_outpost_enhancement_id, MAX(level) AS max_level')
        )
            ->groupBy('mst_outpost_enhancement_id')
            ->get();
        $mstOutpostEnhancementLevel = $mstOutpostEnhancementLevel->pluck("max_level", "mst_outpost_enhancement_id");

        // マスタのゲートレベルを取得
        $mstOutpostEnhancements = MstOutpostEnhancement::query()->get();

        // ユーザーの所持しているゲートレベルを取得
        $usrOutpostEnhancements = UsrOutpostEnhancement::query()
            ->where('usr_user_id', $user->id)
            ->get()
            ->keyBy('mst_outpost_enhancement_id');

        // 更新するデータをまとめる
        $updateOutpostEnhancement = [];
        foreach ($mstOutpostEnhancements as $mstOutpostEnhancement) {
            // ユーザーの所持しているゲートレベルがない場合は新規作成
            if (!isset($usrOutpostEnhancements[$mstOutpostEnhancement->id])) {
                $uuid = (string) Uuid::uuid4();
                $updateOutpostEnhancement[] = [
                    'id' => $uuid,
                    'usr_user_id' => $user->id,
                    'mst_outpost_id' => $mstOutpostEnhancement->mst_outpost_id,
                    'mst_outpost_enhancement_id' => $mstOutpostEnhancement->id,
                    'level' => $mstOutpostEnhancementLevel[$mstOutpostEnhancement->id],
                    'created_at' => $this->clock->now(),
                    'updated_at' => $this->clock->now(),
                ];
            } else {
                // ユーザーの所持しているゲートレベルがある場合は更新
                $usrOutpostEnhancement = $usrOutpostEnhancements[$mstOutpostEnhancement->id];
                $updateOutpostEnhancement[] = [
                    'id' => $usrOutpostEnhancement->id,
                    'usr_user_id' => $usrOutpostEnhancement->usr_user_id,
                    'mst_outpost_id' => $usrOutpostEnhancement->mst_outpost_id,
                    'mst_outpost_enhancement_id' => $usrOutpostEnhancement->mst_outpost_enhancement_id,
                    'level' => $mstOutpostEnhancementLevel[$usrOutpostEnhancement->mst_outpost_enhancement_id],
                    'created_at' => $usrOutpostEnhancement->created_at,
                    'updated_at' => $this->clock->now(),
                ];
            }
        }

        // UsrOutpostEnhancementのレコードを一括で更新
        UsrOutpostEnhancement::upsert(
            $updateOutpostEnhancement,
            ['usr_user_id', 'mst_outpost_enhancement_id'],
            ['level', 'updated_at']
        );
    }
}
