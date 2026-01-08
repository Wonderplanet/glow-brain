using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models.CommonConditions;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record UnitTransformationModel(
        MasterDataId MstEnemyStageParameterId,                  // 変身先のキャラ
        ICommonConditionModel Condition,                        // 変身条件
        FieldObjectId BeforeUnitId,                             // 変身前のキャラ
        UnitTransformationFinishFlag IsTransformationFinish)
    {
        public static UnitTransformationModel Empty { get; } = new (
            MasterDataId.Empty,
            EmptyCommonConditionModel.Instance,
            FieldObjectId.Empty,
            UnitTransformationFinishFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsTransformed()
        {
            return !BeforeUnitId.IsEmpty();
        }
    }
}
