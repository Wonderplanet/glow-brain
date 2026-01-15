using UnityEngine;

namespace WPFramework.Domain.Models
{
    public record ApplicationSystemInfo(string Identifier, string ProductName, string Version, string UnityVersion, SystemLanguage SystemLanguage, RuntimePlatform RuntimePlatform, string PlatformId, string BuildGuid, string BuildNumber, string PlatformName, string LocalizationLocaleCode, string BillingPlatform, string ApplicationRegionCode, string DeviceUniqueIdentifier)
    {
        public string Identifier { get; } = Identifier;
        public string ProductName { get; } = ProductName;
        public string Version { get; } = Version;
        public string UnityVersion { get; } = UnityVersion;
        public SystemLanguage SystemLanguage { get; } = SystemLanguage;
        public RuntimePlatform RuntimePlatform { get; } = RuntimePlatform;
        public string PlatformId { get; } = PlatformId;
        public string BuildGuid { get; } = BuildGuid;
        public string BuildNumber { get; } = BuildNumber;
        public string PlatformName { get; } = PlatformName;
        public string LocalizationLocaleCode { get; } = LocalizationLocaleCode;
        public string BillingPlatform { get; } = BillingPlatform;
        public string ApplicationRegionCode { get; } = ApplicationRegionCode;
        public string DeviceUniqueIdentifier { get; } = DeviceUniqueIdentifier;
    }
}
