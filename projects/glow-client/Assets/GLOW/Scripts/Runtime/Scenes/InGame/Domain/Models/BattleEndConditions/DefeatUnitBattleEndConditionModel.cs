using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.BattleEndConditions
{
    public record DefeatUnitBattleEndConditionModel(
        StageEndType StageEndType,
        MasterDataId CharacterId,
        CharacterName CharacterName,
        DefeatEnemyCount DefeatEnemyCount) : IBattleEndConditionModel
    {
        public StageEndConditionType StageEndConditionType => StageEndConditionType.DefeatUnit;

        public bool MeetsCondition(BattleEndConditionContext context)
        {
            // 判定処理
            if (context.DeadUnitsDictionary.TryGetValue(CharacterId, out var defeatEnemyCount))
            {
                if (DefeatEnemyCount <= defeatEnemyCount)
                {
                    return true;
                }
            }

            return false;
        }

        public bool IsTargetEnemy(MasterDataId enemyId)
        {
            return enemyId == CharacterId;
        }
    }
}
