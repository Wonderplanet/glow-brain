using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.Constants;
using UIKit;
using Zenject;

namespace GLOW.Scenes.ShopTab.Presentation.View
{
    public interface IShopTabBadgeControl
    {
        // void ShowGachaTabNewBadges(bool isActive);
        // void ShowShopTabNewBadges(bool isActive);
        void ShowPackTabBadges(bool isActive);
    }

    public class ShopTabViewController : UIViewController<ShopTabView>, IShopTabBadgeControl
    {
        [Inject] IShopTabViewDelegate ViewDelegate { get; }

        // フッターから遷移か
        public bool IsTransitionedByFooter { get; set; }
        public UIViewController CurrentContentViewController
        {
            get;
            private set;
        }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ViewDelegate.OnViewWillAppear();
        }

        public void ShowCurrentContent(
            ShopContentTypes contentType,
            UIViewController viewController,
            bool isSwitchByTabTaped,
            bool animated = true)
        {
            switch (contentType)
            {
                case ShopContentTypes.Shop:
                case ShopContentTypes.Pack:
                case ShopContentTypes.Pass:
                    ShowShopContent(viewController, animated, isSwitchByTabTaped);
                    break;
                default:
                    throw new ArgumentOutOfRangeException(nameof(contentType), contentType, null);
            }

            ActualView.Tab.SetToggleOn(contentType.ToString());
        }

        public void UpdateBadgeStatus(bool isShopTabBadge, bool isPassTabBadge, bool isPackTabBadge)
        {
            ShowShopTabBadges(isShopTabBadge);
            ShowPassTabBadges(isPassTabBadge);
            ShowPackTabBadges(isPackTabBadge);
        }

        public void ShowShopTabBadges(bool isActive)
        {
            ActualView.ShopTabNewBadges.gameObject.SetActive(isActive);
        }

        public void ShowPackTabBadges(bool isActive)
        {
            ActualView.PackTabNewBadges.gameObject.SetActive(isActive);
        }

        public void ShowPassTabBadges(bool isActive)
        {
            ActualView.PassTabNewBadges.gameObject.SetActive(isActive);
        }

        public void OnChangeShopContent(ShopContentTypes types, MasterDataId oprProductId)
        {
            ViewDelegate.OnChangeShopContent(types, oprProductId);
        }

        void ShowShopContent(UIViewController viewController, bool animated, bool isSwitchByTabTaped)
        {
            CurrentContentViewController = viewController;

            viewController.View.transform.SetParent(ActualView.ContentRoot, false);
            AddChild(viewController);

            // タブタップによる切り替えのみ呼び出す、他画面からの遷移切り替えはHomeViewController側で呼び出している
            if (isSwitchByTabTaped)
            {
                viewController.BeginAppearanceTransition(true, animated);
                viewController.EndAppearanceTransition();
            }
        }

        [UIAction]
        void OnShopTabSelected()
        {
            ViewDelegate.OnTabTapped(ShopContentTypes.Shop);
        }

        [UIAction]
        void OnPassTabSelected()
        {
            ViewDelegate.OnTabTapped(ShopContentTypes.Pass);
        }

        [UIAction]
        void OnPackShopTabSelected()
        {
            ViewDelegate.OnTabTapped(ShopContentTypes.Pack);
        }

        [UIAction]
        void OnSpecificCommerceButtonTapped()
        {
            ViewDelegate.ShowSpecificCommerce();
        }

        [UIAction]
        void OnFundsSettlementButtonTapped()
        {
            ViewDelegate.ShowFundsSettlement();
        }
    }
}
