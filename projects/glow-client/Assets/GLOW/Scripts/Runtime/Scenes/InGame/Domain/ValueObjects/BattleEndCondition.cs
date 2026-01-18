using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record BattleEndCondition(
        StageEndType BattleEndType,
        StageEndConditionType ConditionType,
        BattleEndConditionValue ConditionValue1,
        BattleEndConditionValue ConditionValue2)
    {
        public static BattleEndCondition Empty { get; } = new (
            StageEndType.Finish,
            StageEndConditionType.None,
            BattleEndConditionValue.Empty,
            BattleEndConditionValue.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}