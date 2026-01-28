<?php

namespace App\Filament\Pages;

use App\Constants\GachaSetName;
use App\Domain\Gacha\Enums\GachaPrizeType;
use App\Domain\Gacha\Services\GachaService;
use App\Entities\GachaSimulator\GachaPrizeSimulationResultEntity;
use App\Entities\GachaSimulator\GachaSimulateResultEntity;
use App\Filament\Authorizable;
use App\Filament\Resources\GachaSimulatorResource;
use App\Models\Adm\AdmGachaSimulationLog;
use App\Models\Mst\MstUnitI18n;
use App\Models\Mst\OprGacha;
use App\Models\Mst\OprGachaPrize;
use App\Services\AdminGachaService;
use App\Services\GachaSimulationExcelService;
use App\Services\GachaSimulatorService;
use App\Traits\NotificationTrait;
use App\Traits\RewardInfoGetTrait;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class GachaSimulator extends Page
{
    use RewardInfoGetTrait;
    use NotificationTrait;
    use Authorizable;

    /** 最大シミュレーション試行回数 */
    private const MAX_SIMULATION_COUNT = 10000000;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static bool $shouldRegisterNavigation = false; // 直接は遷移できないようにする
    protected static string $view = 'filament.pages.gacha-simulator';
    protected static ?string $title = 'ガシャシミュレーター詳細';

    public string $oprGachaId = '';
    public string $oprGachaType = '';
    public array $simulationResults = [];
    public bool $rangeCheckErrorFlg = false;
    public bool $hasSimulationData = false;
    public bool $hasRangeCheckError = false;

    public GachaPrizeType $prizeType = GachaPrizeType::REGULAR;

    protected $queryString = [
        'oprGachaId',
    ];

    protected $gachaSimulatorService = null;
    protected $gachaSimulationExcelService = null;
    protected $gachaService = null;
    protected $adminGachaService = null;
    public function __construct()
    {
        $this->gachaSimulatorService = app()->make(GachaSimulatorService::class);
        $this->gachaSimulationExcelService = app()->make(GachaSimulationExcelService::class);
        $this->gachaService = app()->make(GachaService::class);
        $this->adminGachaService = app()->make(AdminGachaService::class);
    }

    protected array $breadcrumbList = [];
    public int $status = 0;
    public string $message = '';
    public string $messageBackgroundColor = '';
    public int $minimumPlayNum = 0;
    public ?int $customPlayNum = null;

    public function mount()
    {
        $this->breadcrumbList();
        $this->minimumPlayNum = $this->getMinimumSimulationNum();

        // 初期データの設定
        if (count($this->simulationResults) === 0) {
            $this->updatedPrizeType();
        }
    }

    private function breadcrumbList()
    {
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            GachaSimulatorResource::getUrl() => 'ガシャシミュレーター一覧',
            GachaSimulator::getUrl(['oprGachaId' => $this->oprGachaId]) => 'ガシャシミュレーター詳細',
        ]);
    }

    private function getBasicInfo(): array
    {

        $oprGacha = OprGacha::query()
            ->where('id', $this->oprGachaId)
            ->first();
        $this->oprGachaType = $oprGacha->gacha_type_label;

        return [
            'ガシャID' => $oprGacha->id,
            'ガシャ名' => $oprGacha->opr_gacha_i18n->name,
            'ガシャタイプ' => $oprGacha->gacha_type_label,
            '説明' => $oprGacha->opr_gacha_i18n->description,
            '開始日時' => $oprGacha->start_at,
            '終了日時' => $oprGacha->end_at,
        ];
    }

    private function getPickUp(): array
    {
        $oprGacha = OprGacha::query()
            ->where('id', $this->oprGachaId)
            ->first();

        $oprGachaPrizes = OprGachaPrize::query()
            ->where('group_id', $oprGacha->prize_group_id)
            ->where('pickup', 1)
            ->get();

        $resourceIds = $oprGachaPrizes->pluck('resource_id')->toArray();

        $mstUnits = MstUnitI18n::query()
            ->whereIn('mst_unit_id', $resourceIds)
            ->get();

        $pickUpList = [];
        foreach ($mstUnits as $mstUnit) {
            $pickUpList[] = [
                'キャラ名' => $mstUnit->name,
            ];
        }
        return $pickUpList;
    }

    private function getUpperTableRows(): array
    {
        $upperTableRows = [];
        $oprGacha = OprGacha::query()
            ->with([
                'opr_gacha_i18n',
                'opr_gacha_uppers',
            ])
            ->where('id', $this->oprGachaId)
            ->first();

        $oprGachaUppers = $oprGacha->opr_gacha_uppers;
        $oprGachaI18n = $oprGacha?->opr_gacha_i18n;

        foreach ($oprGachaUppers as $oprGachaUpper) {
            $row = [
                '天井グループ' => $oprGachaUpper->upper_group,
                '天井タイプ' => $oprGachaUpper->upper_type_label,
                '天井保証回数' => $oprGachaUpper->count,
                '天井文言' => '',
            ];

            if (!is_null($oprGachaI18n)) {
                $row['天井文言'] = $oprGachaI18n->getUpperDescription($oprGachaUpper->getUpperType());
            }

            $upperTableRows[] = $row;
        }

        return $upperTableRows;
    }

    public function getRarityProbability(): array
    {
        $generateGachaProbability = $this->gachaService->generateGachaProbability($this->oprGachaId);
        $generateGachaProbabilityData = $generateGachaProbability->formatToResponse();

        $raritys = array_column($generateGachaProbabilityData['rarityProbabilities'], 'rarity');
        array_multisort($raritys, SORT_DESC, $generateGachaProbabilityData['rarityProbabilities']);
        foreach ($generateGachaProbabilityData['rarityProbabilities'] as $rarityProbabilities) {
            $row = [
                'レアリティ' => $rarityProbabilities['rarity'],
                '提供割合（％）' => $rarityProbabilities['probability'],
            ];

            $upperTableRows[] = $row;
        }
        return $upperTableRows;
    }

    /**
     * シミュレーション実行前の空のデータを用意する
     * @return Collection<GachaPrizeSimulationResultEntity>
     */
    public function getEmptySimulationResults(): Collection
    {
        $oprGachaId = $this->oprGachaId;
        $oprGacha = OprGacha::query()->where('id', $oprGachaId)->first()->toEntity();

        $gachaLotteryBoxData = $this->gachaService->getGachaLotteryBox($oprGacha);
        $gachaBoxes = $this->adminGachaService->getGachaBoxesByPrizeType(
            $gachaLotteryBoxData,
            $this->prizeType
        );
        $gachaSimulateResultEntity = new GachaSimulateResultEntity($gachaBoxes, collect(), $this->prizeType, $this->getActualPlayNum());

        return $this->getSimulationResults($gachaSimulateResultEntity);
    }

    /**
     * @return Collection<GachaPrizeSimulationResultEntity>
     */
    public function getSimulationResultsByAdmGachaSimulationLog(AdmGachaSimulationLog $admGachaSimulationLog): Collection
    {
        $targetSimulationData = $admGachaSimulationLog->getSimulationDataByPrizeType($this->prizeType->value);

        if (is_null($targetSimulationData)) {
            return collect();
        }

        return collect($targetSimulationData)
            ->map(function ($ar) {
                return GachaPrizeSimulationResultEntity::createFromArray($ar);
            });
    }

    /**
     * シミュレーション実行
     */
    public function simulation(): void
    {
        // 試行回数次第で処理時間が多くなるため、最大実行時間を調整する
        // TODO: 処理時間長すぎるとブラウザ側が止まり504GatewayTimeOutになるのでバックグラウンドジョブ化するのが望ましい
        ini_set('max_execution_time', 900); // 15分

        $oprGachaId = $this->oprGachaId;
        $oprGacha = OprGacha::query()->where('id', $oprGachaId)->first();
        $oprGachaEntity = $oprGacha->toEntity();
        $oprGachaPrizes = $this->gachaSimulatorService->getOprGachaPrizesByOprGachas(collect([$oprGacha]))
            ->get($oprGacha->id, collect());

        // ガチャ抽選BOX取得
        $gachaLotteryBoxData = $this->gachaService->getGachaLotteryBox($oprGachaEntity);

        //実際の試行回数を取得
        $playNum = $this->getActualPlayNum();
        // ガシャ抽選
        $gachaSimulateResultEntity = $this->adminGachaService->simulateDraw(
            $oprGachaId,
            $gachaLotteryBoxData,
            $playNum,
            $this->prizeType
        );

        $gachaPrizeSimulationResultEntities = $this->getSimulationResults($gachaSimulateResultEntity);

        $mstGachaDataHash = $this->gachaSimulatorService->makeMstGachaDataHash(
            $oprGacha,
            $oprGachaPrizes,
        );

        $now = CarbonImmutable::now();
        $admGachaSimulationLog = AdmGachaSimulationLog::getOrCreate($oprGachaId);
        $admGachaSimulationLog->updateWithSimulation(
            auth()->id(),
            $playNum,
            $mstGachaDataHash,
            $this->prizeType->value,
            $gachaPrizeSimulationResultEntities,
            $now,
        );

        $this->setSimulationResults($gachaPrizeSimulationResultEntities);

        // シミュレーション実行後はデータありフラグをtrueに設定
        $this->hasSimulationData = true;
        // rangeCheckエラーの有無をチェック
        $this->hasRangeCheckError = $this->gachaSimulatorService->hasRangeCheckError($gachaPrizeSimulationResultEntities);
    }

    /**
     * @return Collection<GachaPrizeSimulationResultEntity>
     */
    private function getSimulationResults(GachaSimulateResultEntity $gachaSimulateResultEntity): Collection
    {
        $gachaPrizeSimulationResultEntities = $this->gachaSimulatorService->createGachaPrizeSimulationResultEntities(
            $gachaSimulateResultEntity,
        );

        if ($this->gachaSimulatorService->hasRangeCheckError($gachaPrizeSimulationResultEntities)) {
            $this->rangeCheckErrorFlg = true;
        }

        return $gachaPrizeSimulationResultEntities;
    }

    /**
     * シミュレーション実行確認ボタン
     */
    public function simulationButton(): Action
    {
        return Action::make('simulationButton')
            ->label('シミュレーション実行')
            ->requiresConfirmation()
            ->modalDescription(new HtmlString('シミュレーションを実行しますか？'))
            ->modalSubmitActionLabel('OK')
            ->modalCancelActionLabel('キャンセル')
            ->action(
                function () {
                    try {
                        $this->simulation();
                        $this->sendProcessCompletedNotification(
                            'シミュレーションを完了しました。',
                            sprintf(
                                "ガシャID: %s, ガシャタイプ: %s, 試行回数: %d, 抽選枠: %s",
                                $this->oprGachaId,
                                $this->oprGachaType,
                                $this->getActualPlayNum(),
                                GachaSetName::getLabelfromGachaPrizeType($this->prizeType->value),
                            )
                        );
                    } catch (\Exception $e) {
                        Log::error('', [$e]);
                        $this->sendDangerNotification(
                            'シミュレーションに失敗しました',
                            $e->getMessage()
                        );
                    }
                }
            );
    }

    /**
     * シミュレーション結果をダウンロード
     */
    public function simulationReportDownloadButton(): Action
    {
        $admGachaSimulationLog = AdmGachaSimulationLog::query()
            ->where('opr_gacha_id', $this->oprGachaId)
            ->first();

        return Action::make('simulationReportDownloadButton')
            ->label('結果Download')
            ->icon('heroicon-o-arrow-down-tray')
            ->disabled(fn () => $admGachaSimulationLog === null || !$this->hasSimulationData)
            ->action(
                function () use ($admGachaSimulationLog) {
                    try {
                        $oprGacha = OprGacha::query()
                            ->where('id', $admGachaSimulationLog->opr_gacha_id)
                            ->first();
                        $simulationDate = CarbonImmutable::parse($admGachaSimulationLog->simulated_at);

                        return $this->gachaSimulationExcelService->downloadGachaResults(
                            $oprGacha->id,
                            $oprGacha->gacha_type_label,
                            $simulationDate,
                            $admGachaSimulationLog->getSimulationDataByPrizeType($this->prizeType->value) ?? [],
                            $oprGacha->opr_gacha_i18n->name,
                            $this->prizeType,
                        );
                    } catch (\Exception $e) {
                        Log::error('', [$e]);
                        $this->sendDangerNotification(
                            'EXCELファイルのダウンロードに失敗しました。',
                            $e->getMessage(),
                        );
                    }
                }
            );
    }

    // シミュレーション結果の報告の送信ボタン押下時に呼ばれる
    public function successButton(): Action
    {
        return Action::make('successButton')
            ->label('報告')
            ->requiresConfirmation()
            ->modalCloseButton(false)
            ->modalIcon('heroicon-o-check-circle')
            ->modalIconColor('success')
            ->modalCancelAction(false)
            ->modalDescription('報告が完了しました')
            ->modalSubmitActionLabel('OK')
            ->action(function () {
                $this->redirect(
                    GachaSimulator::getUrl(['oprGachaId' => $this->oprGachaId]),
                );
            });
    }

    /**
     * 失敗時のエラーボタン
     *
     * @return Action
     */
    public function errorButton(): Action
    {
        return Action::make('errorButton')
            ->label('エラー')
            ->requiresConfirmation()
            ->modalCloseButton(false)
            ->closeModalByClickingAway(false)
            ->modalIcon('heroicon-o-x-circle')
            ->modalIconColor('danger')
            ->modalDescription(function (array $arguments) {
                $message = $arguments['error_message'] ?? 'エラーが発生しました';
                return new HtmlString($message);
            })
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('閉じる');
    }

    /**
     * 最低排出率からシミュレーション数を判定し返す
     *
     * @return int 最低シミュレーション数
     */
    private function getMinimumSimulationNum()
    {
        $oprGachas = OprGacha::query()
            ->where('id', $this->oprGachaId)
            ->first();

        $oprGachaPrizes = OprGachaPrize::query()
            ->where('group_id', $oprGachas->prize_group_id)
            ->get()
            ->sortBy('id');

        //最低排出率の取得
        $totalWeight = $oprGachaPrizes->sum('weight');
        if ($totalWeight === 0) {
            // 重みが0の場合はシミュレーション数を0に設定
            return 0;
        }
        $minRate =  ($oprGachaPrizes->min('weight') / $totalWeight) * 100;

        return $this->gachaSimulatorService->getSimulationNum($minRate);
    }

    /**
     * 実際に使用する試行回数を取得
     *
     * @return int 実際の試行回数
     */
    private function getActualPlayNum(): int
    {
        // カスタム試行回数が設定されていて、最低回数以上の場合はそれを使用
        if ($this->customPlayNum !== null && $this->customPlayNum >= $this->minimumPlayNum) {
            return $this->customPlayNum;
        }

        // それ以外は最低回数を使用
        return $this->minimumPlayNum;
    }

    /**
     * 最大シミュレーション試行回数を取得
     */
    public function getMaxSimulationCount(): int
    {
        return self::MAX_SIMULATION_COUNT;
    }

    /**
     * カスタム試行回数のバリデーション
     */
    public function updatedCustomPlayNum()
    {
        if ($this->customPlayNum === null) {
            return;
        }

        if ($this->customPlayNum < $this->minimumPlayNum) {
            $this->customPlayNum = $this->minimumPlayNum;
            $this->sendDangerNotification(
                "試行回数は最低 {$this->minimumPlayNum} 回以上である必要があります。",
                "",
            );
            return;
        }

        if ($this->customPlayNum > self::MAX_SIMULATION_COUNT) {
            $this->customPlayNum = self::MAX_SIMULATION_COUNT;
            $this->sendDangerNotification(
                "試行回数は最大 " . number_format(self::MAX_SIMULATION_COUNT) . " 回以下でお願いします。",
                "",
            );
        }
    }

    public function getPrizeTypeList(): array
    {
        $oprGacha = OprGacha::query()
            ->with([
                'opr_gacha_i18n',
                'opr_gacha_uppers',
            ])
            ->where('id', $this->oprGachaId)
            ->first();

        $oprGachaUppers = $oprGacha->opr_gacha_uppers;
        $result = [
            GachaPrizeType::REGULAR->value => GachaSetName::REGULAR->value,
        ];
        if ($oprGacha->toEntity()->hasMultiFixedPrize()) {
            $result[GachaPrizeType::FIXED->value] = GachaSetName::FIXED->value;
        }
        foreach ($oprGachaUppers as $oprGachaUpper) {
            $value = $oprGachaUpper->upper_type->value;
            $name = match ($oprGachaUpper->upper_type->value) {
                GachaPrizeType::PICKUP->value => GachaSetName::PICKUP->value,
                GachaPrizeType::MAX_RARITY->value => GachaSetName::MAX_RARITY->value,
                default => '',
            };
            $result[$value] = $name;
        }
        return $result;
    }

    /**
     * 抽選枠変更時にテーブルデータを更新
     *
     * view側のwire:model.live="prizeType"により、変更時に自動で呼ばれる
     */
    public function updatedPrizeType()
    {
        // 保存されたシミュレーション結果があるかチェック
        $admGachaSimulationLog = AdmGachaSimulationLog::query()
            ->where('opr_gacha_id', $this->oprGachaId)
            ->first();

        if (is_null($admGachaSimulationLog)) {
            // シミュレーション結果がない場合はデフォルトデータを表示
            $this->setSimulationResults($this->getEmptySimulationResults());
            $this->hasSimulationData = false;
            $this->hasRangeCheckError = false;
        } else {
            // 保存されたシミュレーション結果がある場合はそれを表示
            $gachaPrizeSimulationResultEntities = $this->getSimulationResultsByAdmGachaSimulationLog(
                $admGachaSimulationLog,
            );
            if ($gachaPrizeSimulationResultEntities->isEmpty()) {
                // シミュレーション結果がない場合はデフォルトデータを表示
                $gachaPrizeSimulationResultEntities = $this->getEmptySimulationResults();
                $this->hasSimulationData = false;
                $this->hasRangeCheckError = false;
            } else {
                $this->hasSimulationData = $gachaPrizeSimulationResultEntities->isNotEmpty();
                $this->hasRangeCheckError = $this->gachaSimulatorService
                    ->hasRangeCheckError($gachaPrizeSimulationResultEntities);
            }
            $this->setSimulationResults($gachaPrizeSimulationResultEntities);
        }

        // prizeTypeを切り替えるとパンくずリストが消えるので追記
        $this->breadcrumbList();
    }

    /**
     * view側でCollectionやEntityなどを直接扱えないため、配列に変換してセットする
     */
    private function setSimulationResults(Collection $gachaPrizeSimulationResultEntities): void
    {
        $this->simulationResults = $gachaPrizeSimulationResultEntities
            ->map(function (GachaPrizeSimulationResultEntity $entity) {
                return $entity->formatToArray();
            })->all();
    }
}
