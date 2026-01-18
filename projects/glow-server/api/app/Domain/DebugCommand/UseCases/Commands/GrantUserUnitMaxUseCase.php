<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUnitGradeUp;
use App\Domain\Resource\Mst\Models\MstUnitLevelUp;
use App\Domain\Resource\Mst\Models\MstUnitRankUp;
use App\Domain\Resource\Mst\Models\MstUnitSpecificRankUp;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class GrantUserUnitMaxUseCase extends BaseCommands
{
    protected string $name = 'ユーザーの所持ユニット全付与＆MAX';
    protected string $description = 'ユーザーの所持ユニットを全付与＆MAXにします';

    public function __construct(
        private Clock $clock,
    ) {
    }

    /**
     * デバッグ機能: Mst_unitsに入っているデータを全て所持ユニットにレベル等をMAXで付与する
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        // Unitのマスタを取得
        $mstUnits = MstUnit::query()->get();

        // MstUnitLevelUp に設定されているunit_labelごとの最大レベルを取得
        $mstUnitLevelUps = MstUnitLevelUp::select(DB::raw('unit_label, MAX(level) AS max_level'))
            ->groupBy('unit_label')
            ->get();
        $mstUnitLevelUps = $mstUnitLevelUps->pluck("max_level", "unit_label");

        // MstUnitGradeUp に設定されているunit_labelごとの最大レベルを取得
        $mstUnitGradeUps = MstUnitGradeUp::select(DB::raw('unit_label, MAX(grade_level) AS max_level'))
            ->groupBy('unit_label')
            ->get();
        $mstUnitGradeUps = $mstUnitGradeUps->pluck("max_level", "unit_label");

        // MstUnitRankUp に設定されているunit_labelごとの最大レベルを取得
        $mstUnitRankUps = MstUnitRankUp::select(DB::raw('unit_label, MAX(`rank`) AS max_rank'))
            ->groupBy('unit_label')
            ->get();
        $mstUnitRankUps = $mstUnitRankUps->pluck("max_rank", "unit_label");

        $mstUnitSpecificRankUps = MstUnitSpecificRankUp::select(DB::raw('mst_unit_id, MAX(`rank`) AS max_rank'))
            ->groupBy('mst_unit_id')
            ->get();
        $mstUnitSpecificRankUps = $mstUnitSpecificRankUps->pluck("max_rank", "mst_unit_id");

        // まずユーザーの所持しているユニットを取得（アップデート対象）
        $usrUnits = UsrUnit::query()
            ->where('usr_user_id', $user->id)
            ->get();
        $usrUnitIds = $usrUnits->pluck("id", "mst_unit_id");
        $usrUnitCreateAts = $usrUnits->pluck("created_at", "mst_unit_id");

        // 投入するデータをまとめる
        $usrUnits = [];
        foreach ($mstUnits as $mstUnit) {
            // 新規追加の場合は、UUIDを生成
            $uuid = (string) Uuid::uuid4();
            if (isset($usrUnitIds[$mstUnit->id])) {
                // すでに所持している場合は、UUIDとcreated_atを引き継ぐ
                $uuid = $usrUnitIds[$mstUnit->id];
                $createdAt = $usrUnitCreateAts[$mstUnit->id];
            } else {
                // createdAtは現在時刻を設定
                $createdAt = $this->clock->now();
            }

            $rankUp = $mstUnitRankUps[$mstUnit->unit_label];
            // mstUnit.SpecificRankUpsの値を取得
            if ($mstUnit->has_specific_rank_up === 1) {
                // has_specific_rank_up が1の場合はmstUnitSpecificRankUpsから値を取得
                $rankUp = $mstUnitSpecificRankUps[$mstUnit->id];
            }

            $usrUnits[] = [
                'id' => $uuid,
                'usr_user_id' => $user->id,
                'mst_unit_id' => $mstUnit->id,
                'level' => $mstUnitLevelUps[$mstUnit->unit_label],
                'grade_level' => $mstUnitGradeUps[$mstUnit->unit_label],
                'rank' => $rankUp,
                'created_at' => $createdAt,
                'updated_at' => $this->clock->now(),
            ];
        }

        // UsrUnitのレコードを一括で挿入
        UsrUnit::upsert($usrUnits, 'id');
    }
}
