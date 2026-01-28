using System;
using System.Runtime.Serialization;
using Newtonsoft.Json;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace WPFramework.Modules.Environment
{
    [Serializable]
    public partial record EnvironmentData
    {
        [DataMember(Name = "env")][JsonProperty("env")]
        public string Env
        {
            get => _obscured_env;
            set => _obscured_env = value ?? string.Empty;
        }
        [DataMember(Name = "name")][JsonProperty("name")]
        public string Name
        {
            get => _obscured_name;
            set => _obscured_name = value ?? string.Empty;
        }
        [DataMember(Name = "description")][JsonProperty("description")]
        public string Description
        {
            get => _obscured_description;
            set => _obscured_description = value ?? string.Empty;
        }
        [DataMember(Name = "api")][JsonProperty("api")]
        public string Api
        {
            get => _obscured_api;
            set => _obscured_api = value ?? string.Empty;
        }
        [DataMember(Name = "assetCdn")][JsonProperty("assetCdn")]
        public string AssetCdn
        {
            get => _obscured_assetCdn;
            set => _obscured_assetCdn = value ?? string.Empty;
        }
        [DataMember(Name = "masterCdn")][JsonProperty("masterCdn")]
        public string MasterCdn
        {
            get => _obscured_masterCdn;
            set => _obscured_masterCdn = value ?? string.Empty;
        }
        [DataMember(Name = "webCdn")][JsonProperty("webCdn")]
        public string WebCdn
        {
            get => _obscured_webCdn;
            set => _obscured_webCdn = value ?? string.Empty;
        }
        [DataMember(Name = "announcementCdn")][JsonProperty("announcementCdn")]
        public string AnnouncementCdn
        {
            get => _obscured_announcementCdn;
            set => _obscured_announcementCdn = value ?? string.Empty;
        }
        [DataMember(Name = "bannerCdn")][JsonProperty("bannerCdn")]
        public string BannerCdn
        {
            get => _obscured_bannerCdn;
            set => _obscured_bannerCdn = value ?? string.Empty;
        }
        [DataMember(Name = "agreementCdn")][JsonProperty("agreementCdn")]
        public string AgreementCdn
        {
            get => _obscured_agreementCdn;
            set => _obscured_agreementCdn = value ?? string.Empty;
        }


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

        public EnvironmentData(string Env, string Name, string Description, string Api, string AssetCdn, string MasterCdn, string WebCdn, string AnnouncementCdn, string BannerCdn, string AgreementCdn)
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
