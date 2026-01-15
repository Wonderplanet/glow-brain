using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace WPFramework.Domain.Models
{
    public partial record EnvironmentModel
    {
        ObscuredString _obscured_env;
        ObscuredString _obscured_name;
        ObscuredString _obscured_description;
        ObscuredString _obscured_api;
        ObscuredString _obscured_assetCdn;
        ObscuredString _obscured_masterCdn;
        ObscuredString _obscured_webCdn;
        ObscuredString _obscured_announcementCdn;
        ObscuredString _obscured_bannerCdn;
        ObscuredString _obscured_agreementCdn;

        public string Env => _obscured_env;
        public string Name => _obscured_name;
        public string Description => _obscured_description;
        public string Api => _obscured_api;
        public string AssetCdn => _obscured_assetCdn;
        public string MasterCdn => _obscured_masterCdn;
        public string WebCdn => _obscured_webCdn;
        public string AnnouncementCdn => _obscured_announcementCdn;
        public string BannerCdn => _obscured_bannerCdn;
        public string AgreementCdn => _obscured_agreementCdn;

        public EnvironmentModel(string Env, string Name, string Description, string Api, string AssetCdn, string MasterCdn, string WebCdn, string AnnouncementCdn, string BannerCdn, string AgreementCdn)
        {
            _obscured_env = Env ?? string.Empty;
            _obscured_name = Name ?? string.Empty;
            _obscured_description = Description ?? string.Empty;
            _obscured_api = Api ?? string.Empty;
            _obscured_assetCdn = AssetCdn ?? string.Empty;
            _obscured_masterCdn = MasterCdn ?? string.Empty;
            _obscured_webCdn = WebCdn ?? string.Empty;
            _obscured_announcementCdn = AnnouncementCdn ?? string.Empty;
            _obscured_bannerCdn = BannerCdn ?? string.Empty;
            _obscured_agreementCdn = AgreementCdn ?? string.Empty;
        }
    }
}
