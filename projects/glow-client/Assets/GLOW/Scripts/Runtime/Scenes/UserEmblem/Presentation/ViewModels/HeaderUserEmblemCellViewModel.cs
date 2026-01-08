using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UserEmblem.Presentation.ViewModels
{
    public record HeaderUserEmblemCellViewModel(
        MasterDataId Id,
        EmblemIconAssetPath AssetPath,
        EmblemDescription Description,
        NotificationBadge Badge)
    {
        public static HeaderUserEmblemCellViewModel Empty { get; } = new(
            MasterDataId.Empty,
            new EmblemIconAssetPath(""),
            new EmblemDescription(""),
            new NotificationBadge(false));

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
