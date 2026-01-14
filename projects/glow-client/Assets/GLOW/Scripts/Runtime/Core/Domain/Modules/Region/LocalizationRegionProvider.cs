using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Modules.Localization;
using UnityEngine.Scripting;
using WPFramework.Modules.Localization;
using WPFramework.Modules.Region;

namespace GLOW.Core.Domain.Modules.Region
{
    public sealed class LocalizationRegionProvider : IApplicationRegionProvider
    {
        ILocalizationInformationProvider LocalizationInformationProvider { get; }

        string IApplicationRegionProvider.RegionCode => GetRegionCode();

        bool IApplicationRegionProvider.IsJapanRegion => GetRegionCode() == LanguageConverter.ToLocaleCode(Language.ja); 

        [Preserve]
        public LocalizationRegionProvider(ILocalizationInformationProvider localizationInformationProvider)
        {
            LocalizationInformationProvider = localizationInformationProvider;
        }

        string GetRegionCode()
        {
            // NOTE: 現在は言語情報からリージョンを特定する
            //       定義はISO3166-1 alpha-2に準拠
            //       https://ja.wikipedia.org/wiki/ISO_3166-1
            var language = LanguageConverter.ToLanguage(LocalizationInformationProvider.LocaleCode);
            return LanguageConverter.ToLocaleCode(language);
        }
    }
}
