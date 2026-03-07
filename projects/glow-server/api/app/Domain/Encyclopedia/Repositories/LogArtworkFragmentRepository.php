<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Repositories;

use App\Domain\Encyclopedia\Models\LogArtworkFragment;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Log\Repositories\Contracts\ILogModelRepositoryRewardSend as IRewardSend;
use App\Domain\Resource\Log\Repositories\LogModelRepository;

/**
 * 原画のかけら獲得と原画完成のログテーブルのリポジトリ
 *
 * ログ作成ルール：
 * - かけら獲得時に、原画が完成していない場合は、かけら獲得ログを作成する
 * - かけら獲得時に、原画が完成した場合は、原画完成ログは必須とし、かけら獲得ログの作成は不要とする
 * - 原画を直接獲得した場合は、かけら獲得ログの作成は不要
 *
 * 原画のかけらは当初ステージでのみ配布されていたため、どのタイミングでどのかけらを獲得し、
 * どのタイミングで原画が完成したのかを、後から分析できるようにログテーブルを用意している。
 *
 * しかし、交換所などで、かけらではなく、原画を直接配布するケースも出てきた。
 * この場合、原画と原画のかけらの配布は同時になるため、かけらが獲得されたログは必須ではない。
 * 1かけらあたり、1レコードになるため、ログテーブルの肥大化を防ぐためにも、
 * かけら獲得ログは、必須ではないケースでは、ログを作成しない運用とする。
 */
class LogArtworkFragmentRepository extends LogModelRepository implements IRewardSend
{
    protected string $modelClass = LogArtworkFragment::class;

    public function create(
        string $usrUserId,
        string $mstArtworkFragmentId,
        string $contentType,
        string $targetId,
        bool $isCompleteArtwork
    ): LogArtworkFragment {
        $model = $this->make(
            $usrUserId,
            $mstArtworkFragmentId,
            $contentType,
            $targetId,
            $isCompleteArtwork
        );

        $this->addModel($model);

        return $model;
    }

    public function make(
        string $usrUserId,
        string $mstArtworkFragmentId,
        string $contentType,
        string $targetId,
        bool $isCompleteArtwork
    ): LogArtworkFragment {
        $model = new LogArtworkFragment();
        $model->setUsrUserId($usrUserId);
        $model->setMstArtworkFragmentId($mstArtworkFragmentId);
        $model->setContentType($contentType);
        $model->setTargetId($targetId);
        $model->setIsCompleteArtwork($isCompleteArtwork);

        return $model;
    }

    /**
     * 報酬として獲得したログデータを作成する
     *
     * 原画の完成フラグ情報は、LogTriggerDtoのtriggerOptionを流用する。
     * RewardSendServiceのbeforeSendで、原画のかけらから原画完成チェックを実施し、
     * 完成する場合は、triggerOptionにtrueをセットしています。
     */
    public function createByReward(string $usrUserId, BaseReward $reward): void
    {
        $logTriggerData = $reward->getLogTriggerData();
        $this->create(
            $usrUserId,
            $reward->getResourceId() ?? '',
            $logTriggerData->getTriggerSource(),
            $logTriggerData->getTriggerValue(),
            (bool) $logTriggerData->getTriggerOption(),
        );
    }
}
