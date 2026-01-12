using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Modules.Advertising.AppIdResolver
{
    public record GlowRewardAppId(ObscuredString AppId)
    {
        public string GetAppId()
        {
#if UNITY_IOS || UNITY_ANDROID
            return AppId;
#else
            return string.Empty;
#endif
        }
    };
}
