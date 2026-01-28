using System;
using GLOW.Scenes.ItemDetail.Presentation.Views;
using UIKit;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.SelectFragmentItemBoxTransit.Presentation
{
    public class SelectFragmentItemBoxTransitViewController : UIViewController<SelectFragmentItemBoxTransitView>, IEscapeResponder
    {
        public record Argument(ItemDetailAvailableLocationViewModel AvailableLocation);

        [Inject] ISelectFragmentItemBoxTransitViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] Argument Arg { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            EscapeResponderRegistry.Bind(this, ActualView);
            ActualView.SetUp(Arg.AvailableLocation, OnTransitionButtonTapped);
        }

        void OnTransitionButtonTapped(ItemDetailEarnLocationViewModel earnLocationViewModel, bool popBeforeDetail)
        {
            ViewDelegate.OnTransitionButtonTapped(earnLocationViewModel);
        }


        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnClose();
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            ViewDelegate.OnClose();
            return true;
        }
    }
}
