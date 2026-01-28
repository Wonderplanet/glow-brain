using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.ItemBox.Presentation.Views;
using GLOW.Scenes.ItemDetail.Presentation.Views;
using GLOW.Scenes.SelectFragmentItemBoxTransit.Presentation;
using GLOW.Scenes.ShopBuyConform.Presentation.View;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ItemBox.Presentation.Presenters
{
    public class SelectionFragmentBoxWireFrame
    {
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IHomeViewControl HomeViewControl { get; }

        SelectionFragmentBoxViewController _selectionFragmentBoxViewController;
        SelectFragmentItemBoxTransitViewController _selectFragmentItemBoxTransitViewController;

        public void OnTapInfoButton(UIViewController parentViewController, ItemDetailAvailableLocationViewModel viewModel)
        {
            var argument = new SelectFragmentItemBoxTransitViewController.Argument(viewModel);
            var controller = ViewFactory.Create<
                SelectFragmentItemBoxTransitViewController,
                SelectFragmentItemBoxTransitViewController.Argument>(argument);

            _selectFragmentItemBoxTransitViewController = controller;
            parentViewController.PresentModally(controller);
        }
        public void OnCloseInfoButton()
        {
            _selectFragmentItemBoxTransitViewController?.Dismiss();
            _selectFragmentItemBoxTransitViewController = null;
        }

        public void OnTransitShop()
        {
            OnCloseInfoButton();
            OnCloseSelectionFragmentBoxView();

            // 交換所の方に商品があるので、交換所に遷移
            HomeViewControl.OnBasicShopSelected();
        }
        public void OnTransitMission()
        {
            OnCloseInfoButton();
            OnCloseSelectionFragmentBoxView();
            HomeViewControl.OnNormalMissionSelected();
        }

        public void ShowConfirmConsumption(ExchangeConfirmViewController.Argument argument, UIViewController parentViewController)
        {
            var controller = ViewFactory.Create<ExchangeConfirmViewController, ExchangeConfirmViewController.Argument>(argument);
            parentViewController.PresentModally(controller);
        }

        #region SelectionFragmentBoxViewController
        public void ShowSelectionFragmentBoxViewController(SelectionFragmentBoxViewController.Argument argument, UIViewController parentViewController)
        {
            var controller = ViewFactory.Create<SelectionFragmentBoxViewController, SelectionFragmentBoxViewController.Argument>(argument);
            _selectionFragmentBoxViewController = controller;

            parentViewController.PresentModally(controller);
        }
        public void ShowMessageForEmptySelection()
        {
            MessageViewUtil.ShowMessageWithOk(
                "確認",
                "交換するキャラのかけらを\n選択してください。");
        }

        public void OnCloseSelectionFragmentBoxView()
        {
            _selectionFragmentBoxViewController?.Dismiss();
            _selectionFragmentBoxViewController = null;
        }
        #endregion
    }
}
