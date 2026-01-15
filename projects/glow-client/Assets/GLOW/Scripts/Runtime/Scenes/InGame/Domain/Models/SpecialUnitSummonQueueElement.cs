using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record SpecialUnitSummonQueueElement(
        BattleSide BattleSide,
        MasterDataId Id,
        PageCoordV2 Pos)
    {
        public static SpecialUnitSummonQueueElement Empty { get; } = new (
            BattleSide.Player,
            MasterDataId.Empty,
            PageCoordV2.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
