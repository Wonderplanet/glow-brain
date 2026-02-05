using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record UnitTransformationParameter(
        MasterDataId MstEnemyStageParameterId,
        UnitTransformationConditionType ConditionType,
        UnitTransformationConditionValue ConditionValue)
    {
        public static UnitTransformationParameter Empty { get; } = new(
            MasterDataId.Empty,
            UnitTransformationConditionType.None,
            UnitTransformationConditionValue.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public InGameCommonConditionType GetCommonConditionType()
        {
            return ConditionType switch
            {
                UnitTransformationConditionType.HpPercentage => InGameCommonConditionType.MyHpLessThanOrEqualPercentage,
                UnitTransformationConditionType.StageTime => InGameCommonConditionType.StageTime,
                _ => InGameCommonConditionType.None
            };
        }
    }
}
