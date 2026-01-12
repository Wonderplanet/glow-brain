namespace GLOW.Scenes.TermsOfService.Presentation.Views
{
    public interface ITermsOfServiceViewDelegate
    {
        void OnViewDidLoad();
        void OnShowTosUrl();
        void OnShowPrivacyPolicyUrl();
        void OnShowGlobalConsentUrl();
        void OnShowInAppAdvertisementUrl();
        void OnLicenseAgree();
        void OnLicenseDisagree();
    }
}
