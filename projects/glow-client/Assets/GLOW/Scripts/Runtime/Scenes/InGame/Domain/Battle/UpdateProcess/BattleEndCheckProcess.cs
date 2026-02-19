using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.BattleEndConditions;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    /// <summary> 勝利敗北、時間切れ判定を行う </summary>
    public class BattleEndCheckProcess : IBattleEndCheckProcess
    {
        BattleEndCheckProcessResult IBattleEndCheckProcess.UpdateBattleEnd(
            BattleEndModel battleEndModel,
            StageTimeModel stageTime,
            HP playerOutpostHP,
            HP enemyOutpostHP,
            HP defenseTargetHP,
            IReadOnlyList<CharacterUnitModel> deadUnits,
            DefeatEnemyCount defeatEnemyCount,
            IReadOnlyDictionary<MasterDataId, DefeatEnemyCount> defeatEnemyCountDictionary,
            BattleGiveUpFlag isGiveUp)
        {
            var context = new BattleEndConditionContext(
                stageTime,
                playerOutpostHP,
                enemyOutpostHP,
                defenseTargetHP,
                deadUnits,
                defeatEnemyCount,
                defeatEnemyCountDictionary,
                isGiveUp);

            // 勝利条件をチェック
            var meetVictoryCondition = battleEndModel.Conditions
                .FirstOrDefault(condition => condition.StageEndType == StageEndType.Victory &&
                                             condition.MeetsCondition(context));

            if (meetVictoryCondition != null)
            {
                return new BattleEndCheckProcessResult(BattleOverFlag.True, meetVictoryCondition);
            }

            // Finish条件をチェック
            var meetFinishCondition = battleEndModel.Conditions
                .FirstOrDefault(condition => condition.StageEndType == StageEndType.Finish &&
                                             condition.MeetsCondition(context));

            if (meetFinishCondition != null)
            {
                return new BattleEndCheckProcessResult(BattleOverFlag.True, meetFinishCondition);
            }

            // 敗北条件をチェック
            var meetDefeatCondition = battleEndModel.Conditions
                .FirstOrDefault(condition => condition.StageEndType == StageEndType.Defeat &&
                                             condition.MeetsCondition(context));

            if (meetDefeatCondition != null)
            {
                return new BattleEndCheckProcessResult(BattleOverFlag.True, meetDefeatCondition);
            }

            return new BattleEndCheckProcessResult(BattleOverFlag.False, EmptyBattleEndConditionModel.Instance);
        }
    }
}
