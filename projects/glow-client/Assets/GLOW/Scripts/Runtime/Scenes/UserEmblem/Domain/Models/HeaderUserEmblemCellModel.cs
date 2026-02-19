using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UserEmblem.Domain.Models
{
    public record HeaderUserEmblemCellModel(
        MasterDataId Id,
        EmblemIconAssetPath AssetPath,
        EmblemDescription Description,
        NotificationBadge Badge)
    {
        public static HeaderUserEmblemCellModel Empty { get; } = new(
            MasterDataId.Empty,
            new EmblemIconAssetPath(""),
            new EmblemDescription(""),
            new NotificationBadge(false));

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
