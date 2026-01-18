using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record InGameGimmickObjectGenerationModel(
        AutoPlayerSequenceElementId AutoPlayerSequenceElementId,
        FieldCoordV2 SummonPosition,
        MasterDataId MstInGameGimmickObjectId)
    {
        public static InGameGimmickObjectGenerationModel Empty { get; } = new (
            AutoPlayerSequenceElementId.Empty,
            FieldCoordV2.Empty,
            MasterDataId.Empty);
    }
}
