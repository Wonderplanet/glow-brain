using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record GimmickObjectToEnemyTransformationModel(
        MasterDataId EnemyId,
        UnitGenerationModel UnitGenerationModel,
        AutoPlayerSequenceElementId TransformTargetGimmickSequenceElementId)
    {
        public TickCount RemainingTransformDelay { get; init; } = new TickCount(75);

        public GimmickObjectTransformationStartedFlag IsTransformationStarted { get; init; } = GimmickObjectTransformationStartedFlag.False;

        public static GimmickObjectToEnemyTransformationModel Empty { get; } = new (
            MasterDataId.Empty,
            UnitGenerationModel.Empty,
            AutoPlayerSequenceElementId.Empty);
    }
}
