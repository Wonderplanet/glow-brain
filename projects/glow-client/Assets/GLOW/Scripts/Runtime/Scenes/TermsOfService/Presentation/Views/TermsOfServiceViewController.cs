using System;
using UIKit;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.TermsOfService.Presentation.Views
{
    public class TermsOfServiceViewController : UIViewController<TermsOfServiceView>, IEscapeResponder
    {
        [Serializable]
        public record Argument(Action Agree, Action Disagree)
        {
            public Action Agree { get; } = Agree;
            public Action Disagree { get; } = Disagree;
        }

        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] ITermsOfServiceViewDelegate ViewDelegate { get; }
        [Inject] Argument Args { get; set; }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            EscapeResponderRegistry.Unregister(this);
        }

        public void NotifyAgreement()
        {
            Args.Agree?.Invoke();
        }

        public void NotifyDisagreement()
        {
            Args.Disagree?.Invoke();
        }

        [UIAction]
        void OnAgree()
        {
            ViewDelegate.OnLicenseAgree();
        }

        [UIAction]
        void OnDisagree()
        {
            ViewDelegate.OnLicenseDisagree();
        }

        [UIAction]
        void OnShowTerms()
        {
            ViewDelegate.OnShowTosUrl();
        }

        [UIAction]
        void OnShowPrivacyPolicy()
        {
            ViewDelegate.OnShowPrivacyPolicyUrl();
        }

        [UIAction]
        void OnShowGlobalConsent()
        {
            ViewDelegate.OnShowGlobalConsentUrl();
        }

        [UIAction]
        void OnShowInAppAdvertisement()
        {
            ViewDelegate.OnShowInAppAdvertisementUrl();
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            Args.Disagree?.Invoke();
            return true;
        }
    }
}
