using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.StaminaRecover;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.ItemDetail.Presentation.Views;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.ItemDetail.Presentation.Presenters
{
    public class ItemDetailPresenter : IItemDetailViewDelegate
    {
        [Inject] ItemDetailViewController ViewController { get; }
        [Inject] ItemDetailViewController.Argument Argument { get; }
        [Inject] ItemDetailTransitionWireFrame ItemDetailTransitionWireFrame { get; }

        void IItemDetailViewDelegate.OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(ItemDetailPresenter), nameof(IItemDetailViewDelegate.OnViewDidLoad));

            ViewController.InitializePlayerResourceDetail(Argument.ViewModel, Argument.PopBeforeDetail);
            ViewController.PlayShowAnimation();
        }

        void IItemDetailViewDelegate.OnCloseSelected()
        {
            ViewController.PlayCloseAnimation();
            ViewController.Dismiss();
        }
        void IItemDetailViewDelegate.OnTransitionMainQuest(
            MasterDataId masterDataId,
            TransitionPossibleFlag transitionPossibleFlag,
            bool popBeforeDetail)
        {
            ItemDetailTransitionWireFrame.OnTransitionMainQuest(masterDataId, transitionPossibleFlag, popBeforeDetail);
        }

        void IItemDetailViewDelegate.OnTransitionEventQuest(
            MasterDataId masterDataId,
            TransitionPossibleFlag transitionPossibleFlag)
        {
            ItemDetailTransitionWireFrame.OnTransitionEventQuest(masterDataId, transitionPossibleFlag);
        }

        void IItemDetailViewDelegate.OnTransitionShop(
            ShopContentTypes shopContentType,
            MasterDataId masterDataId,
            TransitionPossibleFlag transitionPossibleFlag)
        {
            ItemDetailTransitionWireFrame.OnTransitionShop(shopContentType, masterDataId, transitionPossibleFlag);
        }

        void IItemDetailViewDelegate.OnTransitionMission(MissionType missionType)
        {
            ItemDetailTransitionWireFrame.OnTransitionMission(missionType);
        }

        void IItemDetailViewDelegate.OnTransitionExploration()
        {
            ItemDetailTransitionWireFrame.OnTransitionExploration();
        }

        void IItemDetailViewDelegate.OnTransitionExchangeShop(MasterDataId masterDataId)
        {
            ItemDetailTransitionWireFrame.OnTransitionExchangeShop(masterDataId);
        }
    }
}
