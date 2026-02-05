using System;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Transitions;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.AdventBattle.Presentation.View.AdventBattleInfo;
using GLOW.Scenes.AdventBattleMission.Presentation.View;
using GLOW.Scenes.AdventBattleRanking.Presentation.Views;
using GLOW.Scenes.AdventBattleRewardList.Presentation.View;
using GLOW.Scenes.EventBonusUnitList.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views.HomeStageLimitStatusView;
using GLOW.Scenes.HomePartyFormation.Presentation.Views;
using GLOW.Scenes.InGameSpecialRule.Presentation.Views;
using UIKit;
using WonderPlanet.SceneManagement;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.AdventBattle.Presentation.Presenter
{
    public class AdventBattleWireFrame
    {
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] ISceneNavigation SceneNavigation { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }

        public void ShowCloseMessage(Action onClose)
        {
            MessageViewUtil.ShowMessageWithClose("確認",
                "降臨バトルは終了しました。\n次回開催をお待ちください。",
                "",
                onClose:onClose);
        }
        public void ShowLimitChallengeMessage(Action onClose = null)
        {
            MessageViewUtil.ShowMessageWithClose("確認",
                "挑戦回数が上限になりました。\n挑戦可能になるまでお待ち下さい。",
                "",
                onClose:onClose);
        }

        public void ShowEmptyChallengeCountMessage()
        {
            MessageViewUtil.ShowMessageWithClose("確認",
                "本日分の降臨バトルへの挑戦可能回数が0回となりましたので挑戦できません。");
        }

        public void ShowEnemyDetail(UIViewController viewController,AdventBattleInfoViewController.Argument argument)
        {
            var controller = ViewFactory.Create<AdventBattleInfoViewController, AdventBattleInfoViewController.Argument>(argument);
            controller.ReopenStageInfoAction = () =>
            {
                ShowEnemyDetail(viewController, argument);
            };
            viewController.PresentModally(controller);
        }

        public void ShowRankingView(AdventBattleRankingViewController.Argument argument)
        {
            var controller = ViewFactory.Create<AdventBattleRankingViewController, AdventBattleRankingViewController.Argument>(argument);
            HomeViewNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);
        }

        public void ShowBonusUnitView(UIViewController viewController, EventBonusUnitListViewController.Argument argument)
        {
            var controller = ViewFactory.Create<EventBonusUnitListViewController, EventBonusUnitListViewController.Argument>(argument);
            viewController.PresentModally(controller);
        }

        public void ShowPartyFormationView(HomePartyFormationViewController.Argument argument)
        {
            var controller = ViewFactory.Create<HomePartyFormationViewController, HomePartyFormationViewController.Argument>(argument);
            HomeViewNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);
        }

        public void ShowSpecialRuleView(UIViewController viewController, InGameSpecialRuleViewController.Argument argument)
        {
            var controller = ViewFactory.Create<InGameSpecialRuleViewController, InGameSpecialRuleViewController.Argument>(argument);
            viewController.PresentModally(controller);
        }
        
        public void ShowAdventBattleMissionView(UIViewController viewController, Action<NotificationBadge> adventBattleMissionBadgeAction)
        {
            var missionViewController = ViewFactory.Create<AdventBattleMissionViewController>();
            missionViewController.AdventBattleMissionBadgeAction = adventBattleMissionBadgeAction;
            viewController.PresentModally(missionViewController);
        }

        public void ShowAdventBattleRewardListView(AdventBattleRewardListViewController.Argument argument)
        {
            var rewardListViewController = ViewFactory.Create<AdventBattleRewardListViewController, AdventBattleRewardListViewController.Argument>(argument);
            HomeViewNavigation.TryPush(rewardListViewController, HomeContentDisplayType.BottomOverlap);
        }
        
        public void ShowLimitStatusView(UIViewController viewController,HomeStageLimitStatusViewController.Argument argument)
        {
            var controller =
                ViewFactory.Create<HomeStageLimitStatusViewController, HomeStageLimitStatusViewController.Argument>(argument);

            viewController.PresentModally(controller);
        }

        public void TransitInGame()
        {
            SceneNavigation.Switch<InGameTransition>(default, "InGame").Forget();
        }
    }
}
