using UnityEditor;
using UnityEditor.AddressableAssets;
using WPFramework.Modules.Log;

namespace WPFramework.AssetReleaseKeySystem
{
    public sealed class AssetReleaseKeySystemPostprocessor : AssetPostprocessor
    {
        /// <summary> https://docs.unity3d.com/ja/current/ScriptReference/AssetPostprocessor.OnPostprocessAllAssets.html </summary>
        static void OnPostprocessAllAssets(string[] importedAssets, string[] deletedAssets, string[] movedAssets, string[] movedFromAssetPaths, bool didDomainReload)
        {
            if (AssetReleaseKeySystemConfigs.IsIgnoreAutoAddressableRegister)
            {
                return;
            }

            var addressableAssetSettings = AddressableAssetSettingsDefaultObject.Settings;
            var registerSetting = AssetReleaseKeySystemSettingsScriptableObject.GetDefaultObject();
            var importer = new ReleaseKeyTargetAssetImporter(addressableAssetSettings, registerSetting);
            var deleter = new ReleaseKeyTargetAssetDeleter(addressableAssetSettings, registerSetting);
            if (importer.Import(importedAssets))
            {
                ApplicationLog.Log(nameof(AssetReleaseKeySystemPostprocessor), "Imported assets.");
            }

            if (importer.Import(movedAssets))
            {
                ApplicationLog.Log(nameof(AssetReleaseKeySystemPostprocessor), "Moved assets.");
            }

            if (deleter.Delete(deletedAssets))
            {
                ApplicationLog.Log(nameof(AssetReleaseKeySystemPostprocessor), "Deleted assets.");
            }

            if (deleter.Delete(movedFromAssetPaths))
            {
                ApplicationLog.Log(nameof(AssetReleaseKeySystemPostprocessor), "Moved from assets.");
            }
        }
    }
}
