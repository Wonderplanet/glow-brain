using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Domain.Model;
using GLOW.Scenes.EncyclopediaSeries.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaSeries.Domain.Models
{
    public record EncyclopediaArtworkListCellModel(
        MasterDataId MstArtworkId,
        ArtworkPanelModel ArtworkPanelModel,
        EncyclopediaUnlockFlag IsUnlocked,
        EncyclopediaUnlockFlag IsUsing,
        NotificationBadge IsNew);
}
