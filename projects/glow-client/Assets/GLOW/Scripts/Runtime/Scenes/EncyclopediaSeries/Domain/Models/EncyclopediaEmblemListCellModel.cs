using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaSeries.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaSeries.Domain.Models
{
    public record EncyclopediaEmblemListCellModel(
        MasterDataId MstEmblemId,
        EmblemIconAssetPath AssetPath,
        EncyclopediaUnlockFlag IsUnlocked,
        NotificationBadge IsNew);
}
