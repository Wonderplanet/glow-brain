#if GLOW_INGAME_DEBUG
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Battle.Calculator;
using GLOW.Scenes.InGame.Domain.ModelFactories;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Debugs.InGame.Domain.UseCases
{
    /// <summary>
    /// デバッグ用：ゲート強化値を変更するUseCase
    /// </summary>
    public sealed class DebugChangeOutpostEnhancementUseCase
    {
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IRushChargeTimeCalculator RushChargeTimeCalculator { get; }
        [Inject] IOutpostMaxHpCalculator OutpostMaxHpCalculator { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IUnitSummonCoolTimeCalculator UnitSummonCoolTimeCalculator { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMaxBattlePointCalculator MaxBattlePointCalculator { get; }
        [Inject] IBattlePointChargeAmountCalculator BattlePointChargeAmountCalculator { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IMstOutpostEnhanceDataRepository MstOutpostEnhanceDataRepository { get; }
        [Inject] IOutpostEnhancementModelFactory OutpostEnhancementModelFactory { get; }

        public void ChangeEnhancementValue(
            IReadOnlyDictionary<OutpostEnhancementType, OutpostEnhanceLevel> enhancementLevelDictionary)
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();

            // UserOutpostEnhanceModelsを書き換え
            ReplaceUserOutpostEnhanceModels(enhancementLevelDictionary, gameFetchOther);

            // OutpostEnhancementModelを再生成
            var newEnhancementModel = OutpostEnhancementModelFactory.Create();
            InGameScene.OutpostEnhancement = newEnhancementModel;

            // GameFetchOtherを元に戻す
            GameManagement.SaveGameFetchOther(gameFetchOther);

            // ラッシュのチャージ時間を置き換え
            ReplaceRush(newEnhancementModel);

            // ゲートの最大HPがInGame設定で置き換えられてない場合は最大HPを置き換え
            ReplacePlayerOutpost(newEnhancementModel);

            // 最大BattlePointとチャージ量を置き換え
            ReplaceBattlePoint(newEnhancementModel);

            // デッキユニットのSummonCoolTimeを置き換え
            ReplaceDeckUnits(newEnhancementModel);
        }

        void ReplaceDeckUnits(OutpostEnhancementModel newEnhancementModel)
        {
            var updatedDeckUnits = new List<DeckUnitModel>();
            foreach (var unit in InGameScene.DeckUnits)
            {
                if (unit.IsEmptyUnit())
                {
                    updatedDeckUnits.Add(unit);
                    continue;
                }

                var mstCharacter = MstCharacterDataRepository.GetCharacter(unit.CharacterId);
                var newSummonCoolTime = UnitSummonCoolTimeCalculator.Calculate(
                    mstCharacter,
                    newEnhancementModel,
                    TickCount.Empty);

                updatedDeckUnits.Add(unit with { SummonCoolTime = newSummonCoolTime });
            }

            InGameScene.DeckUnits = updatedDeckUnits;
        }

        void ReplaceBattlePoint(OutpostEnhancementModel newEnhancementModel)
        {
            var maxBattlePoint = MaxBattlePointCalculator.Calculate(newEnhancementModel);
            var chargeAmount = BattlePointChargeAmountCalculator.Calculate(newEnhancementModel);
            var battlePointModel = InGameScene.BattlePointModel with
            {
                MaxBattlePoint = maxBattlePoint,
                ChargeAmount = chargeAmount
            };
            InGameScene.BattlePointModel = battlePointModel;
        }

        void ReplacePlayerOutpost(OutpostEnhancementModel newEnhancementModel)
        {
            var playerOutpost = InGameScene.PlayerOutpost;

            if (!playerOutpost.OutpostHpSpecialRuleFlag)
            {
                var maxHpResult = OutpostMaxHpCalculator.Calculate(
                    newEnhancementModel,
                    InGameScene.ArtworkBonusHp,
                    new List<MstInGameSpecialRuleModel>());

                InGameScene.PlayerOutpost = playerOutpost with
                {
                    Hp = maxHpResult.Hp,
                    MaxHp = maxHpResult.Hp,
                };
            }
        }

        void ReplaceRush(OutpostEnhancementModel newEnhancementModel)
        {
            var newChargeTime = RushChargeTimeCalculator.Calculate(newEnhancementModel, MstConfigRepository);
            InGameScene.RushModel = InGameScene.RushModel with
            {
                ChargeTime = newChargeTime
            };
        }

        void ReplaceUserOutpostEnhanceModels(IReadOnlyDictionary<OutpostEnhancementType, OutpostEnhanceLevel> enhancementLevelDictionary, GameFetchOtherModel gameFetchOther)
        {
            var userOutposts = gameFetchOther.UserOutpostModels;
            var usingOutpost = userOutposts.FirstOrDefault(userOutpost => userOutpost.IsUsed);
            if (usingOutpost == null) return;

            var mstOutpost = MstOutpostEnhanceDataRepository.GetOutpostModel(usingOutpost.MstOutpostId);
            var userOutpostEnhancements = gameFetchOther.UserOutpostEnhanceModels;

            foreach (var mstOutpostEnhancement in mstOutpost.EnhancementModels)
            {
                if (!enhancementLevelDictionary.TryGetValue(mstOutpostEnhancement.Type, out var newLevel))
                {
                    continue;
                }

                var newUserEnhancement = new UserOutpostEnhanceModel(
                    usingOutpost.MstOutpostId,
                    mstOutpostEnhancement.Id,
                    newLevel);

                userOutpostEnhancements = userOutpostEnhancements.ReplaceOrAdd(
                    enhancement =>
                        enhancement.MstOutpostId == usingOutpost.MstOutpostId &&
                        enhancement.MstOutpostEnhanceId == mstOutpostEnhancement.Id,
                    newUserEnhancement);
            }

            var newGameFetchOther = gameFetchOther with
            {
                UserOutpostEnhanceModels = userOutpostEnhancements
            };

            GameManagement.SaveGameFetchOther(newGameFetchOther);
        }
    }
}
#endif
