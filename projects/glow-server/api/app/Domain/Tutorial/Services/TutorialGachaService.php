<?php

declare(strict_types=1);

namespace App\Domain\Tutorial\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Gacha\Entities\GachaPrize;
use App\Domain\Gacha\Entities\GachaResultData;
use App\Domain\Resource\Entities\Rewards\GachaReward;
use App\Domain\Tutorial\Models\UsrTutorialGachaInterface;
use App\Domain\Tutorial\Repositories\UsrTutorialGachaRepository;
use Illuminate\Support\Collection;

/**
 * チュートリアルガシャ関連ロジックのサービスクラス
 */
class TutorialGachaService
{
    public function __construct(
        private UsrTutorialGachaRepository $usrTutorialGachaRepository,
    ) {
    }

    /**
     * チュートリアルガシャの一時的な結果をユーザーデータへ上書きする
     *
     * @param GachaResultData $gachaResultData
     */
    public function overwriteGachaTemporaryResult(
        UsrTutorialGachaInterface $usrTutorialGacha,
        GachaResultData $gachaResultData,
    ): void {
        $usrTutorialGacha->setGachaResultJson(
            $gachaResultData->toArray(),
        );
        $this->usrTutorialGachaRepository->syncModel($usrTutorialGacha);
    }

    /**
     * チュートリアルガシャを引けるか検証する
     */
    public function validateGachaDraw(
        UsrTutorialGachaInterface $usrTutorialGacha,
    ): void {
        if ($usrTutorialGacha->isConfirmed()) {
            throw new GameException(
                ErrorCode::TUTORIAL_INVALID_MAIN_PART_ORDER,
                sprintf(
                    'tutorial gacha is already confirmed.',
                ),
            );
        }
    }

    /**
     * チュートリアルガシャの結果を確定させられるか検証する
     */
    public function validateGachaConfirm(
        ?UsrTutorialGachaInterface $usrTutorialGacha,
    ): void {
        if ($usrTutorialGacha === null) {
            throw new GameException(
                ErrorCode::TUTORIAL_INVALID_MAIN_PART_ORDER,
                sprintf(
                    'tutorial gacha is not drawn.',
                ),
            );
        }

        if ($usrTutorialGacha->isConfirmed()) {
            throw new GameException(
                ErrorCode::TUTORIAL_INVALID_MAIN_PART_ORDER,
                sprintf(
                    'tutorial gacha is already confirmed.',
                ),
            );
        }
    }

    /**
     * GachaResultDataからガシャ報酬インスタンスを作成する
     *
     * @return Collection<GachaReward>
     */
    public function makeGachaRewardsByGachaResultData(
        GachaResultData $gachaResultData,
    ): Collection {
        $gachaRewards = collect();
        foreach ($gachaResultData->getResult() as $index => $gachaPrize) {
            /** @var GachaPrize $gachaPrize */
            $gachaRewards->push(
                new GachaReward(
                    $gachaPrize->getResourceType()->value,
                    $gachaPrize->getResourceId(),
                    $gachaPrize->getResourceAmount(),
                    $gachaResultData->getOprGachaId(),
                    $index,
                )
            );
        }

        return $gachaRewards;
    }
}
