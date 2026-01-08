using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record UnitSummonQueueElement(
        MasterDataId EnemyId,
        UnitGenerationModel UnitGenerationModel)
    {
        public static UnitSummonQueueElement Empty { get; } = new(
            MasterDataId.Empty,
            UnitGenerationModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
