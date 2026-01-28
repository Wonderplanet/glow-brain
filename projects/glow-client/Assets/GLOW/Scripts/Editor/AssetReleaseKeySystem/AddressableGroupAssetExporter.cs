using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Text;
using System.Text.RegularExpressions;
using Cysharp.Text;
using UnityEditor;
using UnityEditor.AddressableAssets;
using UnityEditor.AddressableAssets.Settings;
using UnityEngine;

namespace GLOW.Editor.AssetReleaseKeySystem
{
    public sealed class AddressableGroupAssetExporter
    {
        const string ExportFolderName = "AddressableGroupAssets_Export";
        const string ReleaseKeyGroupFileName = "release_key_groups_{0}.csv";
        const string NormalGroupFileName = "release_key_groups_202509010.csv";
        static readonly Regex ReleaseKeyPattern = new Regex(@"!(\d+)$");

        [MenuItem("GLOW/Addressable/Export Addressable Group Assets to CSV")]
        public static void ExportAddressableGroupAssetsToCSV()
        {
            try
            {
                var exporter = new AddressableGroupAssetExporter();
                exporter.Export();
            }
            catch (Exception e)
            {
                Debug.LogError(ZString.Format("CSV出力に失敗しました: {0}", e.Message));
                
                EditorUtility.DisplayDialog("エラー", ZString.Format("CSV出力に失敗しました。\n{0}", e.Message), "OK");
            }
        }

        void Export()
        {
            var settings = AddressableAssetSettingsDefaultObject.Settings;
            if (settings == null)
            {
                throw new InvalidOperationException("AddressableAssetSettingsが見つかりません。");
            }

            var projectPath = Directory.GetParent(Application.dataPath).FullName;
            var exportPath = Path.Combine(projectPath, ExportFolderName);
            
            if (Directory.Exists(exportPath))
            {
                Directory.Delete(exportPath, true);
            }
            Directory.CreateDirectory(exportPath);

            var groups = settings.groups.Where(g => g != null).ToList();
            
            var releaseKeyGroups = new Dictionary<string, List<AddressableAssetGroup>>();
            var normalGroups = new List<AddressableAssetGroup>();
            var allReleaseKeys = new HashSet<string>();
            var allGroupNamesWithoutReleaseKey = new HashSet<string>();

            // すべてのグループからリリースキーとベース名を収集
            foreach (var group in groups)
            {
                var match = ReleaseKeyPattern.Match(group.Name);
                if (match.Success)
                {
                    var releaseKey = match.Groups[1].Value;
                    allReleaseKeys.Add(releaseKey);
                    
                    // リリースキー部分を除いたグループ名を収集
                    var baseGroupName = group.Name.Substring(0, group.Name.Length - match.Value.Length);
                    allGroupNamesWithoutReleaseKey.Add(baseGroupName);
                    
                    if (!releaseKeyGroups.ContainsKey(releaseKey))
                    {
                        releaseKeyGroups[releaseKey] = new List<AddressableAssetGroup>();
                    }
                    releaseKeyGroups[releaseKey].Add(group);
                }
                else
                {
                    normalGroups.Add(group);
                }
            }
            
            // グループが存在しないリリースキーも追加
            foreach (var releaseKey in allReleaseKeys)
            {
                if (!releaseKeyGroups.ContainsKey(releaseKey))
                {
                    releaseKeyGroups[releaseKey] = new List<AddressableAssetGroup>();
                }
            }

            foreach (var kvp in releaseKeyGroups)
            {
                var fileName = ZString.Format(ReleaseKeyGroupFileName, kvp.Key);
                var filePath = Path.Combine(exportPath, fileName);
                ExportReleaseKeyGroupsToCSV(kvp.Value, kvp.Key, allGroupNamesWithoutReleaseKey, filePath);
                
                Debug.Log(ZString.Format("リリースキー {0} のグループをエクスポート: {1}", kvp.Key, fileName));
            }

            if (normalGroups.Any())
            {
                var filePath = Path.Combine(exportPath, NormalGroupFileName);
                ExportGroupsToCSV(normalGroups, filePath);
                
                Debug.Log(ZString.Format("通常グループをエクスポート: {0}", NormalGroupFileName));
            }

            Debug.Log(ZString.Format("CSV出力が完了しました: {0}", exportPath));
            
            EditorUtility.DisplayDialog("完了", ZString.Format("CSV出力が完了しました。\n{0}", exportPath), "OK");

            EditorUtility.RevealInFinder(exportPath);
        }

        void ExportReleaseKeyGroupsToCSV(
            List<AddressableAssetGroup> groups, 
            string releaseKey, 
            HashSet<string> allGroupNamesWithoutReleaseKey,
            string filePath)
        {
            var groupAssetMap = new Dictionary<string, List<string>>();
            var maxAssetCount = 0;

            // 存在するグループのアセットを収集
            var existingGroupNames = new HashSet<string>();
            foreach (var group in groups)
            {
                existingGroupNames.Add(group.Name);
                var assetPaths = new List<string>();
                foreach (var entry in group.entries)
                {
                    if (entry != null)
                    {
                        assetPaths.Add(entry.address);
                    }
                }
                groupAssetMap[group.Name] = assetPaths.OrderBy(p => p).ToList();
                maxAssetCount = Math.Max(maxAssetCount, assetPaths.Count);
            }
            
            // すべてのベースグループ名に対して、該当リリースキーのグループが存在しない場合は「なし」を追加
            foreach (var baseGroupName in allGroupNamesWithoutReleaseKey)
            {
                var expectedGroupName = ZString.Format("{0}!{1}", baseGroupName, releaseKey);
                if (!existingGroupNames.Contains(expectedGroupName))
                {
                    groupAssetMap[expectedGroupName] = new List<string> { "なし" };
                    maxAssetCount = Math.Max(maxAssetCount, 1);
                }
            }
            
            // グループ名でソート
            var sortedGroupNames = groupAssetMap.Keys.OrderBy(k => k).ToList();

            using (var writer = new StreamWriter(filePath, false, Encoding.UTF8))
            {
                var header = string.Join(",", sortedGroupNames.Select(k => EscapeCSV(k)));
                writer.WriteLine(header);

                for (int i = 0; i < maxAssetCount; i++)
                {
                    var row = new List<string>();
                    foreach (var groupName in sortedGroupNames)
                    {
                        var assets = groupAssetMap[groupName];
                        if (i < assets.Count)
                        {
                            row.Add(EscapeCSV(assets[i]));
                        }
                        else
                        {
                            row.Add("");
                        }
                    }
                    writer.WriteLine(string.Join(",", row));
                }
            }
        }

        
        void ExportGroupsToCSV(List<AddressableAssetGroup> groups, string filePath)
        {
            var groupAssetMap = new Dictionary<string, List<string>>();
            var maxAssetCount = 0;

            foreach (var group in groups.OrderBy(g => g.Name))
            {
                var assetPaths = new List<string>();
                foreach (var entry in group.entries)
                {
                    if (entry != null)
                    {
                        assetPaths.Add(entry.address);
                    }
                }
                groupAssetMap[group.Name] = assetPaths.OrderBy(p => p).ToList();
                maxAssetCount = Math.Max(maxAssetCount, assetPaths.Count);
            }

            using (var writer = new StreamWriter(filePath, false, Encoding.UTF8))
            {
                var header = string.Join(",", groupAssetMap.Keys.Select(k => EscapeCSV(k)));
                writer.WriteLine(header);

                for (int i = 0; i < maxAssetCount; i++)
                {
                    var row = new List<string>();
                    foreach (var groupName in groupAssetMap.Keys)
                    {
                        var assets = groupAssetMap[groupName];
                        if (i < assets.Count)
                        {
                            row.Add(EscapeCSV(assets[i]));
                        }
                        else
                        {
                            row.Add("");
                        }
                    }
                    writer.WriteLine(string.Join(",", row));
                }
            }
        }

        string EscapeCSV(string value)
        {
            if (value.Contains(",") || value.Contains("\"") || value.Contains("\n") || value.Contains("\r"))
            {
                return ZString.Format("\"{0}\"", value.Replace("\"", "\"\""));
            }
            return value;
        }
    }
}