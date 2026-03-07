using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.ArtworkPanelMission.Domain.ValueObject;

namespace GLOW.Scenes.ArtworkPanelMission.Presentation.ViewModel
{
    public record ArtworkPanelMissionFetchResultViewModel(
        IReadOnlyList<ArtworkPanelMissionCellViewModel> MissionListCellViewModels)
    {
        public static ArtworkPanelMissionFetchResultViewModel Empty { get; } = new(
            new List<ArtworkPanelMissionCellViewModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public ArtworkPanelMissionCount GetAchievedCount()
        {
            var achievedCount = MissionListCellViewModels.Count(cell => cell.MissionStatus == MissionStatus.Received);
            return new ArtworkPanelMissionCount(achievedCount);
        } 
        
        public ArtworkPanelMissionCount GetTotalCount()
        {
            var totalCount = MissionListCellViewModels.Count;
            return new ArtworkPanelMissionCount(totalCount);
        }
        
        public bool IsExistReceivableMission()
        {
            return MissionListCellViewModels.Any(cell => cell.MissionStatus == MissionStatus.Receivable);
        }
    }
}