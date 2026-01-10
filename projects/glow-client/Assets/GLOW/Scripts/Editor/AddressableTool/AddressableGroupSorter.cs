using System;
using System.Collections.Generic;
using System.Linq;
using UnityEditor;
using UnityEditor.AddressableAssets;
using UnityEditor.AddressableAssets.Settings;
using UnityEditor.AddressableAssets.Settings.GroupSchemas;
using UnityEngine;

namespace GLOW.Editor.AddressableTool
{
    public static class AddressableGroupSorter
    {
        [MenuItem("GLOW/Addressable/Sort Groups (アルファベット順)")]
        public static void SortAddressableGroups()
        {
            var settings = AddressableAssetSettingsDefaultObject.Settings;
            if (settings == null)
            {
                Debug.LogError("Addressable Asset Settings not found!");
                return;
            }

            // SortSettingsを取得または作成
            var sortSettings = AddressableAssetGroupSortSettings.Create();
            
            // グループのリストを取得
            var groups = new List<AddressableAssetGroup>(settings.groups);
                        
            // 名前順でソート
            groups.Sort((a, b) => string.Compare(a.Name, b.Name, StringComparison.Ordinal));
            
            // sortOrderを更新
            sortSettings.sortOrder = groups.Select(g => g.Guid).ToArray();
            
            // 設定を保存
            EditorUtility.SetDirty(sortSettings);
            AssetDatabase.SaveAssets();
            
            Debug.Log($"Addressable Groups sorted alphabetically.");
        }
    }
}