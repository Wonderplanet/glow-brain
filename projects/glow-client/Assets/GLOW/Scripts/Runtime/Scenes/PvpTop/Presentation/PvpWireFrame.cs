using System;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Transitions;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.PvpRewardList.Presentation.View;
using GLOW.Scenes.PvpTop.Presentation.View.PvpTicketConfirm;
using UIKit;
using WonderPlanet.SceneManagement;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.PvpTop.Presentation
{
    public class PvpWireFrame
    {
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] ISceneNavigation SceneNavigation { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }

        public void ShowPvpRewardListView()
        {
            var rewardListViewController = ViewFactory.Create<PvpRewardListViewController>();
            HomeViewNavigation.TryPush(rewardListViewController, HomeContentDisplayType.BottomOverlap);
        }

        public void ShowTicketConfirmView(
            PvpTicketConfirmViewController.Argument argument,
            Action onApply,
            Action onShop,
            UIViewController parentViewController)
        {
            var vc = ViewFactory.Create<
                PvpTicketConfirmViewController,
                PvpTicketConfirmViewController.Argument>(argument);
            vc.OnApplyAction = onApply;
            vc.OnShopTransitAction = onShop;
            parentViewController.PresentModally(vc);
        }

        public void TransitInGame()
        {
            SceneNavigation.Switch<InGameTransition>(default, "InGame").Forget();
        }

        public void BackToHomeAfterPvpEnded()
        {
            MessageViewUtil.ShowMessageWithButton(
                "確認",
                "ランクマッチは終了しました。\n次回開催をお待ちください。\nホーム画面に移動します。",
                "",
                "はい",
                () =>
                {
                    HomeViewNavigation.Switch(HomeContentTypes.Main);
                });
        }
    }
}
