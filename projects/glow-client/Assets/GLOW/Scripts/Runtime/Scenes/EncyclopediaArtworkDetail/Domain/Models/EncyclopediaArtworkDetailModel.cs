using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Domain.Model;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Domain.Models
{
    public record EncyclopediaArtworkDetailModel(
        MasterDataId MstArtworkId,
        ArtworkName Name,
        ArtworkEffectDescription EffectDescription,
        IReadOnlyList<EncyclopediaArtworkFragmentListCellModel> ArtworkFragmentList,
        ArtworkUnlockFlag ArtworkUnlockFlag,
        EnableArtworkChangeFlag IsEnableSwitchOutpostArtwork);
}
