using System.Collections.Generic;
using System.IO;
using System.Linq;
using UnityEditor;
using UnityEditor.AddressableAssets;

namespace WPFramework.AssetReleaseKeySystem
{
    public static class AssetReleaseKeySystemMenu
    {
        const string MenuItemPingAddressableRegisterSettings = "Tools/WonderPlanet/Asset ReleaseKey System/設定ファイルを選択";
        const string MenuItemIgnoreAutoAddressableRegister   = "Tools/WonderPlanet/Asset ReleaseKey System/自動インポート停止";
        const string MenuItemImportTargetAsset               = "Tools/WonderPlanet/Asset ReleaseKey System/再インポート";

        const string AssetsMenuRegisterSelectedAssets        = "Assets/WonderPlanet/Asset ReleaseKey System/選択したアセットを登録";

        [MenuItem(MenuItemPingAddressableRegisterSettings, false, 0)]
        public static void PingAddressableRegisterSettings()
        {
            var guids = AssetDatabase.FindAssets($"t:{nameof(AssetReleaseKeySystemSettingsScriptableObject)}");
            if (guids.Length == 0)
            {
                return;
            }

            var path = AssetDatabase.GUIDToAssetPath(guids[0]);
            var asset = AssetDatabase.LoadAssetAtPath<AssetReleaseKeySystemSettingsScriptableObject>(path);
            if (!asset)
            {
                return;
            }

            EditorGUIUtility.PingObject(asset);
        }

        [MenuItem(MenuItemIgnoreAutoAddressableRegister, false, 1)]
        public static void ToggleIgnoreAutoAddressableRegister()
        {
            AssetReleaseKeySystemConfigs.IsIgnoreAutoAddressableRegister = !AssetReleaseKeySystemConfigs.IsIgnoreAutoAddressableRegister;
        }

        [MenuItem(MenuItemIgnoreAutoAddressableRegister, true)]
        public static bool ValidateToggleIgnoreAutoAddressableRegister()
        {
            Menu.SetChecked(MenuItemIgnoreAutoAddressableRegister, AssetReleaseKeySystemConfigs.IsIgnoreAutoAddressableRegister);
            return true;
        }

        [MenuItem(MenuItemImportTargetAsset, false, 20)]
        public static void ReimportAssetsFromSettings()
        {
            try
            {
                EditorUtility.DisplayProgressBar("Importing", "Importing...", 0.0f);

                var releaseKeySystemSettings = AssetReleaseKeySystemSettingsScriptableObject.GetDefaultObject();
                var targetFiles = new List<string>();
                foreach (var info in releaseKeySystemSettings.ImportAssetInfos)
                {
                    targetFiles.AddRange(GetTargetFiles(info.Path));
                }

                EditorUtility.DisplayProgressBar("Importing", "Importing...", 0.5f);

                // NOTE: この処理はStartEditingをすると失敗するので注意
                var addressableAssetSettings = AddressableAssetSettingsDefaultObject.Settings;
                var importer = new ReleaseKeyTargetAssetImporter(addressableAssetSettings, releaseKeySystemSettings);
                importer.Import(targetFiles.ToArray());

                EditorUtility.DisplayProgressBar("Importing", "Importing...", 1.0f);
            }
            finally
            {
                EditorUtility.ClearProgressBar();
            }
        }

        [MenuItem(MenuItemImportTargetAsset, true)]
        public static bool ValidateReimportAssetsFromSettings()
        {
            var registerSettings = AssetReleaseKeySystemSettingsScriptableObject.GetDefaultObject();
            return registerSettings;
        }

        [MenuItem(AssetsMenuRegisterSelectedAssets, false)]
        public static void RegisterSelectedAssets()
        {
            var releaseKeySystemSettings = AssetReleaseKeySystemSettingsScriptableObject.GetDefaultObject();
            var assets = Selection.GetFiltered(typeof(UnityEngine.Object), SelectionMode.Assets);
            if (assets.Length == 0)
            {
                return;
            }
            var assetPaths = assets.Select(AssetDatabase.GetAssetPath).ToArray();
            var targetFiles = new List<string>();

            foreach (var assetPath in assetPaths)
            {
                targetFiles.AddRange(GetTargetFiles(assetPath));
            }

            // NOTE: この処理はStartEditingをすると失敗するので注意
            var addressableAssetSettings = AddressableAssetSettingsDefaultObject.Settings;
            var importer = new ReleaseKeyTargetAssetImporter(addressableAssetSettings, releaseKeySystemSettings);
            importer.Import(targetFiles.ToArray());
        }

        [MenuItem(AssetsMenuRegisterSelectedAssets, true)]
        public static bool ValidateRegisterSelectedAssets()
        {
            var releaseKeySystemSettings = AssetReleaseKeySystemSettingsScriptableObject.GetDefaultObject();
            return releaseKeySystemSettings;
        }

        static IList<string> GetTargetFiles(string assetPath)
        {
            var targetFiles = new List<string>();

            if (Directory.Exists(assetPath))
            {
                // NOTE: 選択したフォルダから再起的に全てのファイルを取得
                var files = Directory.GetFiles(assetPath, "*", SearchOption.AllDirectories);
                // NOTE: アセット登録に不要なファイルを除外(.meta/.keep/.gitkeep/.DS_Store)
                targetFiles.AddRange(files.Where(file => !file.EndsWith(".meta"))
                    .Where(file => !file.EndsWith(".keep"))
                    .Where(file => !file.EndsWith(".gitkeep"))
                    .Where(file => !file.EndsWith(".DS_Store")));
            }
            else
            {
                targetFiles.Add(assetPath);
            }

            return targetFiles;
        }
    }
}
