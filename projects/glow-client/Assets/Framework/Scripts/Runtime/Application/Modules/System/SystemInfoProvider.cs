using UnityEngine;
using UnityEngine.Scripting;
using WonderPlanet.UnityBuildNumber;
using WonderPlanet.UnityDeviceIdentifier;
using WPFramework.Constants.Platform;
using WPFramework.Domain.Models;
using WPFramework.Domain.Modules;
using WPFramework.Modules.Localization;
using WPFramework.Modules.Region;
#if UNITY_EDITOR
using UnityEditor;
#endif // UNITY_EDITOR

namespace WPFramework.Application.Modules
{
    public sealed class SystemInfoProvider : ISystemInfoProvider
    {
        ILocalizationInformationProvider LocalizationInformationProvider { get; }
        IApplicationRegionProvider ApplicationRegionProvider { get; }

        [Preserve]
        public SystemInfoProvider(
            ILocalizationInformationProvider localizationInformationProvider,
            IApplicationRegionProvider applicationRegionProvider)
        {
            LocalizationInformationProvider = localizationInformationProvider;
            ApplicationRegionProvider = applicationRegionProvider;
        }

        ApplicationSystemInfo ISystemInfoProvider.GetApplicationSystemInfo()
        {
            return new ApplicationSystemInfo(
                Identifier: UnityEngine.Application.identifier,
                ProductName: UnityEngine.Application.productName,
                Version: UnityEngine.Application.version,
                UnityVersion: UnityEngine.Application.unityVersion,
                SystemLanguage: UnityEngine.Application.systemLanguage,
                RuntimePlatform: UnityEngine.Application.platform,
                PlatformId: GetPlatformId(UnityEngine.Application.platform),
                BuildGuid: UnityEngine.Application.buildGUID,
                BuildNumber: BuildNumber.GetBuildNumber(),
                PlatformName: GetPlatformName(UnityEngine.Application.platform),
                LocalizationLocaleCode: LocalizationInformationProvider.LocaleCode,
                BillingPlatform: GetBillingPlatform(UnityEngine.Application.platform),
                ApplicationRegionCode: ApplicationRegionProvider.RegionCode,
                DeviceUniqueIdentifier: DeviceIdentifier.GetUid());
        }

        string GetPlatformId(RuntimePlatform runtimePlatform)
        {
#if UNITY_EDITOR
            var buildTarget = EditorUserBuildSettings.activeBuildTarget;
            return buildTarget switch
            {
                BuildTarget.Android => PlatformId.Android,
                BuildTarget.iOS     => PlatformId.IOS,
                _                               => "unknown"
            };
#else
            return runtimePlatform == RuntimePlatform.Android ? PlatformId.Android : PlatformId.IOS;
#endif // UNITY_EDITOR
        }

        string GetPlatformName(RuntimePlatform runtimePlatform)
        {
            // NOTE: UNITY_EDITORの場合はEditorUserBuildSettings.activeBuildTargetを参照する
#if UNITY_EDITOR
            var buildTarget = EditorUserBuildSettings.activeBuildTarget;
            var platformName = buildTarget.ToString().ToLower();
            return platformName;
#else
            return runtimePlatform switch
            {
                RuntimePlatform.Android       => "android",
                RuntimePlatform.IPhonePlayer  => "ios",
                RuntimePlatform.WindowsPlayer => "windows",
                RuntimePlatform.OSXPlayer     => "macos",
                RuntimePlatform.WebGLPlayer   => "webgl",
                RuntimePlatform.OSXEditor     => "ios",
                RuntimePlatform.WindowsEditor => "android",
                _                             => "unknown"
            };
#endif // UNITY_EDITOR
        }

        string GetBillingPlatform(RuntimePlatform platform)
        {
#if UNITY_EDITOR
            var buildTarget = EditorUserBuildSettings.activeBuildTarget;
            return buildTarget switch
            {
                BuildTarget.Android => "GooglePlay",
                BuildTarget.iOS     => "AppStore",
                _                               => string.Empty
            };
#else
            return platform switch
            {
                RuntimePlatform.Android      => "GooglePlay",
                RuntimePlatform.IPhonePlayer => "AppStore",
                _                            => string.Empty
            };
#endif  // UNITY_EDITOR
        }
    }
}
