using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public class BattlePointUpdateProcess : IBattlePointUpdateProcess
    {
        public BattlePointUpdateProcessResult UpdateBattlePoint(
            BattlePointModel bpModel,
            BattlePointModel pvpOpponentBpModel,
            OutpostEnhancementModel enhancementModel,
            OutpostEnhancementModel pvpOpponentEnhancementModel,
            IReadOnlyList<CharacterUnitModel> deadCharacterUnits,
            TickCount tickCount)
        {
            var newBpModel = UpdateBattlePointModel(
                bpModel,
                enhancementModel,
                deadCharacterUnits,
                BattleSide.Player,
                tickCount);

            var newOpponentBpModel = UpdateBattlePointModel(
                pvpOpponentBpModel,
                pvpOpponentEnhancementModel,
                deadCharacterUnits,
                BattleSide.Enemy,
                tickCount);

            return new BattlePointUpdateProcessResult(
                newBpModel,
                newOpponentBpModel);
        }

        BattlePointModel UpdateBattlePointModel(
            BattlePointModel bpModel,
            OutpostEnhancementModel enhancementModel,
            IReadOnlyList<CharacterUnitModel> deadCharacterUnits,
            BattleSide selfBattleSide,
            TickCount tickCount)
        {
            if (bpModel.IsEmpty())
            {
                return bpModel;
            }

            BattlePoint newBp = bpModel.CurrentBattlePoint;
            TickCount remainingTickCountForCharge = bpModel.RemainingTickCountForCharge - tickCount;

            // 時間経過によるチャージ
            if (remainingTickCountForCharge.IsZero())
            {
                newBp += bpModel.ChargeAmount;
                remainingTickCountForCharge = bpModel.ChargeInterval;
            }

            // 敵撃破によるチャージ
            var outpostEnhanceEnemyKillPointOffset =
                enhancementModel.GetEnhancementValue(OutpostEnhancementType.LeaderPointUp);
            newBp += deadCharacterUnits
                .Where(unit => unit.BattleSide != selfBattleSide)
                .Select(unit => GetBattlePointIncreaseWhenEnemyKill(unit, outpostEnhanceEnemyKillPointOffset))
                .Sum();

            // 最大値を越えないようにする
            newBp = BattlePoint.Min(newBp, bpModel.MaxBattlePoint);

            return bpModel with
            {
                CurrentBattlePoint = newBp,
                RemainingTickCountForCharge = remainingTickCountForCharge
            };
        }

        BattlePoint GetBattlePointIncreaseWhenEnemyKill(
            CharacterUnitModel characterUnit,
            OutpostEnhanceValue outpostEnhanceEnemyKillPointOffset)
        {
            var result = new BattlePoint((int)characterUnit.DropBattlePoint.Value) +
                         outpostEnhanceEnemyKillPointOffset.ToBattlePoint();

            return result;
        }
    }
}
