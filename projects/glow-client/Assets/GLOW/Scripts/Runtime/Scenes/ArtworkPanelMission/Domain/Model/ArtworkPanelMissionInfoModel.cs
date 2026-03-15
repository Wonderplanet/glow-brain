using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Domain.Model;

namespace GLOW.Scenes.ArtworkPanelMission.Domain.Model
{
    public record ArtworkPanelMissionInfoModel(
        MasterDataId MstArtworkPanelMissionId,
        MasterDataId MstEventId,
        ArtworkPanelModel ArtworkPanelModel,
        RemainingTimeSpan RemainingTimeSpan)
    {
        public static ArtworkPanelMissionInfoModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            ArtworkPanelModel.Empty,
            RemainingTimeSpan.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}