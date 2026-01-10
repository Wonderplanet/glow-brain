<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Encyclopedia\Delegators\EncyclopediaDelegator;
use App\Domain\Encyclopedia\Delegators\EncyclopediaEffectDelegator;
use App\Domain\Item\Delegators\ItemDelegator;
use App\Domain\Outpost\Delegators\OutpostDelegator;
use App\Domain\Pvp\Entities\PvpEncyclopediaEffect;
use App\Domain\Pvp\Models\UsrPvpInterface;
use App\Domain\Pvp\Models\UsrPvpSessionInterface;
use App\Domain\Pvp\Repositories\UsrPvpSessionRepository;
use App\Domain\Pvp\Services\PvpMissionTriggerService;
use App\Domain\Resource\Mst\Entities\MstPvpEntity;
use App\Domain\Resource\Mst\Services\MstConfigService;
use App\Domain\Resource\Sys\Entities\SysPvpSeasonEntity;
use App\Domain\Resource\Usr\Entities\UsrUnitEntity;
use App\Domain\Unit\Delegators\UnitDelegator;
use App\Http\Responses\Data\OpponentPvpStatusData;
use App\Http\Responses\Data\PvpUnitData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class PvpStartService
{
    public function __construct(
        private UnitDelegator $unitDelegator,
        private OutpostDelegator $outpostDelegator,
        private EncyclopediaDelegator $encyclopediaDelegator,
        private EncyclopediaEffectDelegator $encyclopediaEffectDelegator,
        private UsrPvpSessionRepository $usrPvpSessionRepository,
        private MstConfigService $mstConfigService,
        private ItemDelegator $itemDelegator,
        private PvpMissionTriggerService $pvpMissionTriggerService,
    ) {
    }

    /**
     * PVPに挑戦できる状態かどうかを確認する
     * 挑戦できない場合はエラーを投げる
     *
     * @param UsrPvpInterface $usrPvp
     * @param MstPvpEntity $mstPvp
     * @return void
     * @throws GameException
     */
    public function validateCanStart(UsrPvpInterface $usrPvp, MstPvpEntity $mstPvp, bool $isUseItem): void
    {
        if ($isUseItem) {
            // アイテム消費の挑戦回数を使用する場合
            if ($usrPvp->getDailyRemainingItemChallengeCount() <= 0) {
                throw new GameException(
                    ErrorCode::PVP_NO_CHALLENGE_RIGHT,
                    'item challenge count is over',
                );
            }

            $mstItemId = $this->mstConfigService->getPvpChallengeItemId();
            if ($mstItemId === null) {
                throw new GameException(
                    ErrorCode::MST_NOT_FOUND,
                    'mst_config for PVP challenge item is not found.',
                );
            }
            $usrItem = $this->itemDelegator->getUsrItemByMstItemId($usrPvp->getUsrUserId(), $mstItemId);
            if (is_null($usrItem) || $usrItem->getAmount() < $mstPvp->getItemChallengeCostAmount()) {
                throw new GameException(
                    ErrorCode::ITEM_AMOUNT_IS_NOT_ENOUGH,
                    'not enough item for PVP challenge',
                );
            }

            // アイテム挑戦回数が使えるのはデイリーの挑戦回数が0になってからの制限チェック
            if ($usrPvp->getDailyRemainingChallengeCount() > 0) {
                throw new GameException(
                    ErrorCode::PVP_NO_CHALLENGE_RIGHT,
                    'daily challenge count must be 0 to use item challenge count',
                );
            }
        } else {
            // デイリーの挑戦回数を使用する場合
            if ($usrPvp->getDailyRemainingChallengeCount() <= 0) {
                throw new GameException(
                    ErrorCode::PVP_NO_CHALLENGE_RIGHT,
                    'daily challenge count is over',
                );
            }
        }
    }

    public function validateCanResume(
        UsrPvpSessionInterface $usrPvpSession,
        SysPvpSeasonEntity $currentSysPvpSeason
    ): void {
        // 開始日時が現在のPVPシーズンの期間外であればエラーを投げる
        if (
            ! $currentSysPvpSeason->isInSeason(
                $usrPvpSession->getBattleStartAtAsCarbon()
            )
        ) {
            throw new GameException(
                ErrorCode::PVP_SEASON_PERIOD_OUTSIDE,
                sprintf(
                    'PVP session is not statable. User ID: %s, Session ID: %s, Current Season id: %s',
                    $usrPvpSession->getUsrUserId(),
                    $usrPvpSession->getId(),
                    $currentSysPvpSeason->getId()
                )
            );
        }
    }

    /**
     * PVPセッションを開始し、対戦相手情報を取得する
     */
    public function startPvpSession(
        string $usrUserId,
        string $sysPvpSeasonId,
        int $partyNo,
        string $opponentMyId,
        OpponentPvpStatusData $opponentPvpStatusData,
        int $opponentScore,
        CarbonImmutable $now,
        bool $isUseItem = false
    ): void {

        // 既存のPVPセッション情報を取得または作成
        $usrPvpSession = $this->usrPvpSessionRepository->findOrCreate($usrUserId, $sysPvpSeasonId);

        // PVPセッション情報を更新
        $usrPvpSession->startSession(
            $sysPvpSeasonId,
            $partyNo,
            $opponentMyId,
            $opponentPvpStatusData,
            $opponentScore,
            $now,
            $isUseItem
        );

        $this->usrPvpSessionRepository->syncModel($usrPvpSession);

        // PVP挑戦ミッションのトリガーを送信
        $this->pvpMissionTriggerService->sendStartTriggers();
    }

    // TODO: このメソッド未使用なので削除する。テストも削除。
    /**
     * 対戦相手のユニット情報をUnitDelegator経由で取得する
     */
    public function getOpponentUnits(string $opponentId, Collection $mstUnitIds): Collection
    {
        // UnitDelegator経由でユニット情報を取得
        $usrUnitEntities = $this->unitDelegator->getByMstUnitIds($opponentId, $mstUnitIds);

        if ($usrUnitEntities->isEmpty()) {
            return collect();
        }

        return $usrUnitEntities->map(function (UsrUnitEntity $usrUnit) {
            return new PvpUnitData(
                mstUnitId: $usrUnit->getMstUnitId(),
                level: $usrUnit->getLevel(),
                rank: $usrUnit->getRank(),
                gradeLevel: $usrUnit->getGradeLevel(),
            );
        })->values();
    }

    // TODO: このメソッド未使用なので削除する。テストも削除。
    /**
     * 対戦相手の前哨基地強化情報を取得する
     * OutpostDelegator経由で前哨基地強化情報を取得する
     */
    public function getOpponentOutpostEnhancements(string $opponentId): Collection
    {
        // TODO: 実際のマッチング実装時は、この部分で実際の対戦相手データを使用
        // 現在はテスト用として自分の前哨基地強化情報を対戦相手として返す

        // OutpostDelegator経由で前哨基地強化情報を取得
        $outpostEnhancements = $this->outpostDelegator->getOutpostEnhancements($opponentId);

        if ($outpostEnhancements->isEmpty()) {
            return collect();
        }

        // UsrOutpostEnhancementInterfaceをPVP用のデータ形式に変換
        return $outpostEnhancements->values();
    }

    // TODO: このメソッド未使用なので削除する。テストも削除。
    /**
     * 対戦相手のアートワーク情報を取得する
     * EncyclopediaDelegator経由でアートワークデータを取得する
     */
    public function getOpponentArtworks(string $opponentId): Collection
    {
        // TODO: 実際のマッチング実装時は、この部分で実際の対戦相手データを使用
        // 現在はテスト用として自分のアートワーク情報を対戦相手として返す
        $usrArtworks = $this->encyclopediaDelegator->getUsrArtworks($opponentId);

        return $usrArtworks->map(function ($artwork) {
            return $artwork->getMstArtworkId();
        })->values();
    }

    // TODO: このメソッド未使用なので削除する。テストも削除。
    /**
     * 対戦相手の図鑑効果を取得する
     * TODO: 実際の実装時は、実際の対戦相手の図鑑効果を取得するように修正
     */
    public function getOpponentEncyclopediaEffects(string $opponentId): Collection
    {
        // TODO: 実際のマッチング実装時は、この部分で実際の対戦相手の図鑑効果を取得
        $usrEncyclopediaEffects = $this->encyclopediaEffectDelegator->getUserEncyclopediaEffects($opponentId);

        return $usrEncyclopediaEffects->map(function (PvpEncyclopediaEffect $effect) {
            return new PvpEncyclopediaEffect($effect->getMstEncyclopediaEffectId());
        })->values();
    }
}
