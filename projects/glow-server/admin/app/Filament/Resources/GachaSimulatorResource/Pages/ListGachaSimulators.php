<?php

namespace App\Filament\Resources\GachaSimulatorResource\Pages;

use App\Filament\Resources\GachaSimulatorResource;
use App\Models\Adm\AdmGachaSimulationLog;
use App\Models\Mst\OprGacha;
use App\Models\Mst\OprGachaPrize;
use App\Services\GachaSimulatorService;
use App\Traits\PageTrait;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

class ListGachaSimulators extends ListRecords
{
    use PageTrait;

    protected static string $resource = GachaSimulatorResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTableRecords(): Paginator
    {
        return $this->augmentPaginatorWithCallback(
            function (Paginator $paginator) {
                $this->addToPaginatedRecords(
                    $paginator,
                );
            }
        );
    }

    /**
     * ページネートで取得したレコードを使って一覧に必要な情報を追加する
     * @param \Illuminate\Contracts\Pagination\Paginator $paginator
     * @return void
     */
    private function addToPaginatedRecords(
        Paginator $paginator,
    ): void {
        /**
         * @var Collection<OprGacha> $paginatedOprGachas
         */
        $paginatedOprGachas = $paginator->getCollection();

        $gachaSimulatorService = app()->make(GachaSimulatorService::class);

        $admGachaSimulationLogs = AdmGachaSimulationLog::query()
            ->whereIn('opr_gacha_id', $paginatedOprGachas->pluck('id')->all())
            ->get()
            ->keyBy('opr_gacha_id');

        $oprGachaPrizesByOprGachaId = $gachaSimulatorService->getOprGachaPrizesByOprGachas($paginatedOprGachas);

        // ページネートされたデータにセット
        foreach ($paginatedOprGachas as $oprGacha) {
            /** @var \App\Models\Mst\OprGacha $oprGacha */
            $oprGachaEntity = $oprGacha->toEntity();

            // シミュレーションログをセット
            $admGachaSimulationLog = $admGachaSimulationLogs->get($oprGachaEntity->getId());
            $oprGacha->adm_gacha_simulation_log = $admGachaSimulationLog;

            $gachaSimulatorService->checkAndSetChangedMstDataHash(
                $oprGacha,
                $oprGachaPrizesByOprGachaId->get($oprGacha->id) ?? collect(),
                $admGachaSimulationLog,
            );
        }

        $paginator->setCollection($paginatedOprGachas);
    }
}
