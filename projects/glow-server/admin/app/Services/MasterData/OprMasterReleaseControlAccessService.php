<?php

namespace App\Services\MasterData;

use App\Models\Opr\OprMasterReleaseControl;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OprMasterReleaseControlAccessService
{
    // リリース制御のうち、現在有効なマスターを指しているレコードを取得する
    public function selectActiveOprMasterReleaseControl(Carbon $now = new Carbon()): ?OprMasterReleaseControl
    {
        $masterReleaseControl = OprMasterReleaseControl::select('*')
            ->where('release_at', '<=', $now)
            ->orderBy('release_at', 'desc')
            ->orderBy('updated_at', 'desc')
            ->first();

        return $masterReleaseControl;
    }

    // リリース制御のうち、現在有効なマスターを指しているレコードを取得する
    public function selectActiveOprMasterReleaseControlAll(Carbon $now = new Carbon()): Collection
    {
        $masterReleaseControls = OprMasterReleaseControl::select('*')
            ->where('release_at', '<=', $now)
            ->orderBy('release_at', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();

        return $masterReleaseControls;
    }

    // リリースキー指定で、有効になるマスターを指しているレコードを取得する
    public function selectActiveOprMasterReleaseControlByReleaseKey(string $releaseKey): ?OprMasterReleaseControl
    {
        $masterReleaseControl = OprMasterReleaseControl::select('*')
            ->where('release_key', '=', $releaseKey)
            ->orderBy('release_at', 'desc')
            ->orderBy('updated_at', 'desc')
            ->first();

        return $masterReleaseControl;
    }

    // opr_master_release_controlに登録されているrelease_key一覧を取得
    public function selectReleaseKeys(): array
    {
        $masterReleaseControls = OprMasterReleaseControl::select('*')
            ->get();

        return $masterReleaseControls->pluck('release_key')->toArray();
    }

    // opr_master_release_controlに登録されている最も未来のrelease_keyを取得
    public function selectMostFutureReleaseKey(): string
    {
        $masterReleaseControl = OprMasterReleaseControl::select('*')
            ->orderBy('release_at', 'desc')
            ->orderBy('updated_at', 'desc')
            ->first();

        return is_null($masterReleaseControl) ? '' : $masterReleaseControl->release_key;

    }
}
