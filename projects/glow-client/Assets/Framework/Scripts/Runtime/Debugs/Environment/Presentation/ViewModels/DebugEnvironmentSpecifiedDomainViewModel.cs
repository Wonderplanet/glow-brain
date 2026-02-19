namespace WPFramework.Debugs.Environment.Presentation.ViewModels
{
    public partial record DebugEnvironmentSpecifiedDomainViewModel
    {
        public string Api { get; set; }
        public string AssetCdn { get; set; }
        public string MasterCdn { get; set; }
        public string WebCdn { get; set; }
        public string AnnouncementCdn { get; set; }
        public string BannerCdn { get; set; }
        public string AgreementCdn { get; set; }

        public DebugEnvironmentSpecifiedDomainViewModel()
        {
        }

        public DebugEnvironmentSpecifiedDomainViewModel(string api, string assetCdn, string masterCdn, string webCdn, string announcementCdn, string bannerCdn, string agreementCdn)
        {
            Api = api;
            AssetCdn = assetCdn;
            MasterCdn = masterCdn;
            WebCdn = webCdn;
            AnnouncementCdn = announcementCdn;
            BannerCdn = bannerCdn;
            AgreementCdn = agreementCdn;
        }
    }
}
