using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.BeginnerMission.Domain.ValueObject;

namespace GLOW.Scenes.BeginnerMission.Presentation.ViewModel
{
    public class BeginnerMissionCellViewModel : IBeginnerMissionCellViewModel
    {
        public MasterDataId BeginnerMissionId { get; }
        public MissionStatus MissionStatus { get; }
        public MissionProgress MissionProgress { get; }
        public CriterionValue CriterionValue { get; }
        public CriterionCount CriterionCount { get; }
        public IReadOnlyList<PlayerResourceIconViewModel> PlayerResourceIconViewModels { get; }
        public BonusPoint BonusPoint { get; }
        public BeginnerMissionLockFlag IsLock { get; }
        public MissionDescription MissionDescription { get; }
        public DestinationScene DestinationScene { get; }
        
        public BeginnerMissionCellViewModel(
            MasterDataId beginnerMissionId, 
            MissionStatus missionStatus, 
            MissionProgress missionProgress, 
            CriterionValue criterionValue,
            CriterionCount criterionCount, 
            IReadOnlyList<PlayerResourceIconViewModel> playerResourceIconViewModels, 
            BonusPoint bonusPoint, 
            BeginnerMissionLockFlag isLock,
            MissionDescription missionDescription, 
            DestinationScene destinationScene)
        {
            BeginnerMissionId = beginnerMissionId;
            MissionStatus = missionStatus;
            MissionProgress = missionProgress;
            CriterionValue = criterionValue;
            CriterionCount = criterionCount;
            PlayerResourceIconViewModels = playerResourceIconViewModels;
            BonusPoint = bonusPoint;
            IsLock = isLock;
            MissionDescription = missionDescription;
            DestinationScene = destinationScene;
        }
    }
}