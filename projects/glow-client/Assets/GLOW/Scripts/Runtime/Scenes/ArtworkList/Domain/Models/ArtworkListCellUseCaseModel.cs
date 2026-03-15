using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Domain.Model;

namespace GLOW.Scenes.ArtworkList.Domain.Models
{
    public record ArtworkListCellUseCaseModel(
        MasterDataId MstArtworkId,
        ArtworkCompleteFlag IsCompleted,
        ArtworkPanelModel ArtworkPanelModel,
        Rarity Rarity,
        ArtworkGradeLevel Grade);
}

