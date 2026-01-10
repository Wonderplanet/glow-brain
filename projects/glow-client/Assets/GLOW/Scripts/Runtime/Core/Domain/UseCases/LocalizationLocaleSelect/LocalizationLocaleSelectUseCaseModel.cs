namespace GLOW.Core.Domain.UseCases.LocalizationLocaleSelect
{
    public record LocalizationLocaleSelectUseCaseModel(string LocalizationLocaleCode)
    {
        public string LocalizationLocaleCode { get; } = LocalizationLocaleCode;
    }
}
