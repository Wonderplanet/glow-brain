using System;
using UnityEditor;
using UnityEngine;

namespace WPFramework.AssetReleaseKeySystem
{
    [CreateAssetMenu(fileName = "AssetReleaseKeySystemSettings", menuName = "Addressables/AddressableRegister/設定ファイル", order = 1)]
    public sealed class AssetReleaseKeySystemSettingsScriptableObject : ScriptableObject
    {
        [Header("リリースキーの検知パターン")]
        [SerializeField] string _releaseKeyPattern = @"\d{9}";
        [Header("自動でAddress 登録するディレクトとその情報の指定")]
        [SerializeField] ImportAssetInfo[] _importAssetInfos = Array.Empty<ImportAssetInfo>();

        public string ReleaseKeyNamePattern => $@"\\/[\\w]+!\{_releaseKeyPattern}\\/";
        public string ReleaseKeyPathExtractPattern => $@"(?:\/?)([\w]+!{_releaseKeyPattern})(?:\/)";
        public string ReleaseKeyExtractPattern => $@"!{_releaseKeyPattern}\/";
        public ImportAssetInfo[] ImportAssetInfos => _importAssetInfos;

        public static AssetReleaseKeySystemSettingsScriptableObject GetDefaultObject()
        {
            var guids = AssetDatabase.FindAssets($"t:{nameof(AssetReleaseKeySystemSettingsScriptableObject)}");
            if (guids.Length == 0)
            {
                return null;
            }

            var path = AssetDatabase.GUIDToAssetPath(guids[0]);
            return AssetDatabase.LoadAssetAtPath<AssetReleaseKeySystemSettingsScriptableObject>(path);
        }

        public (string, ImportAssetInfo) FindAssetPathAndImportAssetInfo(string asset)
        {
            // NOTE: 設定情報から対象の設定を特定する
            var assetPath = string.Empty;
            ImportAssetInfo registerAssetInfo = null;
            foreach (var info in ImportAssetInfos)
            {
                if (!info.IsTargetAssetPath(asset))
                {
                    continue;
                }

                // NOTE: 省略対指定されているかをチェックし
                //       対象文字列がパスの中に含まれている場合処理を行わない
                var removePrefixName = string.IsNullOrEmpty(info.RemovePrefixPath) ? info.Path : info.RemovePrefixPath;
                var folderNameIndex = asset.IndexOf(removePrefixName, StringComparison.Ordinal);
                if (folderNameIndex < 0)
                {
                    continue;
                }

                // NOTE: アセットパスを構築
                assetPath = asset.Remove(0, folderNameIndex + removePrefixName.Length + 1);
                registerAssetInfo = info;
                break;
            }

            return (assetPath, registerAssetInfo);
        }
    }
}
