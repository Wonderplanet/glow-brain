using GLOW.Core.Presentation.Modules.Audio;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.OtherMenu.Presentation
{
    public class OtherMenuViewController : UIViewController<OtherMenuView>, IEscapeResponder
    {
        [Inject] IOtherMenuViewDelegate ViewDelegate { get; }

        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            EscapeResponderRegistry.Unregister(this);
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_003);
            ViewDelegate.OnCloseSelected();
            return true;
        }

        [UIAction]
        void OnTermsOfService()
        {
            ViewDelegate.OnTermsOfService();
        }

        [UIAction]
        void OnPrivacyPolicy()
        {
            ViewDelegate.OnPrivacyPolicy();
        }

        [UIAction]
        void OnInAppAdvertisement()
        {
            ViewDelegate.OnInAppAdvertisement();
        }

        [UIAction]
        void OnPrivacyOption()
        {
            ViewDelegate.OnPrivacyOption();
        }

        [UIAction]
        void OnCopyright()
        {
            ViewDelegate.OnCopyright();
        }

        [UIAction]
        void OnFundSettlement()
        {
            ViewDelegate.OnFundSettlement();
        }

        [UIAction]
        void OnSpecificCommerce()
        {
            ViewDelegate.OnSpecificCommerce();
        }

        [UIAction]
        void OnPurchaseLimit()
        {
            ViewDelegate.OnPurchaseLimit();
        }

        [UIAction]
        void OnAppAppliedBalance()
        {
            ViewDelegate.OnAppAppliedBalance();
        }

        [UIAction]
        void OnAccountDelete()
        {
            ViewDelegate.OnAccountDelete();
        }

        [UIAction]
        void OnCloseButton()
        {
            ViewDelegate.OnCloseSelected();
        }
    }
}
