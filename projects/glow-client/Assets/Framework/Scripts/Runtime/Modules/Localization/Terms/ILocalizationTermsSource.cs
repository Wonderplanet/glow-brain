namespace WPFramework.Modules.Localization.Terms
{
    public interface ILocalizationTermsSource
    {
        public string Get(string key);
        public string Get(string key, params object[] args);
    }
}
