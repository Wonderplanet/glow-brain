namespace GLOW.Core.Domain.Modules.Network
{
    public static class RequestHeader
    {
        public static class Common
        {
            public const string BundleVersion = "Client-Version";
            public const string Platform = "Platform";
            public const string ApplicationLanguage = "LANGUAGE";
            public const string RequestEncrypted = "X-Request-Encrypted";
        }

        public static class Game
        {
            public const string OprHash = "Opr-Hash";
            public const string MstHash = "Mst-Hash";
            public const string MstI18nHash = "Mst-I18n-Hash";
            public const string OprI18nHash = "Opr-I18n-Hash";
            public const string AssetHash = "Asset-Hash";
            public const string AdId = "X-Ad-ID";
            public const string CountryCode = "X-Country-Code";
        }

        public static class Agreement
        {
            public const string Authorization = "Authorization";
        }
    }
}
