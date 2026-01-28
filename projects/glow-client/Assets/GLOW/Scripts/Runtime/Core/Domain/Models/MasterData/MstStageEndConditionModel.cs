using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Core.Domain.Models
{
    public record MstStageEndConditionModel(
        MasterDataId MstStageId,
        StageEndType StageEndType,
        StageEndConditionType ConditionType,
        BattleEndConditionValue ConditionValue1,
        BattleEndConditionValue ConditionValue2)
    {
        public static MstStageEndConditionModel Empty { get; } = new MstStageEndConditionModel(
            MasterDataId.Empty,
            StageEndType.Victory,
            StageEndConditionType.EnemyOutpostBreakDown,
            BattleEndConditionValue.Empty,
            BattleEndConditionValue.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
