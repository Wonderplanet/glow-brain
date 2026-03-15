using System.Collections.Generic;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.ArtworkFragmentAcquisition.Domain.Models;
using GLOW.Scenes.ArtworkPanelMission.Domain.ValueObject;

namespace GLOW.Scenes.ArtworkPanelMission.Domain.Model
{
    public record ArtworkPanelMissionReceiveResultModel(
        IReadOnlyList<CommonReceiveResourceModel> CommonReceiveResourceModels,
        ArtworkPanelMissionFetchResultModel ArtworkPanelMissionFetchResultModel)
    {
        public static ArtworkPanelMissionReceiveResultModel Empty { get; } =
            new(
                new List<CommonReceiveResourceModel>(),
                ArtworkPanelMissionFetchResultModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
    }
}