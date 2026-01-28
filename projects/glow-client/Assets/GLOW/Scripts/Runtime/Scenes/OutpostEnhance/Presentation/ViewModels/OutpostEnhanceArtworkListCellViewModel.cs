using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Presentation.ViewModels;

namespace GLOW.Scenes.OutpostEnhance.Presentation.ViewModels
{
    public record OutpostEnhanceArtworkListCellViewModel(
        MasterDataId MstArtworkId,
        ArtworkFragmentPanelViewModel ArtworkFragmentPanelViewModel,
        NotificationBadge Badge,
        bool IsLock,
        bool IsSelect);
}
