using UnityEngine;
using WPFramework.Modules.Log;

namespace WPFramework.Modules.Platform
{
    public sealed class PlatformStoreLinker
    {
        public string IOSId { get; }
        public string AndroidId { get; }

        public PlatformStoreLinker(string iosId, string androidId)
        {
            // NOTE: iOSはストアのURLにあるアプリのIDを指定する必要がある
            //       Androidはストアに登録したpackageNameを指定する必要がある
            IOSId = iosId;
            AndroidId = androidId;
        }

        string CreatePlatformStoreUrl(RuntimePlatform platform)
        {
            var url = string.Empty;
            switch (platform)
            {
                case RuntimePlatform.Android:
                    url = $"market://details?id={AndroidId}";
                    break;
                case RuntimePlatform.IPhonePlayer:
                    url = $"itms-apps://itunes.apple.com/app/id{IOSId}?mt=8";
                    break;
                default:
#if UNITY_ANDROID
                    url = $"https://play.google.com/store/apps/details?id={AndroidId}";
#elif UNITY_IOS
                    url = $"https://apps.apple.com/app/id{IOSId}";
#endif  // UNITY_ANDROID or UNITY_IOS
                    break;
            }

            return url;
        }

        public void OpenURL()
        {
            var url = CreatePlatformStoreUrl(Application.platform);
            if (string.IsNullOrEmpty(url))
            {
                ApplicationLog.LogWarning(nameof(PlatformStoreLinker), "OpenURL Not supported platform");
                return;
            }

            ApplicationLog.Log(nameof(PlatformStoreLinker), $"OpenURL: {url}");
            Application.OpenURL(url);
        }
    }
}
