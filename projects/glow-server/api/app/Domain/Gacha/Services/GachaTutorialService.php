<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Services;

use App\Domain\Gacha\Entities\GachaResultData;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\GachaUnlockConditionType;
use App\Domain\Gacha\Repositories\UsrGachaRepository;
use App\Domain\Resource\Mst\Entities\OprGachaEntity;
use App\Domain\Resource\Mst\Repositories\OprGachaRepository;
use Carbon\CarbonImmutable;

class GachaTutorialService
{
    public function __construct(
        private GachaService $gachaService,
        // Repositories
        private OprGachaRepository $oprGachaRepository,
        private UsrGachaRepository $usrGachaRepository,
    ) {
    }

    /**
     * @return GachaResultData
     */
    public function draw(
        string $usrUserId,
        CarbonImmutable $now,
        OprGachaEntity $oprGacha,
        int $playNum,
        CostType $costType,
    ): GachaResultData {
        $oprGachaId = $oprGacha->getId();

        // ガシャ情報取得
        $oprGacha = $this->gachaService->getOprGacha($oprGachaId);
        $usrGacha = $this->gachaService->getUsrGacha($usrUserId, $oprGachaId);

        // 各種バリデーション
        $this->gachaService->validatePlayNum($playNum, $oprGacha->getMultiDrawCount());
        $this->gachaService->validateCostType($oprGacha, $costType);

        // ガチャ抽選BOX取得
        $gachaLotteryBoxData = $this->gachaService->getGachaLotteryBox($oprGacha);

        // ガチャ抽選処理実行
        $gachaResultData = $this->gachaService->executeLottery(
            $oprGacha,
            $gachaLotteryBoxData,
            $playNum,
            collect(),
            collect(),
            false,
        );

        // ユーザデータ更新
        $usrGacha->incrementPlayCount($playNum);
        $usrGacha->setPlayedAt($now->toDateTimeString());
        $this->gachaService->saveUsr($usrGacha, collect());

        return $gachaResultData;
    }

    /**
     * チュートリアル完了ガチャを解放する
     * @param string          $usrUserId
     * @param CarbonImmutable $now
     * @return void
     */
    public function unlockMainPartTutorialCompleteGacha(string $usrUserId, CarbonImmutable $now): void
    {
        /** @var OprGachaEntity|null $oprGacha */
        $oprGacha = $this->oprGachaRepository->getByUnlockConditionType(
            GachaUnlockConditionType::MAIN_PART_TUTORIAL_COMPLETE->value,
            $now
        )->first();

        if ($oprGacha === null) {
            // 対象ガチャがないので何もしない
            return;
        }

        $usrGacha = $this->usrGachaRepository->create($usrUserId, $oprGacha->getId());
        $expiresAt = $now->addHours($oprGacha->getUnlockDurationHours())->toDateTimeString();
        $usrGacha->setExpiresAt($expiresAt);
        $this->usrGachaRepository->syncModel($usrGacha);
    }
}
