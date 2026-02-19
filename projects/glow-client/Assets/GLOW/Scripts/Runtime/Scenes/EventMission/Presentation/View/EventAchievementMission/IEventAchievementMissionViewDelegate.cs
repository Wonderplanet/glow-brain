using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.EventMission.Presentation.ViewModel.EventMissionCell;

namespace GLOW.Scenes.EventMission.Presentation.View.EventAchievementMission
{
    public interface IEventAchievementMissionViewDelegate
    {
        void OnViewDidLoad();
        void ReceiveMissionReward(IEventMissionCellViewModel viewModel);
        void OnChallenge(IEventMissionCellViewModel viewModel, Action onTransitionCompleted);
        void OnRewardIconSelected(PlayerResourceIconViewModel playerResourceIconViewModel);
        void OnEscape();
    }
}
