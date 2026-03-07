using GLOW.Scenes.ArtworkFormation.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.HomePartyFormation.Domain.Constants;
using GLOW.Scenes.HomePartyFormation.Presentation.Views;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.HomePartyFormation.Presentation.Presenters
{
    public class HomePartyTabPresenter : IHomePartyTabViewDelegate
    {
        [Inject] HomePartyTabViewController.Argument Argument { get; }
        [Inject] HomePartyTabViewController ViewController { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }

        HomePartyFormationType _currentTabType = HomePartyFormationType.None;

        void IHomePartyTabViewDelegate.OnViewDidLoad()
        {
            SwitchContent(HomePartyFormationType.UnitPartyFormation);
        }

        void IHomePartyTabViewDelegate.OnArtworkPartyFormationTabSelected()
        {
            SwitchContent(HomePartyFormationType.ArtworkPartyFormation);
        }

        void IHomePartyTabViewDelegate.OnUnitPartyFormationTabSelected()
        {
            SwitchContent(HomePartyFormationType.UnitPartyFormation);
        }

        void IHomePartyTabViewDelegate.OnBackButtonTapped()
        {
            HomeViewNavigation.TryPop();
        }

        void SwitchContent(HomePartyFormationType type)
        {
            if (_currentTabType == type) return;

            ViewController.CurrentViewController?.Dismiss();
            var controller = CreateViewController(type);
            ViewController.SetCurrentViewController(controller);

            ViewController.Show(controller);
            controller.View.transform.SetParent(ViewController.ContentRoot, false);

            _currentTabType = type;
        }

        UIViewController CreateViewController(HomePartyFormationType type)
        {
            if (type == HomePartyFormationType.UnitPartyFormation)
            {
                var argment = new HomePartyFormationViewController.Argument(
                    Argument.SpecialRuleTargetMstStageId,
                    Argument.SpecialRuleContentType,
                    Argument.EventBonusGroupId,
                    Argument.EnhanceQuestId);

                return ViewFactory.Create<HomePartyFormationViewController,
                    HomePartyFormationViewController.Argument>(argment);
            }

            return ViewFactory.Create<ArtworkFormationViewController>();
        }
    }
}
