using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.Mission.Presentation.ViewModel.AchievementMission;

namespace GLOW.Scenes.Mission.Presentation.View.AchievementMission
{
    public interface IAchievementMissionViewDelegate
    {
        void OnViewDidLoad();
        void ReceiveReward(IAchievementMissionCellViewModel viewModel, Action onReceiveComplete = null);
        void BulkReceive();
        void OnChallenge(
            MasterDataId mstAchievementId,
            DestinationScene destination,
            CriterionValue criterionValue);
        void OnRewardIconSelected(PlayerResourceIconViewModel playerResourceIconViewModel);
        void OnEscape();
    }
}
