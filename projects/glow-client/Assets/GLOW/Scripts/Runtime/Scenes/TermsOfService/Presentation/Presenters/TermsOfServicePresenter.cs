using GLOW.Core.Domain.Models;
using GLOW.Scenes.TermsOfService.Domain.Model;
using GLOW.Scenes.TermsOfService.Domain.UseCases;
using GLOW.Scenes.TermsOfService.Presentation.Views;
using WonderPlanet.OpenURLExtension;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Scenes.TermsOfService.Presentation.Presenters
{
    public class TermsOfServicePresenter : ITermsOfServiceViewDelegate
    {
        [Inject] TermsOfServiceViewController ViewController { get; }
        [Inject] TermsOfServiceUseCases TermsOfServiceUseCases { get; }
        [Inject] GetTermsOfServiceUrlUseCase GetTermsOfServiceUrlUseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }

        TermsUrlModel _termsUrlModel;

        void ITermsOfServiceViewDelegate.OnViewDidLoad()
        {
            _termsUrlModel = GetTermsOfServiceUrlUseCase.GetTermsUrlModel();
        }

        void ITermsOfServiceViewDelegate.OnShowTosUrl()
        {
            CustomOpenURL.OpenURL(_termsUrlModel.TosUrl);
        }

        void ITermsOfServiceViewDelegate.OnShowPrivacyPolicyUrl()
        {
            CustomOpenURL.OpenURL(_termsUrlModel.PrivacyPolicyUrl);
        }

        void ITermsOfServiceViewDelegate.OnShowGlobalConsentUrl()
        {
            CustomOpenURL.OpenURL(_termsUrlModel.GlobalConsentUrl);
        }

        void ITermsOfServiceViewDelegate.OnShowInAppAdvertisementUrl()
        {
            CustomOpenURL.OpenURL(_termsUrlModel.InAppAdvertisementUrl);
        }

        void ITermsOfServiceViewDelegate.OnLicenseAgree()
        {
            DoAsync.Invoke(ViewController.ActualView, ScreenInteractionControl, async (cancellationToken) =>
            {
                await TermsOfServiceUseCases.AgreeToLicense(cancellationToken);
                ViewController.Dismiss(completion: () => ViewController.NotifyAgreement());
            });
        }

        void ITermsOfServiceViewDelegate.OnLicenseDisagree()
        {
            ViewController.Dismiss(completion: () => ViewController.NotifyDisagreement());
        }
    }
}
