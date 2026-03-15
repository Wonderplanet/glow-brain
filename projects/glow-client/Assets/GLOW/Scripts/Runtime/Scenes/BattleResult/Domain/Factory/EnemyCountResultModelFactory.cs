using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.InGame.Domain.Battle.AutoPlayer;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.BattleEndConditions;
using GLOW.Scenes.InGame.Domain.Models.LogModel;
using GLOW.Scenes.InGame.Domain.Repositories;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.BattleResult.Domain.Factory
{
    public class EnemyCountResultModelFactory : IEnemyCountResultModelFactory
    {
        [Inject] IInGameLogRepository InGameLogRepository { get; set; }
        [Inject] IInGameScene InGameScene { get; set; }
        [Inject(Id = AutoPlayer.EnemyAutoPlayerBindId)] IAutoPlayer EnemyAutoPlayer { get; set; }

        public EnemyCountResultModel Create()
        {
            var inGameLogModel = InGameLogRepository.GetLog();
            var totalBossCount = GetTotalBossCount();
            var remainingTargetEnemyCount = GetRemainingTargetEnemyCount(inGameLogModel);

            return new EnemyCountResultModel(
                inGameLogModel.DefeatBossEnemyCount,
                totalBossCount,
                remainingTargetEnemyCount
            );
        }

        BossCount GetTotalBossCount()
        {
            if (!InGameScene.MstInGame.BossCount.IsEmpty())
            {
                return InGameScene.MstInGame.BossCount;
            }

            return EnemyAutoPlayer.BossCount.ToBossCount();
        }

        DefeatEnemyCount GetRemainingTargetEnemyCount(InGameLogModel inGameLog)
        {
            var hasDefeatedEnemyCountStageEndCondition =
                InGameScene.BattleEndModel
                    .TryGetCondition<DefeatedEnemyCountBattleEndConditionModel>(out var defeatedEnemyCountCondition);

            if (hasDefeatedEnemyCountStageEndCondition)
            {
                var remainingCount = defeatedEnemyCountCondition.DefeatedEnemyCount - InGameScene.DefeatEnemyCount;
                return DefeatEnemyCount.Max(remainingCount, DefeatEnemyCount.Zero);
            }

            var hasDefeatUnitBattleEndCondition =
                InGameScene.BattleEndModel
                    .TryGetCondition<DefeatUnitBattleEndConditionModel>(out var defeatUnitCondition);

            if (hasDefeatUnitBattleEndCondition)
            {
                if (inGameLog.DefeatEnemyCountDictionary.TryGetValue(defeatUnitCondition.CharacterId, out var defeatEnemyCount))
                {
                    var remainingCount = defeatUnitCondition.DefeatEnemyCount - defeatEnemyCount;
                    return DefeatEnemyCount.Max(remainingCount, DefeatEnemyCount.Zero);
                }

                return defeatUnitCondition.DefeatEnemyCount;
            }

            return DefeatEnemyCount.Empty;
        }
    }
}

