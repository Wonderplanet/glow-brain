using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.BeginnerMission.Presentation.ViewModel;

namespace GLOW.Scenes.BeginnerMission.Presentation.View
{
    public interface IBeginnerMissionContentViewDelegate
    {
        void OnViewDidLoad();
        void ReceiveMissionReward(IBeginnerMissionCellViewModel viewModel);
        void OnChallenge(
            MasterDataId mstBeginnerId, 
            DestinationScene destination, 
            CriterionValue criterionValue, 
            Action onTransitionCompleted);
        void OnRewardIconSelected(PlayerResourceIconViewModel playerResourceIconViewModel);
        void OnUnlockMissionButtonSelected();
        void OnMissionBonusPointSelected();
        void OnEscape();
    }
}