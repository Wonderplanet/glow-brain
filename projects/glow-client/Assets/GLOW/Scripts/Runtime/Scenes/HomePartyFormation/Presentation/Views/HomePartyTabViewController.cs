using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using UIKit;
using UnityEngine;
using Zenject;

namespace GLOW.Scenes.HomePartyFormation.Presentation.Views
{
    public class HomePartyTabViewController : UIViewController<HomePartyTabView>
    {
        public record Argument(
            MasterDataId SpecialRuleTargetMstStageId,
            InGameContentType SpecialRuleContentType,
            EventBonusGroupId EventBonusGroupId,
            MasterDataId EnhanceQuestId);
        [Inject] IHomePartyTabViewDelegate ViewDelegate { get; }

        UIViewController _currentViewController;
        public UIViewController CurrentViewController => _currentViewController;

        public RectTransform ContentRoot => ActualView.ContentRoot;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public void SetCurrentViewController(UIViewController viewController)
        {
            _currentViewController = viewController;
        }

        [UIAction]
        void OnUnitPartyFormationTabSelected()
        {
            ViewDelegate.OnUnitPartyFormationTabSelected();
            ActualView.SetTabOn(ActualView.UnitPartyListTabKey);
        }

        [UIAction]
        void OnArtworkPartyFormationTabSelected()
        {
            ViewDelegate.OnArtworkPartyFormationTabSelected();
            ActualView.SetTabOn(ActualView.ArtworkPartyTabKey);
        }

        [UIAction]
        void OnBackButtonTapped()
        {
            ViewDelegate.OnBackButtonTapped();
        }
    }
}
