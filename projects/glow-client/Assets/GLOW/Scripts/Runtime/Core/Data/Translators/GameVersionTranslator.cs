using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;

namespace GLOW.Core.Data.Translators
{
    public static class GameVersionTranslator
    {
        public static GameVersionModel TranslateToModel(GameVersionResultData gameVersionResultData)
        {
            return new GameVersionModel(
                MstHash: gameVersionResultData.MstHash,
                OprHash: gameVersionResultData.OprHash,
                MstI18nHash: gameVersionResultData.MstI18nHash,
                OprI18nHash: gameVersionResultData.OprI18nHash,
                AssetHash: gameVersionResultData.AssetHash,
                MstPath: gameVersionResultData.MstPath,
                OprPath: gameVersionResultData.OprPath,
                MstI18nPath: gameVersionResultData.MstI18nPath,
                OprI18nPath: gameVersionResultData.OprI18nPath,
                AssetCatalogDataPath: gameVersionResultData.AssetCatalogDataPath,
                AssetVersion: gameVersionResultData.AssetVersion,
                TosVersion: gameVersionResultData.TosVersion,
                TosUserAgreeVersion: gameVersionResultData.TosUserAgreeVersion,
                TosUrl: gameVersionResultData.TosUrl,
                PrivacyPolicyVersion: gameVersionResultData.PrivacyPolicyVersion,
                PrivacyPolicyUserAgreeVersion: gameVersionResultData.PrivacyPolicyUserAgreeVersion,
                PrivacyPolicyUrl: gameVersionResultData.PrivacyPolicyUrl,
                GlobalConsentVersion: gameVersionResultData.GlobalCnsntVersion,
                GlobalConsentUserAgreeVersion: gameVersionResultData.GlobalCnsntUserAgreeVersion,
                GlobalConsentUrl: gameVersionResultData.GlobalCnsntUrl,
                InAppAdvertisementTermsVersion: gameVersionResultData.InAppAdvertisementVersion,
                InAppAdvertisementTermsUserAgreeVersion: gameVersionResultData.InAppAdvertisementUserAgreeVersion,
                InAppAdvertisementTermsUrl: gameVersionResultData.InAppAdvertisementUrl
                );
        }
    }
}
