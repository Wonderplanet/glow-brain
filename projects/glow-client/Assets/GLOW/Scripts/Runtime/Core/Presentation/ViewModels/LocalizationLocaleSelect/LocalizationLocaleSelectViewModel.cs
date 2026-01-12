namespace GLOW.Core.Presentation.ViewModels.LocalizationLocaleSelect
{
    public record LocalizationLocaleSelectViewModel(
        string Locale,
        string TitleText,
        string ButtonTextJa,
        string ButtonTextEn,
        string ButtonTextCh_Hant
        )
    {
        public string Locale { get; } = Locale;
        public string TitleText { get; } = TitleText;
        public string ButtonTextJa { get; } = ButtonTextJa;
        public string ButtonTextEn { get; } = ButtonTextEn;
        public string ButtonTextCh_Hant { get; } = ButtonTextCh_Hant;
    }
}
