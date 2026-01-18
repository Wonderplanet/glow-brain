using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.ValueObjects;
using ArtworkEffectDescription = GLOW.Core.Domain.ValueObjects.ArtworkEffectDescription;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.ViewModels
{
    public record EncyclopediaArtworkDetailViewModel(
        MasterDataId MstArtworkId,
        ArtworkName Name,
        ArtworkEffectDescription EffectDescription,
        IReadOnlyList<EncyclopediaArtworkFragmentListCellViewModel> ArtworkFragmentList,
        ArtworkUnlockFlag ArtworkUnlock,
        EnableArtworkChangeFlag IsEnableSwitchOutpostArtwork);
}
