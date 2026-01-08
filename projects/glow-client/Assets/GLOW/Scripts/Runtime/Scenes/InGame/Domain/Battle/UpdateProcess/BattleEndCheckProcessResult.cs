using GLOW.Scenes.InGame.Domain.Models.BattleEndConditions;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public record BattleEndCheckProcessResult(
        BattleOverFlag IsBattleOver,
        IBattleEndConditionModel MetBattleEndCondition)
    {
        public static BattleEndCheckProcessResult Empty { get; } = new (
            BattleOverFlag.False, 
            EmptyBattleEndConditionModel.Instance);
    }
}
