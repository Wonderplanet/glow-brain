using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.TermsOfService.Domain.Model
{
    public record TermsUrlModel(
        ObscuredString TosUrl,
        ObscuredString PrivacyPolicyUrl,
        ObscuredString GlobalConsentUrl,
        ObscuredString InAppAdvertisementUrl);
}
