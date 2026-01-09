using GLOW.Scenes.FragmentProvisionRatio.Presentation;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.ItemBox.Presentation.Views;
using GLOW.Scenes.ShopBuyConform.Presentation.View;
using GLOW.Scenes.UnitDetail.Presentation.Views;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ItemBox.Presentation.Presenters
{
    public class RandomFragmentBoxWireFrame
    {
        [Inject] IHomeViewControl HomeViewControl { get; }
        [Inject] RandomFragmentBoxViewController ViewController { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }

        RandomFragmentBoxViewController _fragmentBoxViewController;
        FragmentProvisionRatioViewController _ratioViewController;

        bool isClosed;

        public void RegisterRandomFragmentBoxViewController(RandomFragmentBoxViewController vc)
        {
            isClosed = false;
            // UnregisterはShouldCloseModalsでやる
            _fragmentBoxViewController = vc;
        }

        public void UnregisterRandomFragmentBoxViewController()
        {
            ShouldCloseModals();
        }
        public void OnUseSelected(ExchangeConfirmViewController.Argument argument)
        {
            var controller =
                ViewFactory.Create<ExchangeConfirmViewController, ExchangeConfirmViewController.Argument>(argument);
            ViewController.PresentModally(controller);
        }

        public void OnProvisionRatio(FragmentProvisionRatioViewController.Argument argument)
        {
            var controller =
                ViewFactory.Create<FragmentProvisionRatioViewController, FragmentProvisionRatioViewController.Argument>(
                    argument);
            _ratioViewController = controller;
            // HomeViewNavigation.Push(controller, ShowHomeViewType.FullScreenOverlap);
            _fragmentBoxViewController.Show(controller);

        }

        public void OnCloseProvisionRatio()
        {
            _ratioViewController?.Dismiss();
            _ratioViewController = null;
            if (_fragmentBoxViewController != null) _fragmentBoxViewController.Parent.View.Hidden = false;
        }

        public void OnShowUnitView(UnitDetailViewController.Argument argument)
        {
            PauseModals();
            var controller = ViewFactory.Create<UnitDetailViewController, UnitDetailViewController.Argument>(argument);
            controller.OnClose = ResumeModals;

            HomeViewNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);
        }

        public void OnTransitShop()
        {
            ShouldCloseModals();

            // 交換所の方に商品があるので、交換所に遷移
            HomeViewControl.OnBasicShopSelected();
        }

        void PauseModals()
        {
            if (_fragmentBoxViewController != null) _fragmentBoxViewController.Parent.View.Hidden = true;
            if (_ratioViewController != null) _ratioViewController.ActualView.Hidden = true;
        }

        void ResumeModals()
        {
            if (_fragmentBoxViewController != null) _fragmentBoxViewController.Parent.View.Hidden = false;
            if (_ratioViewController != null) _ratioViewController.ActualView.Hidden = false;
        }

        void ShouldCloseModals()
        {
            if (isClosed) return;
            isClosed = true;

            // ratioViewControllerはFragmentBoxViewControllerのChildにしてるのでDismissで一緒に消える
            _fragmentBoxViewController?.Dismiss();

            _fragmentBoxViewController = null;
            _ratioViewController = null;
        }
    }
}
