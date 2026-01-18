using WondlerPlanet.CheatProtectKit.ObscuredTypes;
using WPFramework.Constants.MasterData;

namespace GLOW.Core.Domain.Models
{
    public record GameVersionModel(
        ObscuredString MstHash,
        ObscuredString OprHash,
        ObscuredString MstI18nHash,
        ObscuredString OprI18nHash,
        ObscuredString AssetHash,
        ObscuredString MstPath,
        ObscuredString OprPath,
        ObscuredString MstI18nPath,
        ObscuredString OprI18nPath,
        ObscuredString AssetCatalogDataPath,
        ObscuredString AssetVersion,
        ObscuredInt TosVersion,
        ObscuredInt TosUserAgreeVersion,
        ObscuredString TosUrl,
        ObscuredInt PrivacyPolicyVersion,
        ObscuredInt PrivacyPolicyUserAgreeVersion,
        ObscuredString PrivacyPolicyUrl,
        ObscuredInt GlobalConsentVersion,
        ObscuredInt GlobalConsentUserAgreeVersion,
        ObscuredString GlobalConsentUrl,
        ObscuredInt InAppAdvertisementTermsVersion,
        ObscuredInt InAppAdvertisementTermsUserAgreeVersion,
        ObscuredString InAppAdvertisementTermsUrl)
    {
        const int ShortHashLength = 6;

        public string GetMstPath(MasterType masterType)
        {
            return masterType switch
            {
                MasterType.Mst     => MstPath,
                MasterType.MstI18n => MstI18nPath,
                MasterType.Opr     => OprPath,
                MasterType.OprI18n => OprI18nPath,
                _                  => string.Empty
            };
        }

        public string GetMstHash(MasterType masterType)
        {
            return masterType switch
            {
                MasterType.Mst     => MstHash,
                MasterType.MstI18n => MstI18nHash,
                MasterType.Opr     => OprHash,
                MasterType.OprI18n => OprI18nHash,
                _                  => string.Empty
            };
        }

        public string MstShortHash => GenerateShortHashFromFullHash(MstHash, ShortHashLength);
        public string OprShortHash => GenerateShortHashFromFullHash(OprHash, ShortHashLength);
        public string AssetShortHash => GenerateShortHashFromFullHash(AssetHash, ShortHashLength);
        public string MstI18nShortHash => GenerateShortHashFromFullHash(MstI18nHash, ShortHashLength);
        public string OprI18nShortHash => GenerateShortHashFromFullHash(OprI18nHash, ShortHashLength);

        public bool IsAgreementConsented()
        {
            return TosVersion == TosUserAgreeVersion &&
                   PrivacyPolicyVersion == PrivacyPolicyUserAgreeVersion &&
                   GlobalConsentVersion == GlobalConsentUserAgreeVersion &&
                   InAppAdvertisementTermsVersion == InAppAdvertisementTermsUserAgreeVersion;
        }

        string GenerateShortHashFromFullHash(string fullHash, int length)
        {
            if (string.IsNullOrEmpty(fullHash))
            {
                return string.Empty;
            }

            return fullHash.Length <= length ? fullHash : fullHash.Substring(0, length);
        }
    }
}
