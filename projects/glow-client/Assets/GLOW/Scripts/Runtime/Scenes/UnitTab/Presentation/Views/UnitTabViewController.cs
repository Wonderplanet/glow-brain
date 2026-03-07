using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitTab.Domain.Constants;
using UIKit;
using Zenject;

namespace GLOW.Scenes.UnitTab.Presentation.Views
{
    public interface IUnitTabViewControl
    {
        void TransitToUnitList();
        void TransitToOutpostEnhance();
        void TransitToPartyFormation();
        void TransitToArtworkFormation();
        void TransitToArtworkList();
    }

    public class UnitTabViewController : UIViewController<UnitTabView>, IUnitTabViewControl
    {
        public record Argument(UnitTabType Type);

        [Inject] IUnitTabViewDelegate ViewDelegate { get; }

        UIViewController _currentContentViewController;
        public UIViewController CurrentContentViewController => _currentContentViewController;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.ViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ViewDelegate.ViewWillAppear();
        }

        public override void UnloadView()
        {
            base.UnloadView();
            ViewDelegate.UnloadView();
        }

        public void SetBadge(NotificationBadge unitList, NotificationBadge outpostEnhance)
        {
            ActualView.SetBadge(unitList, outpostEnhance);
        }

        public void SetTabOn(UnitTabType type)
        {
            ActualView.SetTabOn(type.ToString());
        }

        public void SetBackgroundRectTop(float top)
        {
            ActualView.SetBackgroundRectTop(top);
        }

        public void ShowCurrentContent(UIViewController viewController, bool animated = true, bool worldPositionStays = true)
        {
            _currentContentViewController = viewController;

            Show(viewController, animated);
            viewController.View.transform.SetParent(ActualView.ContentRoot, worldPositionStays);
        }

        public void TransitToUnitList()
        {
            ViewDelegate.UnitListTabSelect();
        }

        public void TransitToOutpostEnhance()
        {
            ViewDelegate.OutpostEnhanceTabSelect();
        }

        public void TransitToPartyFormation()
        {
            ViewDelegate.PartyFormationTabSelect();
        }

        public void TransitToArtworkFormation()
        {
            ViewDelegate.ArtworkFormationTabSelect();
        }

        public void TransitToArtworkList()
        {
            ViewDelegate.ArtworkListTabSelect();
        }

        [UIAction]
        void OnUnitListTabSelected()
        {
            ViewDelegate.UnitListTabSelect();
        }

        [UIAction]
        void OnPartyFormationTabSelected()
        {
            ViewDelegate.PartyFormationTabSelect();
        }

        [UIAction]
        void OnOutpostEnhanceTabSelected()
        {
            ViewDelegate.OutpostEnhanceTabSelect();
        }

        [UIAction]
        void OnArtworkFormationTabSelected()
        {
            ViewDelegate.ArtworkFormationTabSelect();
        }

        [UIAction]
        void OnArtworkListTabSelected()
        {
            ViewDelegate.ArtworkListTabSelect();
        }
    }
}
