using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle.Calculator;
using GLOW.Scenes.InGame.Domain.Battle.Evaluator;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.PvpTop.Domain.Resolver;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public class BattlePointInitializer : IBattlePointInitializer
    {
        static readonly BattlePoint DefaultInitialBattlePoint = new BattlePoint(200);

        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IMstCurrentPvpModelResolver MstCurrentPvpModelResolver { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IBattlePointChargeAmountCalculator BattlePointChargeAmountCalculator { get; }
        [Inject] IMaxBattlePointCalculator MaxBattlePointCalculator { get; }
        [Inject] IArtworkEffectActivationEvaluator ArtworkEffectActivationEvaluator { get; }

        public BattlePointInitializerResult Initialize(
            InGameType type,
            MasterDataId mstAdventBattleId,
            ContentSeasonSystemId sysPvpSeasonId,
            OutpostEnhancementModel outpostEnhancement,
            OutpostEnhancementModel pvpOpponentOutpostEnhancement,
            ArtworkEffectModel artworkEffect,
            ArtworkEffectModel pvpOpponentArtworkEffect,
            IReadOnlyList<DeckUnitModel> deckUnits,
            IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits,
            InGameContinueSelectingFlag isInGameContinueSelecting)
        {
            var chargeIntervalConfig = MstConfigRepository.GetConfig(MstConfigKey.InGameBattlePointChargeInterval);
            var defaultBattlePointChargeInterval = chargeIntervalConfig.Value.ToTickCount();

            // プレイヤー用
            var battlePoint = InitializePlayerBattlePoint(
                type,
                mstAdventBattleId,
                sysPvpSeasonId,
                outpostEnhancement,
                defaultBattlePointChargeInterval,
                artworkEffect,
                deckUnits,
                isInGameContinueSelecting);

            // Pvpでの対戦相手用
            var opponentBattlePoint = InitializePvpOpponentBattlePoint(
                type,
                sysPvpSeasonId,
                pvpOpponentOutpostEnhancement,
                pvpOpponentArtworkEffect,
                pvpOpponentDeckUnits,
                defaultBattlePointChargeInterval);

            return new BattlePointInitializerResult(
                battlePoint,
                opponentBattlePoint);
        }

        BattlePointModel InitializePlayerBattlePoint(
            InGameType type,
            MasterDataId mstAdventBattleId,
            ContentSeasonSystemId sysPvpSeasonId,
            OutpostEnhancementModel outpostEnhancement,
            TickCount defaultBattlePointChargeInterval,
            ArtworkEffectModel artworkEffect,
            IReadOnlyList<DeckUnitModel> deckUnits,
            InGameContinueSelectingFlag isInGameContinueSelecting)
        {
            var chargeAmount = BattlePointChargeAmountCalculator.Calculate(outpostEnhancement);
            var maxBattlePoint = MaxBattlePointCalculator.Calculate(outpostEnhancement);

            // 初期リーダーP(MstConfigKey.InGameInitializeBattlePoint)の取得
            var initializeBattlePoint = DefaultInitialBattlePoint;
            var configInitializeBattlePointModel = MstConfigRepository.GetConfig(MstConfigKey.InGameInitializeBattlePoint);
            if (!configInitializeBattlePointModel.IsEmpty())
            {
                initializeBattlePoint = BattlePoint.Min(configInitializeBattlePointModel.Value.ToBattlePoint(), maxBattlePoint);
            }

            if (type == InGameType.AdventBattle)
            {
                var mstAdventBattle = MstAdventBattleDataRepository
                    .GetMstAdventBattleModel(mstAdventBattleId);

                initializeBattlePoint = BattlePoint.Min(mstAdventBattle.InitialBattlePoint, maxBattlePoint);
            }

            if (type == InGameType.Pvp)
            {
                var mstPvpModel = MstCurrentPvpModelResolver.CreateMstPvpModel(sysPvpSeasonId);
                if (!mstPvpModel.IsEmpty())
                {
                    initializeBattlePoint = BattlePoint.Min(mstPvpModel.InitialBattlePoint, maxBattlePoint);
                }
            }

            // 原画効果 開始時リーダーP増加
            initializeBattlePoint += GetArtworkEffectInitialBattlePointUp(artworkEffect, deckUnits);

            // 導入チュートリアル時の初期リーダーPの上書き
            if (GameRepository.GetGameFetchOther().TutorialStatus.IsIntroduction())
            {
                initializeBattlePoint = maxBattlePoint;
            }

            if (isInGameContinueSelecting == InGameContinueSelectingFlag.True)
            {
                // コンティニュー選択中は初期リーダーPを0にする
                initializeBattlePoint = BattlePoint.Zero;
            }

            return new BattlePointModel(
                maxBattlePoint,
                initializeBattlePoint,
                chargeAmount,
                defaultBattlePointChargeInterval,
                TickCount.Zero,
                true);
        }

        BattlePointModel InitializePvpOpponentBattlePoint(
            InGameType type,
            ContentSeasonSystemId sysPvpSeasonId,
            OutpostEnhancementModel pvpOpponentOutpostEnhancement,
            ArtworkEffectModel pvpOpponentArtworkEffect,
            IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits,
            TickCount defaultBattlePointChargeInterval)
        {
            // Pvpでの対戦相手用
            BattlePointModel opponentBattlePoint = BattlePointModel.Empty;
            if (type == InGameType.Pvp)
            {
                var opponentChargeAmount =
                    BattlePointChargeAmountCalculator.Calculate(pvpOpponentOutpostEnhancement);
                var opponentMaxBattlePoint =
                    MaxBattlePointCalculator.Calculate(pvpOpponentOutpostEnhancement);

                // 初期リーダーP(MstConfigKey.InGameInitializeBattlePoint)の取得
                var initializeBattlePoint = DefaultInitialBattlePoint;
                var configInitializeBattlePointModel = MstConfigRepository.GetConfig(MstConfigKey.InGameInitializeBattlePoint);
                if (!configInitializeBattlePointModel.IsEmpty())
                {
                    initializeBattlePoint = BattlePoint.Min(
                        configInitializeBattlePointModel.Value.ToBattlePoint(),
                        opponentMaxBattlePoint);
                }

                // リーダーP初期値の設定がある場合は上書き
                var mstPvpModel = MstCurrentPvpModelResolver.CreateMstPvpModel(sysPvpSeasonId);
                if (!mstPvpModel.IsEmpty())
                {
                    initializeBattlePoint = BattlePoint.Min(mstPvpModel.InitialBattlePoint, opponentMaxBattlePoint);
                }

                // 原画効果 開始時リーダーP増加
                initializeBattlePoint += GetArtworkEffectInitialBattlePointUp(
                    pvpOpponentArtworkEffect,
                    pvpOpponentDeckUnits);

                opponentBattlePoint = new BattlePointModel(
                    opponentMaxBattlePoint,
                    initializeBattlePoint,
                    opponentChargeAmount,
                    defaultBattlePointChargeInterval,
                    TickCount.Zero,
                    true);
            }

            return opponentBattlePoint;
        }

        // 原画効果 開始時リーダーP増加の取得
        BattlePoint GetArtworkEffectInitialBattlePointUp(
            ArtworkEffectModel artworkEffect,
            IReadOnlyList<DeckUnitModel> deckUnits)
        {
            var artworkEffectBattlePoint = BattlePoint.Empty;
            foreach (var effectElement in artworkEffect.EffectElements)
            {
                if (effectElement.EffectType == ArtworkEffectType.InitialLeaderPointUp &&
                    ArtworkEffectActivationEvaluator.EvaluateActivation(
                        effectElement.ActivationRules,
                        deckUnits))
                {
                    artworkEffectBattlePoint += effectElement.EffectValue.ToBattlePoint();
                }
            }

            return artworkEffectBattlePoint;
        }
    }
}
