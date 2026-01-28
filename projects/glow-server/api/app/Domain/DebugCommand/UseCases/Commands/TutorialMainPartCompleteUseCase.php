<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Gacha\Services\GachaTutorialService;
use App\Domain\Party\Constants\PartyConstant;
use App\Domain\Party\Repositories\UsrPartyRepository;
use App\Domain\Resource\Mst\Repositories\MstTutorialRepository;
use App\Domain\Resource\Mst\Repositories\OprGachaRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Tutorial\Enums\TutorialType;
use App\Domain\Tutorial\Repositories\UsrTutorialGachaRepository;
use App\Domain\Tutorial\Services\TutorialGachaService;
use App\Domain\Unit\Repositories\UsrUnitRepository;
use App\Domain\User\Repositories\UsrUserRepository;
use App\Domain\User\Services\UserService;

class TutorialMainPartCompleteUseCase extends BaseCommands
{
    use UseCaseTrait;

    protected string $name = 'チュートリアルメインパート完了';
    protected string $description = 'MstTutorialのtypeがMainのデータの中で、'
        . 'sort_orderが最大のチュートリアルコンテンツを、完了した状態へ更新します';

    public function __construct(
        private Clock $clock,
        private MstTutorialRepository $mstTutorialRepository,
        private UsrUserRepository $usrUserRepository,
        private UsrUnitRepository $usrUnitRepository,
        private OprGachaRepository $oprGachaRepository,
        private UsrTutorialGachaRepository $usrTutorialGachaRepository,
        private UsrPartyRepository $usrPartyRepository,
        private RewardDelegator $rewardDelegator,
        private GachaTutorialService $gachaTutorialService,
        private TutorialGachaService $tutorialGachaService,
        private UserService $userService,
    ) {
    }

    /**
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        $now = $this->clock->now();

        $usrUser = $this->usrUserRepository->findById($user->id);

        // メインパートの最後のマスターデータを取得
        $mstTutorial = $this->mstTutorialRepository->getActivesByType(
            TutorialType::MAIN,
            $now,
        )->sortByDesc->getSortOrder()
            ->first();

        if ($mstTutorial === null) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_tutorials record is not found. (type: %s)',
                    TutorialType::MAIN->value,
                ),
            );
        }

        // チュートリアルガチャを引く
        $oprGacha = $this->oprGachaRepository
            ->getByGachaType(GachaType::TUTORIAL, isThrowError: true)
            ->first();

        $usrTutorialGacha = $this->usrTutorialGachaRepository->getOrCreate($user->id);

        $gachaResultData = $this->gachaTutorialService->draw(
            $user->id,
            $now,
            $oprGacha,
            playNum: $oprGacha->getMultiDrawCount(),
            costType: CostType::DIAMOND,
        );

        // チュートリアルガチャ排出結果そのままを記録する
        $this->tutorialGachaService->overwriteGachaTemporaryResult(
            $usrTutorialGacha,
            $gachaResultData,
        );

        // チュートリアルガチャ結果を確定
        $gachaRewards = $this->tutorialGachaService->makeGachaRewardsByGachaResultData(
            $gachaResultData,
        );
        $this->rewardDelegator->addRewards($gachaRewards);
        $this->rewardDelegator->sendRewards($user->id, $platform, $now);

        // 確定済ステータスへ更新
        $usrTutorialGacha->confirm($now);
        $this->usrTutorialGachaRepository->syncModel($usrTutorialGacha);

        // 取れたユニットを取得してパーティに設定
        $usrUnit = $this->usrUnitRepository->getListByUsrUserId($user->id)->first();

        // PartyConstant::INITIAL_PARTY_COUNT分のパーティを作成
        for ($partyNo = PartyConstant::FIRST_PARTY_NO; $partyNo <= PartyConstant::INITIAL_PARTY_COUNT; $partyNo++) {
            $this->usrPartyRepository->create($usrUser->getId(), $partyNo, collect([$usrUnit->getId()]));
        }

        // アバター登録（取得したユニットをアバターとして設定）
        $this->userService->setNewAvatar($user->id, $usrUnit->getMstUnitId());

        // チュートリアルステータスを更新
        $usrUser->setTutorialStatus($mstTutorial->getFunctionName());
        $this->usrUserRepository->syncModel($usrUser);
    }
}
