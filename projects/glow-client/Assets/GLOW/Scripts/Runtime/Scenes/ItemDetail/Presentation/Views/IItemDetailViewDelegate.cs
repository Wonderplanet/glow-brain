using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.StaminaRecover;
using GLOW.Scenes.Home.Domain.Constants;

namespace GLOW.Scenes.ItemDetail.Presentation.Views
{
    internal interface IItemDetailViewDelegate
    {
        void OnViewDidLoad();
        void OnCloseSelected();
        void OnTransitionMainQuest(MasterDataId masterDataId, TransitionPossibleFlag transitionPossibleFlag, bool popBeforeDetail = false);
        void OnTransitionEventQuest(MasterDataId masterDataId, TransitionPossibleFlag transitionPossibleFlag);
        void OnTransitionShop(ShopContentTypes shopContentType, MasterDataId masterDataId, TransitionPossibleFlag transitionPossibleFlag);
        void OnTransitionMission(MissionType missionType);
        void OnTransitionExploration();
        void OnTransitionExchangeShop(MasterDataId masterDataId);
    }
}
