using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.AdventBattleMission.Presentation.ViewModel;

namespace GLOW.Scenes.AdventBattleMission.Presentation.View
{
    public interface IAdventBattleMissionViewDelegate
    {
        void OnViewWillAppear();
        void OnReceiveButtonTapped(AdventBattleMissionCellViewModel viewModel);
        void OnChallengeButtonTapped(DestinationScene destination, Action onTransitionCompleted);
        void OnRewardIconSelected(PlayerResourceIconViewModel viewModel);
        void OnBulkReceiveButtonTapped();
        void OnCloseButtonTapped();
        void OnEscape();
    }
}