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
        ?int $stepNumber,
    ): void {
        $this->adm_user_id = $admUserId;
        $this->simulation_num = $playNum;
        $this->mst_gacha_data_hash = $mstGachaDataHash;
        $this->setSimulationDataByParams(
            $prizeType,
            $gachaPrizeSimulationResultEntities,
            $stepNumber,
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

    private function makeSimulationDataKey(string $prizeType, ?int $stepNumber = null): string
    {
        return is_null($stepNumber)
            ? $prizeType
            : "{$prizeType}-Step{$stepNumber}";
    }

    public function getSimulationDataByParams(string $prizeType, ?int $stepNumber = null): ?array
    {
        if (is_null($stepNumber)) {
            return $this->getSimulationDataByPrizeType($prizeType);
        } else {
            return $this->getSimulationDataByPrizeTypeAndStep($prizeType, $stepNumber);
        }
    }

    public function setSimulationDataByParams(
        string $prizeType,
        Collection $gachaPrizeSimulationResultEntities,
        ?int $stepNumber = null
    ): void {
        if (is_null($stepNumber)) {
            $this->setSimulationDataByPrizeType(
                $prizeType,
                $gachaPrizeSimulationResultEntities,
            );
        } else {
            $this->setSimulationDataByPrizeTypeAndStep(
                $prizeType,
                $gachaPrizeSimulationResultEntities,
                $stepNumber,
            );
        }
    }

    /**
     * @param string $prizeType
     * @return array<mixed>|null null: 該当データなし
     */
    private function getSimulationDataByPrizeType(string $prizeType): ?array
    {
        $simulationData = $this->getSimulationData();
        $key = $this->makeSimulationDataKey($prizeType, null);
        return $simulationData[$key] ?? null;
    }

    /**
     * @param string $prizeType
     * @param Collection<GachaPrizeSimulationResultEntity> $gachaPrizeSimulationResultEntities
     * @return void
     */
    private function setSimulationDataByPrizeType(
        string $prizeType,
        Collection $gachaPrizeSimulationResultEntities,
    ): void {
        $simulationData = $this->getSimulationData();

        $key = $this->makeSimulationDataKey($prizeType, null);

        $simulationData[$key] = $gachaPrizeSimulationResultEntities->map(
            function (GachaPrizeSimulationResultEntity $entity) {
                return $entity->formatToArray();
            })->all();

        $this->setSimulationData($simulationData);
    }

    /**
     * ステップ番号を考慮してシミュレーションデータを取得
     *
     * @param string $prizeType
     * @param int|null $stepNumber nullの場合は通常ガシャとして扱う
     * @return array|null
     */
    private function getSimulationDataByPrizeTypeAndStep(
        string $prizeType,
        ?int $stepNumber = null
    ): ?array {
        $simulationData = $this->getSimulationData();

        $key = $this->makeSimulationDataKey($prizeType, $stepNumber);

        return $simulationData[$key] ?? null;
    }

    /**
     * ステップ番号を考慮してシミュレーションデータを保存
     *
     * @param string $prizeType
     * @param Collection<GachaPrizeSimulationResultEntity> $gachaPrizeSimulationResultEntities
     * @param int|null $stepNumber nullの場合は通常ガシャとして扱う
     * @return void
     */
    private function setSimulationDataByPrizeTypeAndStep(
        string $prizeType,
        Collection $gachaPrizeSimulationResultEntities,
        ?int $stepNumber = null
    ): void {
        $simulationData = $this->getSimulationData();

        $key = $this->makeSimulationDataKey($prizeType, $stepNumber);

        $simulationData[$key] = $gachaPrizeSimulationResultEntities->map(
            function (GachaPrizeSimulationResultEntity $entity) {
                return $entity->formatToArray();
            }
        )->all();

        $this->setSimulationData($simulationData);
    }
}
