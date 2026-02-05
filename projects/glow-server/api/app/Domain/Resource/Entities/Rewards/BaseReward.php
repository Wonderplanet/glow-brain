<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Common\Utils\StringUtil;
use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Dtos\RewardDto;
use App\Domain\Resource\Enums\RewardConvertedReason;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Enums\UnreceivedRewardReason;
use Ramsey\Uuid\Uuid;

/**
 * 報酬オブジェクトの基底クラス。
 *
 * 報酬の状態:
 * - 未送信: ビジネスロジック側からRewardManagerに追加されただけで、何も処理をしていない状態
 * - 送信済み: 報酬送信処理を実行した状態。即時報酬配布されるのが基本だが、メールボックスなどに送信するケースもある
 * - 配布済み、付与済み: ユーザが実際に報酬を受け取った状態。報酬の付与処理が完了した状態
 */
class BaseReward
{
    /** @var string RewardManagerでの管理のために使用するid */
    private string $id;

    /** @var bool 報酬送信処理をしたかどうかのフラグ。true: 処理済み, false: 未処理 */
    private bool $isSent = false;

    private RewardDto $rewardData;

    /** @var RewardDto 元のRewardData */
    private RewardDto $originalRewardData;

    /** @var RewardConvertedReason リワードが変更された理由 */
    private RewardConvertedReason $rewardConvertedReason;

    /**
     * @var UnreceivedRewardReason 報酬が受け取れなかった理由
     * None: 受け取り成功 (デフォルト)
     */
    private UnreceivedRewardReason $unreceivedRewardReason;

    private LogTriggerDto $logTriggerData;

    /**
     * ログ保存時に使用する変数で
     * 実際に付与を実行した際のリソース変動量を保持するため
     */
    // 付与前のリソース量
    private int $beforeAmount = 0;
    // 付与後のリソース量
    private int $afterAmount = 0;

    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
        LogTriggerDto $logTriggerData,
    ) {
        $this->id = $this->generateUniqueId();

        $this->rewardData = new RewardDto($type, $resourceId, $amount);
        $this->originalRewardData = $this->rewardData;

        $this->logTriggerData = $logTriggerData;

        $this->unreceivedRewardReason = UnreceivedRewardReason::NONE;
    }

    public function __clone()
    {
        $this->id = $this->generateUniqueId();
        $this->rewardData = clone $this->rewardData;
        $this->originalRewardData = clone $this->originalRewardData;
        $this->logTriggerData = clone $this->logTriggerData;
    }

    private function generateUniqueId(): string
    {
        return (string) Uuid::uuid4();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->rewardData->getType();
    }

    public function getResourceId(): ?string
    {
        $resourceId = $this->rewardData->getResourceId();

        if (StringUtil::isNotSpecified($resourceId)) {
            return null;
        }

        return $resourceId;
    }

    public function getAmount(): int
    {
        return $this->rewardData->getAmount();
    }

    /**
     * リソース変換して、報酬量などの調整を行った後のデータを再設定する際に使用する。
     * 報酬の種類(BaseRewardを継承したクラス)は変わらないが、RewardDataの中身だけ変わる場合がある。
     * 使用ケース例: idleBox系のアイテムを、コインや別アイテムへ変換したとき
     *
     * @param RewardDto $rewardData
     * @return void
     */
    public function setRewardData(RewardDto $rewardData): void
    {
        $this->originalRewardData = $this->rewardData;
        $this->rewardData = $rewardData;
    }

    /**
     * 有効な報酬オブジェクトかどうかをチェックする。
     * 無効な場合は、RewardManagerの管理に入らない。
     * true: 有効、false: 無効
     *
     * @return boolean
     */
    public function isValid(): bool
    {
        $enum = RewardType::tryFrom($this->getType());
        if (
            $enum === null
            || $this->getAmount() <= 0
            || ($enum->hasResourceId() && $this->getResourceId() === null)
        ) {
            return false;
        }

        return true;
    }

    /**
     * 報酬送信処理を実行したとして、フラグを立てる。
     *
     * @return void
     */
    public function markAsSent(): void
    {
        $this->isSent = true;
    }

    /**
     * 報酬送信を実行する必要があるかどうかのフラグを返す。
     * true: 未送信なので送信実行必要, false: 送信処理済み
     *
     * @return boolean
     */
    public function needToSend(): bool
    {
        return $this->isSent === false;
    }

    /**
     * 報酬送信を実行したかどうかのフラグを返す。
     *
     * @return boolean
     */
    public function isSent(): bool
    {
        return $this->isSent;
    }

    /**
     * 重複した場合のRewardType
     * @return RewardConvertedReason|null
     */
    public function getRewardConvertedReason(): ?RewardConvertedReason
    {
        return $this->rewardConvertedReason ?? null;
    }

    /**
     * @param RewardConvertedReason $reason
     * @return void
     */
    public function setRewardConvertedReason(RewardConvertedReason $reason): void
    {
        $this->rewardConvertedReason = $reason;
    }

    /**
     * @return RewardDto
     */
    public function getOriginalRewardData(): RewardDto
    {
        return $this->originalRewardData;
    }

    public function getLogTriggerData(): LogTriggerDto
    {
        $convretedResource = $this->getRewardConvertedReason();
        if ($convretedResource instanceof RewardConvertedReason) {
            $this->logTriggerData->setTriggerOption($convretedResource->value);
        }

        return $this->logTriggerData;
    }

    public function setLogTriggerOption(string $option): void
    {
        $this->logTriggerData->setTriggerOption($option);
    }

    public function setBeforeAmount(int $beforeAmount): void
    {
        $this->beforeAmount = $beforeAmount;
    }

    public function getBeforeAmount(): int
    {
        return $this->beforeAmount;
    }

    public function setAfterAmount(int $afterAmount): void
    {
        $this->afterAmount = $afterAmount;
    }

    public function getAfterAmount(): int
    {
        return $this->afterAmount;
    }

    /**
     * 変換されているか否か
     *
     * @return bool
     */
    public function isConverted(): bool
    {
        return $this->rewardData->getType() !== $this->originalRewardData->getType() ||
            $this->rewardData->getResourceId() !== $this->originalRewardData->getResourceId();
    }

    /**
     * @return ?array<string, mixed>
     */
    protected function getPreConversionResource(): ?array
    {
        $preConversionResource = null;
        if ($this->isConverted()) {
            $preConversionResource = [
                'resourceType' => StringUtil::snakeToPascalCase($this->originalRewardData->getType()),
                'resourceId' => $this->originalRewardData->getResourceId(),
                'resourceAmount' => $this->originalRewardData->getAmount(),
            ];
        }
        return $preConversionResource;
    }

    public function getUnreceivedRewardReason(): UnreceivedRewardReason
    {
        return $this->unreceivedRewardReason;
    }

    public function setUnreceivedRewardReason(UnreceivedRewardReason $reason): void
    {
        $this->unreceivedRewardReason = $reason;
    }

    public function isUnreceived(): bool
    {
        return $this->unreceivedRewardReason !== UnreceivedRewardReason::NONE;
    }

    /**
     * @return array<string, mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'unreceivedRewardReasonType' => $this->unreceivedRewardReason->value,
            'resourceType' => StringUtil::snakeToPascalCase($this->getType()),
            'resourceId' => $this->getResourceId(),
            'resourceAmount' => $this->getAmount(),
            'preConversionResource' => $this->getPreConversionResource(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getRewardResponseData(): array
    {
        return [
            'reward' => $this->formatToResponse(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function formatToLog(): array
    {
        return $this->formatToResponse();
    }

    /**
     * @return array<string, mixed>
     */
    public function formatToLogWithTrigger(): array
    {
        $log =  $this->formatToLog();

        $log['triggerSource'] = $this->logTriggerData->getTriggerSource();
        $log['triggerValue'] = $this->logTriggerData->getTriggerValue();
        $log['triggerOption'] = $this->logTriggerData->getTriggerOption();

        return $log;
    }

    /**
     * Rewardインスタンスを個数で分離し、指定個数でRewardインスタンスを新たに生成する
     *
     * 非推奨：変換済みのRewardインスタンスでの使用。
     *   変換前後の個数レートは保持していないため、厳密にamountを減算することができないため。
     *   変換する前のRewardインスタンスで実行するメソッドとして想定している。
     *
     * 使用例：ユニットXを3体配布するRewardの場合
     *   1体は新規獲得(インスタンスA)で変換不要だが、残り2体は重複獲得(インスタンスB)なので変換する必要がある。
     *   divideRewardByAmount(1)で、インスタンスAをamount=1で新たに生成し、既存のインスタンスBのamountを1減らす。
     *   その後、インスタンスBのみ変換処理がかけられ、元は同じRewardだが異なる変換処理を行い、その経緯を保持することができる。
     *
     * @param int $amount 新たに生成するRewardのamount
     * @return BaseReward
     */
    public function divideRewardByAmount(int $amount): BaseReward
    {
        $this->rewardData = new RewardDto(
            $this->getType(),
            $this->getResourceId(),
            $this->getAmount() - $amount,
        );
        $this->originalRewardData = $this->rewardData;

        // 報酬数のみを変更したインスタンスを生成するためにclone
        $newReward = clone $this;
        $newReward->rewardData = new RewardDto(
            $this->getType(),
            $this->getResourceId(),
            $amount,
        );
        $newReward->originalRewardData = $newReward->rewardData;

        return $newReward;
    }
}
