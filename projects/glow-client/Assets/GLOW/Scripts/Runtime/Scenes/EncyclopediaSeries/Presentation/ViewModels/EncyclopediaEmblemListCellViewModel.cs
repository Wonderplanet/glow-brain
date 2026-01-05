using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaSeries.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaSeries.Presentation.ViewModels
{
    public record EncyclopediaEmblemListCellViewModel(
        MasterDataId MstEmblemId,
        EmblemIconAssetPath AssetPath,
        EncyclopediaUnlockFlag IsUnlocked,
        NotificationBadge NewBadge);
}
