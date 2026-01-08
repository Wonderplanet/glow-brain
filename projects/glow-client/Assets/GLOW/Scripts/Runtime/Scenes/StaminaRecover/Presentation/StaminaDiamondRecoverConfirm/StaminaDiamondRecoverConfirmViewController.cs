using System;
using UIKit;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.StaminaRecover.Presentation.StaminaDiamondRecoverConfirm
{
    public class StaminaDiamondRecoverConfirmViewController :
        UIViewController<StaminaDiamondRecoverConfirmView>,
        IEscapeResponder
    {
        [Inject] IStaminaDiamondRecoverConfirmViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public Action OnCancel { get; set; }
        public Action OnConfirm { get; set; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad(this);

            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            EscapeResponderRegistry.Unregister(this);

        }

        public void SetViewModel(StaminaDiamondRecoverConfirmViewModel viewModel)
        {
            ActualView.SetViewModel(viewModel);
        }

        [UIAction]
        void OnSpecificCommerceButtonTapped()
        {
            ViewDelegate.SpecificCommerceButtonTapped();
        }

        [UIAction]
        void OnDecide()
        {
            ViewDelegate.OnRecoverAtDiamond();
        }

        [UIAction]
        void OnDiamondPurchaseButtonTapped()
        {
            ViewDelegate.TransitionToDiamondShopView();
        }

        [UIAction]
        void OnCancelButtonTapped()
        {
            Dismiss(true, () => OnCancel?.Invoke());
        }

        bool IEscapeResponder.OnEscape()
        {
            if (View.Hidden) return false;

            OnCancelButtonTapped();
            return true;
        }
    }
}
