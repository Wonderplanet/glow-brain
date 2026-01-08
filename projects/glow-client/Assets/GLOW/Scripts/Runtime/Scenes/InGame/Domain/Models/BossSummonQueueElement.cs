using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record BossSummonQueueElement(MasterDataId EnemyId, UnitGenerationModel UnitGenerationModel)
    {
        public static BossSummonQueueElement Empty { get; } = new(MasterDataId.Empty, UnitGenerationModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
