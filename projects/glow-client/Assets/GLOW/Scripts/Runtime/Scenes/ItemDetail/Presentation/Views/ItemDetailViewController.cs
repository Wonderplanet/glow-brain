using GLOW.Core.Domain.Constants;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.ItemDetail.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.ItemDetail.Presentation.Views
{
    public class ItemDetailViewController : UIViewController<ItemDetailView>
    {
        public record Argument(ItemDetailWithTransitViewModel ViewModel, ShowTransitAreaFlag ShowTransitAreaFlag, bool PopBeforeDetail);

        [Inject] IItemDetailViewDelegate ViewDelegate { get; }
        [Inject] Argument Args { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ViewDelegate.OnViewDidLoad();
        }


        public void InitializePlayerResourceDetail(ItemDetailWithTransitViewModel viewModel, bool popBeforeDetail)
        {
            ActualView.InitializePlayerResourceDetail(viewModel, OnTransitionButtonTapped, Args.ShowTransitAreaFlag.Value, popBeforeDetail);
        }

        public void PlayShowAnimation()
        {
            ActualView.PlayShowAnimation();
        }

        public void PlayCloseAnimation()
        {
            ActualView.PlayCloseAnimation();
        }

        [UIAction]
        public void OnCloseButtonTapped()
        {
            ViewDelegate.OnCloseSelected();
        }

        void OnTransitionButtonTapped(ItemDetailEarnLocationViewModel earnLocationViewModel, bool popBeforeDetail)
        {
            switch (earnLocationViewModel.TransitionType)
            {
                case ItemTransitionType.MainQuest:
                    ViewDelegate.OnTransitionMainQuest(earnLocationViewModel.MasterDataId, earnLocationViewModel.TransitionPossibleFlag, popBeforeDetail);
                    break;
                case ItemTransitionType.EventQuest:
                    ViewDelegate.OnTransitionEventQuest(earnLocationViewModel.MasterDataId, earnLocationViewModel.TransitionPossibleFlag);
                    break;
                case ItemTransitionType.ShopItem:
                    ViewDelegate.OnTransitionShop(ShopContentTypes.Shop, earnLocationViewModel.MasterDataId, earnLocationViewModel.TransitionPossibleFlag);
                    break;
                case ItemTransitionType.Pack:
                    ViewDelegate.OnTransitionShop(ShopContentTypes.Pack, earnLocationViewModel.MasterDataId, earnLocationViewModel.TransitionPossibleFlag);
                    break;
                case ItemTransitionType.Achievement:
                    ViewDelegate.OnTransitionMission(MissionType.Achievement);
                    break;
                case ItemTransitionType.LoginBonus:
                    ViewDelegate.OnTransitionMission(MissionType.DailyBonus);
                    break;
                case ItemTransitionType.DailyMission:
                    ViewDelegate.OnTransitionMission(MissionType.Daily);
                    break;
                case ItemTransitionType.WeeklyMission:
                    ViewDelegate.OnTransitionMission(MissionType.Weekly);
                    break;
                case ItemTransitionType.Patrol:
                    ViewDelegate.OnTransitionExploration();
                    break;
                case ItemTransitionType.ExchangeShop:
                    ViewDelegate.OnTransitionExchangeShop(earnLocationViewModel.MasterDataId);
                    break;
            }
        }
    }
}
