using UnityEditor;

namespace WPFramework.AssetReleaseKeySystem
{
    public static class AssetReleaseKeySystemConfigs
    {
        public static bool IsIgnoreAutoAddressableRegister
        {
            get => EditorPrefs.GetBool($"{nameof(AssetReleaseKeySystemMenu)}.IsIgnoreAutoAddressableRegister", false);
            set => EditorPrefs.SetBool($"{nameof(AssetReleaseKeySystemMenu)}.IsIgnoreAutoAddressableRegister", value);
        }
    }
}
