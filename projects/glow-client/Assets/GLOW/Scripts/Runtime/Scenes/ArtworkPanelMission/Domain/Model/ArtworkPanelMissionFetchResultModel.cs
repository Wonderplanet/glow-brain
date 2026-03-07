using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.ArtworkPanelMission.Domain.ValueObject;

namespace GLOW.Scenes.ArtworkPanelMission.Domain.Model
{
    public record ArtworkPanelMissionFetchResultModel(
        IReadOnlyList<ArtworkPanelMissionCellModel> ArtworkPanelMissionListCellModels)
    {
        public static ArtworkPanelMissionFetchResultModel Empty { get; } = new(
            new List<ArtworkPanelMissionCellModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}