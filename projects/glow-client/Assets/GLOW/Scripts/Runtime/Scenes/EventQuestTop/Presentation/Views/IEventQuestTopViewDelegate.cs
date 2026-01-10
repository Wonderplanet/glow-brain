using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.EventQuestTop.Presentation.ViewModels;
using UIKit;

namespace GLOW.Scenes.EventQuestTop.Presentation.Views
{
    public interface IEventQuestTopViewDelegate
    {
        void OnViewDidLoad();
        void OnViewWillAppear();
        void OnViewDidUnload();
        void OnStageInfoButtonTapped(MasterDataId mstStageId);
        void OnQuestUnReleasedClicked(StageReleaseRequireSentence stageReleaseRequireSentence);
        void OnBackButtonTapped();
        void OnMissionButtonTapped();
        void OnPartyEditButtonTapped(MasterDataId mstStageId);
        void OnInGameSpecialRuleTapped(MasterDataId selectedMstStageRuleGroupId);
        void OnEventExchangeShopButtonTapped();

        void OnStageStart(
            UIViewController controller,
            EventQuestTopElementViewModel selectedElementViewModel);

    }
}
