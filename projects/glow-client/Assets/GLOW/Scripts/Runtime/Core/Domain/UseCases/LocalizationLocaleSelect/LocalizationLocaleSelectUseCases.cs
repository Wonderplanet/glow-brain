using WPFramework.Domain.Modules;
using WPFramework.Modules.Localization;
using Zenject;

namespace GLOW.Core.Domain.UseCases.LocalizationLocaleSelect
{
    public sealed class LocalizationLocaleSelectUseCases
    {
        [Inject] ILocalizationLocaleSelector LocalizationLocaleSelector { get; }
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }

        public LocalizationLocaleSelectUseCaseModel GetLanguageSelect()
        {
            var applicationSystemInfo = SystemInfoProvider.GetApplicationSystemInfo();
            var localizationLocaleCode = applicationSystemInfo.LocalizationLocaleCode;
            return new LocalizationLocaleSelectUseCaseModel(localizationLocaleCode);
        }

        public void SetLocaleName(string newLocaleName)
        {
            LocalizationLocaleSelector.ReserveNewLocale(newLocaleName);
        }
    }
}
