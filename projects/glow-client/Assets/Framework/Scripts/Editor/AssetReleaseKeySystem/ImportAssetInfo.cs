using System;
using System.Collections.Generic;
using System.IO;
using UnityEditor.AddressableAssets.Settings;
using UnityEditor.AddressableAssets.Settings.GroupSchemas;
using UnityEngine;
using WonderPlanet.ResourceManagement;

namespace WPFramework.AssetReleaseKeySystem
{
    [Serializable]
    public sealed class ImportAssetInfo
    {
        [Header("インポート対象設定")]

        [SerializeField] string _path;

        [Header("Addressablesグループ指定")]

        [SerializeField] AddressableAssetGroupTemplate _groupTemplate;

        [Header("BundledAssetGroupSchema設定")]

        [SerializeField] BundledAssetGroupSchema.BundlePackingMode bundleMode;

        [Header("AutoImportSchema設定")]

        [SerializeField] SearchOption _searchOption;
        [SerializeField] bool _directory = true;
        [SerializeField] bool _file = true;
        [SerializeField] string _regex;
        [SerializeField] AddressableNamingRule _namingRule;
        [SerializeField] string _addressableNamePrefix;
        [SerializeField] bool _removeExtension;
        [SerializeField] string[] _labels = Array.Empty<string>();

        [Header("ValidateAssetEntryGroupSchema設定")]

        [SerializeField]
        List<string> _whiteEntryPathList = new List<string>();

        [SerializeField]
        List<string> _whiteDependencyPathList = new List<string>();

        [SerializeField]
        bool _isValidateDependentOnLocalToRemote = false;

        [Header("グループ名設定")]

        [SerializeField] NameConversionWordInfo[] _conversionWordInfos = Array.Empty<NameConversionWordInfo>();

        public string Path => _path;
        public string RemovePrefixPath => GetPreviousLevelPath(_path);
        public SearchOption SearchOption => _searchOption;
        public BundledAssetGroupSchema.BundlePackingMode BundleMode => bundleMode;
        public bool Directory => _directory;
        public bool File => _file;
        public string Regex => _regex;
        public AddressableNamingRule NamingRule => _namingRule;
        public string AddressableNamePrefix => _addressableNamePrefix;
        public bool RemoveExtension => _removeExtension;
        public string[] Labels => _labels;
        public NameConversionWordInfo[] ConversionWordInfos => _conversionWordInfos;
        public AddressableAssetGroupTemplate GroupTemplate => _groupTemplate;
        public IReadOnlyCollection<string> WhiteEntryPathList => _whiteEntryPathList;
        public IReadOnlyCollection<string> WhiteDependencyPathList => _whiteDependencyPathList;
        public bool IsValidateDependentOnLocalToRemote => _isValidateDependentOnLocalToRemote;

        public bool IsTargetAssetPath(string asset)
        {
            // NOTE: パス指定がからの場合は処理を行わない
            if (string.IsNullOrEmpty(Path))
            {
                return false;
            }

            // NOTE: インポートされてきた対象が処理対象のパスのものかをチェック
            var path = Path;
            // NOTE: パスの最後にスラッシュがついていない場合は/をつける
            //       このチェックをしない場合はパスの一部が一致してしまうため
            if (!path.EndsWith("/"))
            {
                path += "/";
            }
            if (!asset.StartsWith(path))
            {
                return false;
            }

            // NOTE: 対象になっていない場合は処理を行わない
            var targetRelativePath = System.Text.RegularExpressions.Regex.Replace(asset, $"^{Path}/?", "");
            var match = System.Text.RegularExpressions.Regex.Match(targetRelativePath, Regex);
            return match.Success;
        }

        static string GetPreviousLevelPath(string path)
        {
            // NOTE: 最後のスラッシュの位置を取得
            var lastSlashIndex = path.LastIndexOf('/');

            // NOTE: スラッシュが見つからなかった場合、または一つの階層しかない場合は空文字を返す
            return lastSlashIndex == -1 ? string.Empty :
                // 最後のスラッシュまでの部分文字列を返す
                path.Substring(0, lastSlashIndex);
        }
    }
}
