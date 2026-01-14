using System;
using System.Globalization;
using System.IO;
using System.Linq;
using UnityEditor;
using UnityEditor.AddressableAssets.Settings;
using UnityEditor.AddressableAssets.Settings.GroupSchemas;
using WonderPlanet.AddressableValidator.GroupSchemas;
using WonderPlanet.ResourceManagement;
using WPFramework.Modules.Log;

namespace WPFramework.AssetReleaseKeySystem
{
    public sealed class ReleaseKeyTargetAssetImporter
    {
        readonly AddressableAssetSettings _addressableAssetSettings;
        readonly AssetReleaseKeySystemSettingsScriptableObject _releaseKeySystemSettings;

        public ReleaseKeyTargetAssetImporter(AddressableAssetSettings addressableAssetSettings, AssetReleaseKeySystemSettingsScriptableObject releaseKeySystemSettings)
        {
            _addressableAssetSettings = addressableAssetSettings;
            _releaseKeySystemSettings = releaseKeySystemSettings;
        }

        public bool Import(string[] importedAssets)
        {
            if (!_releaseKeySystemSettings)
            {
                return false;
            }

            if (_releaseKeySystemSettings.ImportAssetInfos.Length == 0)
            {
                return false;
            }

            var isRegistered = false;

            try
            {
                // NOTE: OSによってデミリタが違う場合があるので置き換え
                var assets = importedAssets
                    .Select(x => x.Replace('\\', '/'))
                    .Where(File.Exists)
                    .ToArray();
                foreach (var asset in assets)
                {
                    (var assetPath, var importAssetInfo) = _releaseKeySystemSettings.FindAssetPathAndImportAssetInfo(asset);

                    // NOTE: 対象のアセット情報が見つからない場合は処理を行わない
                    if (importAssetInfo == null || string.IsNullOrEmpty(assetPath))
                    {
                        continue;
                    }

                    if (!importAssetInfo.GroupTemplate)
                    {
                        ApplicationLog.LogError(nameof(ReleaseKeyTargetAssetImporter), $"[{importAssetInfo.Path}]のグループテンプレートが設定されていません。");
                        continue;
                    }

                    // NOTE: パスからリリースキーの取得
                    var releaseKey = ReleaseKeyHelper.FindReleaseKeyFromPath(assetPath, _releaseKeySystemSettings.ReleaseKeyExtractPattern);
                    var appendPath = ReleaseKeyHelper.ExtractDateOrBasePath(assetPath, _releaseKeySystemSettings.ReleaseKeyPathExtractPattern);
                    // NOTE: パスには/で始まる情報が含まれていない場合には処理が行われない
                    assetPath = ReleaseKeyHelper.RemoveReleaseKeyDirectoryFromPath(assetPath, _releaseKeySystemSettings.ReleaseKeyNamePattern);

                    // NOTE: グループ名とアセット名をパスから取得する
                    var assetGroupName = ReleaseKeyAssetGroupHelper.GetGroupNameFromPath(assetPath, importAssetInfo.ConversionWordInfos);
                    if (string.IsNullOrEmpty(assetGroupName))
                    {
                        ApplicationLog.LogWarning(nameof(ReleaseKeyTargetAssetImporter), $"[{assetPath}]からグループ名が取得できませんでした。");
                        continue;
                    }

                    // NOTE: リリースキーが存在する場合はリリースコントロール用のデミリタで区切ったグループ名を指定する
                    assetGroupName = ReleaseKeyAssetGroupHelper.CreateAssetGroupNameFromReleaseKeyIfNeeds(assetGroupName, releaseKey);

                    var importPath =
                         string.IsNullOrEmpty(appendPath) ? importAssetInfo.Path : $"{importAssetInfo.Path}/{appendPath}";
                    var assetGroup = _addressableAssetSettings.FindGroup(assetGroupName);

                    // NOTE: グループが見つからない場合は作成
                    if (!assetGroup)
                    {
                        assetGroup = ReleaseKeyAssetGroupHelper.CreateAddressableGroup(_addressableAssetSettings, assetGroupName, importAssetInfo.GroupTemplate);
                        ApplicationLog.Log(nameof(ReleaseKeyTargetAssetImporter), $"[{assetGroupName}]を作成しました。");
                    }

                    if (!assetGroup)
                    {
                        ApplicationLog.LogError(nameof(ReleaseKeyTargetAssetImporter), $"[{assetGroupName}]の作成に失敗しました。");
                        continue;
                    }

                    ReleaseKeyAssetGroupHelper.UpdateAddressableGroup(_addressableAssetSettings, assetGroupName, importAssetInfo.GroupTemplate);

                    ApplicationLog.Log(nameof(ReleaseKeyTargetAssetImporter), $"[{asset}]を[{assetGroupName}]に登録します。ImportPath:[{importPath}]");

                    // NOTE: 既にスキーマがついているならばスキーマの設定を操作する
                    if (assetGroup.HasSchema<BundledAssetGroupSchema>())
                    {
                        var bundledAssetGroupSchema = assetGroup.GetSchema<BundledAssetGroupSchema>();
                        bundledAssetGroupSchema.BundleMode = importAssetInfo.BundleMode;
                    }

                    // NOTE: 既にスキーマがついているならばスキーマの設定を操作する
                    if (assetGroup.HasSchema<AutoImportGroupSchema>())
                    {
                        var autoImportGroupSchema = assetGroup.GetSchema<AutoImportGroupSchema>();
                        autoImportGroupSchema.targetPath = importPath;
                        autoImportGroupSchema.directory = importAssetInfo.Directory;
                        autoImportGroupSchema.file = importAssetInfo.File;
                        autoImportGroupSchema.regex = importAssetInfo.Regex;
                        autoImportGroupSchema.namingRule = importAssetInfo.NamingRule;
                        autoImportGroupSchema.addressableNamePrefix = importAssetInfo.AddressableNamePrefix;
                        autoImportGroupSchema.removeExtension = importAssetInfo.RemoveExtension;
                        autoImportGroupSchema.labels = importAssetInfo.Labels.ToList();
                        autoImportGroupSchema.searchOption = importAssetInfo.SearchOption;
                        var processor = new AddressableAssetImportProcessor();
                        processor.Execute(autoImportGroupSchema);
                        EditorUtility.SetDirty(autoImportGroupSchema);
                        AssetDatabase.SaveAssetIfDirty(autoImportGroupSchema);
                    }

                    // NOTE: 既にスキーマがついているならばスキーマの設定を操作する
                    if (assetGroup.HasSchema<ValidateAssetEntryGroupSchema>())
                    {
                        var validateAssetEntryGroupSchema = assetGroup.GetSchema<ValidateAssetEntryGroupSchema>();
                        validateAssetEntryGroupSchema.WhiteEntryPathList = importAssetInfo.WhiteEntryPathList;
                        validateAssetEntryGroupSchema.WhiteDependencyPathList = importAssetInfo.WhiteDependencyPathList;
                        validateAssetEntryGroupSchema.IsValidateDependentOnLocalToRemote = importAssetInfo.IsValidateDependentOnLocalToRemote;
                    }

                    isRegistered = true;
                }
            }
            finally
            {
                if (isRegistered)
                {
                    AssetDatabase.SaveAssets();
                }
            }

            return isRegistered;
        }
    }
}
