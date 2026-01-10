using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.BattleEndConditions
{
    public record BattleEndConditionContext(
        StageTimeModel StageTime,
        HP PlayerOutpostHP,
        HP EnemyOutpostHP,
        HP DefenseTargetHP,
        IReadOnlyList<CharacterUnitModel> DeadUnits,
        DefeatEnemyCount DefeatEnemyCount,
        IReadOnlyDictionary<MasterDataId, DefeatEnemyCount> DeadUnitsDictionary,
        BattleGiveUpFlag IsGiveUp)
    {
        public static BattleEndConditionContext Empty { get; } = new(
            StageTimeModel.Empty,
            HP.Empty,
            HP.Empty,
            HP.Empty,
            new List<CharacterUnitModel>(),
            DefeatEnemyCount.Empty,
            new Dictionary<MasterDataId, DefeatEnemyCount>(),
            BattleGiveUpFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
