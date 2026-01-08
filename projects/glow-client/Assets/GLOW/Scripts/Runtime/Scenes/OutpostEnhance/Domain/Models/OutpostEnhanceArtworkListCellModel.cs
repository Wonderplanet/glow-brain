using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Domain.Model;

namespace GLOW.Scenes.OutpostEnhance.Domain.Models
{
    public record OutpostEnhanceArtworkListCellModel(MasterDataId MstArtworkId,
        ArtworkPanelModel ArtworkPanelModel,
        NotificationBadge Badge,
        bool IsLock,
        bool IsSelect);
}
