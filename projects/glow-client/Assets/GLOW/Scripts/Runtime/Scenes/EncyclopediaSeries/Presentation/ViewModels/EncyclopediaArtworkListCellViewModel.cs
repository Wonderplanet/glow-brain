using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaSeries.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaSeries.Presentation.ViewModels
{
    public record EncyclopediaArtworkListCellViewModel(MasterDataId MstArtworkId,
        ArtworkFragmentPanelViewModel FragmentPanelViewModel,
        EncyclopediaUnlockFlag IsUnlocked,
        EncyclopediaUnlockFlag IsUsing,
        NotificationBadge NewBadge);
}
