using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record DeckUnitSummonQueueElement(MasterDataId Id, BattleSide BattleSide)
    {
        public static DeckUnitSummonQueueElement Empty { get; } = new(
            MasterDataId.Empty,
            BattleSide.Player);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
