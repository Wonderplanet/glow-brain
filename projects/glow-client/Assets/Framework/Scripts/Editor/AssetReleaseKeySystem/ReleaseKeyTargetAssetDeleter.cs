using System.IO;
using System.Linq;
using UnityEditor;
using UnityEditor.AddressableAssets.Settings;
using WPFramework.Modules.Log;

namespace WPFramework.AssetReleaseKeySystem
{
    public sealed class ReleaseKeyTargetAssetDeleter
    {
        readonly AddressableAssetSettings _addressableAssetSettings;
        readonly AssetReleaseKeySystemSettingsScriptableObject _releaseKeySystemSettings;

        public ReleaseKeyTargetAssetDeleter(AddressableAssetSettings addressableAssetSettings, AssetReleaseKeySystemSettingsScriptableObject releaseKeySystemSettings)
        {
            _addressableAssetSettings = addressableAssetSettings;
            _releaseKeySystemSettings = releaseKeySystemSettings;
        }

        public bool Delete(string[] deletedAssets)
        {
            if (!_releaseKeySystemSettings)
            {
                return false;
            }

            if (_releaseKeySystemSettings.ImportAssetInfos.Length == 0)
            {
                return false;
            }

            var isDeleted = false;
            try
            {
                // NOTE: OSによってデミリタが違う場合があるので置き換え
                var assets = deletedAssets
                    .Select(x => x.Replace('\\', '/'))
                    .ToArray();
                foreach (var asset in assets)
                {
                    (var assetPath, var registerAssetInfo) =
                        _releaseKeySystemSettings.FindAssetPathAndImportAssetInfo(asset);

                    // NOTE: 対象のアセット情報が見つからない場合は処理を行わない
                    if (registerAssetInfo == null || string.IsNullOrEmpty(assetPath))
                    {
                        continue;
                    }

                    // NOTE: パスからリリースキーの取得
                    var releaseKey = ReleaseKeyHelper.FindReleaseKeyFromPath(assetPath,
                        _releaseKeySystemSettings.ReleaseKeyExtractPattern);
                    var appendPath = ReleaseKeyHelper.ExtractDateOrBasePath(assetPath,
                        _releaseKeySystemSettings.ReleaseKeyPathExtractPattern);
                    // NOTE: パスには/で始まる情報が含まれていない場合には処理が行われない
                    assetPath = ReleaseKeyHelper.RemoveReleaseKeyDirectoryFromPath(assetPath,
                        _releaseKeySystemSettings.ReleaseKeyNamePattern);

                    // NOTE: グループ名とアセット名をパスから取得する
                    var assetGroupName =
                        ReleaseKeyAssetGroupHelper.GetGroupNameFromPath(assetPath, registerAssetInfo.ConversionWordInfos);
                    if (string.IsNullOrEmpty(assetGroupName))
                    {
                        ApplicationLog.LogWarning(nameof(ReleaseKeyTargetAssetImporter),
                            $"[{assetPath}]からグループ名が取得できませんでした。");
                        continue;
                    }

                    // NOTE: リリースキーが存在する場合はリリースコントロール用のデミリタで区切ったグループ名を指定する
                    assetGroupName = ReleaseKeyAssetGroupHelper.CreateAssetGroupNameFromReleaseKeyIfNeeds(assetGroupName, releaseKey);

                    var assetGroup = _addressableAssetSettings.FindGroup(assetGroupName);
                    if (!assetGroup)
                    {
                        continue;
                    }

                    if (assetGroup.entries.Count > 0)
                    {
                        continue;
                    }

                    _addressableAssetSettings.RemoveGroup(assetGroup);
                    ApplicationLog.Log(nameof(ReleaseKeyTargetAssetDeleter), $"[{assetGroup.Name}]を削除しました。");

                    isDeleted = true;
                }
            }
            finally
            {
                if (isDeleted)
                {
                    AssetDatabase.SaveAssets();
                }
            }

            return isDeleted;
        }
    }
}
