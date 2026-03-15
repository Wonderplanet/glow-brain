using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Domain.Model;
using GLOW.Scenes.ArtworkPanelMission.Domain.ValueObject;

namespace GLOW.Scenes.ArtworkPanelMission.Domain.Model
{
    public record ArtworkPanelMissionModel(
        MasterDataId MstArtworkPanelMissionId,
        MasterDataId MstEventId,
        ArtworkPanelModel ArtworkPanelModel,
        RemainingTimeSpan RemainingTimeSpan,
        ArtworkPanelMissionFetchResultModel ArtworkPanelMissionFetchResultModel)
    {
        public static ArtworkPanelMissionModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            ArtworkPanelModel.Empty,
            RemainingTimeSpan.Empty,
            ArtworkPanelMissionFetchResultModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}