using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaResult.Domain.Model
{
    public record GachaResultResourceModel(
        PlayerResourceModel PlayerResourceModel,
        IsNewUnitBadge IsNewUnitBadge
    )
    {
        public static GachaResultResourceModel Empty { get; } = new GachaResultResourceModel(PlayerResourceModel.Empty, IsNewUnitBadge.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
