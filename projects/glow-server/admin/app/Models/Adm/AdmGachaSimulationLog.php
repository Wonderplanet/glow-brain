<?php

namespace App\Models\Adm;

use App\Constants\ReportStatus;
use App\Entities\GachaSimulator\GachaPrizeSimulationResultEntity;
use Illuminate\Support\Collection;

class AdmGachaSimulationLog extends AdmModel
{

    protected $table = 'adm_gacha_simulation_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'adm_user_id',
        'opr_gacha_id',
        'simulation_num',
        'mst_gacha_data_hash',
        'simulation_data',
        'report_status',
        'simulated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
    ];

    public static function create(string $oprGachaId): self
    {
        $model = new self();
        $model->id = $model->newUniqueId();
        $model->opr_gacha_id = $oprGachaId;
        return $model;
    }

    public static function getOrCreate(string $oprGachaId): self
    {
        $model = self::query()
            ->where('opr_gacha_id', $oprGachaId)
            ->first();

        if (!is_null($model)) {
            return $model;
        }

        return self::create($oprGachaId);
    }

    /**
     * シミュレーションの内容を更新する
     *
     * @param Collection<GachaPrizeSimulationResultEntity> $gachaPrizeSimulationResultEntities
     * @return void
     */
    public function updateWithSimulation(
        string $admUserId,
        int $playNum,
        string $mstGachaDataHash,
        string $prizeType,
        Collection $gachaPrizeSimulationResultEntities,
        \DateTimeImmutable $now,
    ): void {
        $this->adm_user_id = $admUserId;
        $this->simulation_num = $playNum;
        $this->mst_gacha_data_hash = $mstGachaDataHash;
        $this->setSimulationDataByPrizeType(
            $prizeType,
            $gachaPrizeSimulationResultEntities,
        );
        $this->report_status = ReportStatus::BEFORE_REPORTING->value;
        $this->simulated_at = $now;
        $this->save();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function hasSimulated(): bool
    {
        return !is_null($this->simulation_data);
    }

    public function getSimulationData(): array
    {
        return json_decode($this->simulation_data ?? '[]', true) ?? [];
    }

    public function setSimulationData(array $data): void
    {
        $this->simulation_data = json_encode($data);
    }

    /**
     * @param string $prizeType
     * @return array<mixed>|null null: 該当データなし
     */
    public function getSimulationDataByPrizeType(string $prizeType): ?array
    {
        $simulationData = $this->getSimulationData();
        return $simulationData[$prizeType] ?? null;
    }

    /**
     * @param string $prizeType
     * @param Collection<GachaPrizeSimulationResultEntity> $gachaPrizeSimulationResultEntities
     * @return void
     */
    public function setSimulationDataByPrizeType(
        string $prizeType,
        Collection $gachaPrizeSimulationResultEntities,
    ): void {
        $simulationData = $this->getSimulationData();
        $simulationData[$prizeType] = $gachaPrizeSimulationResultEntities->map(
            function (GachaPrizeSimulationResultEntity $entity) {
                return $entity->formatToArray();
            })->all();

        $this->setSimulationData($simulationData);
    }
}
