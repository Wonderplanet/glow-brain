using System.Collections.Generic;
using System.IO;
using System.Linq;
using Cysharp.Text;
using UnityEditor;
using UnityEditor.AddressableAssets;
using UnityEngine;

namespace GLOW.Editor.AddressableTool
{
    public class FastFollowLabelChecker : EditorWindow
    {
        const string FastFollowLabel = "fastfollow";
        const string TargetLoadPathId = "bfcc1a3f92c564008855e43dbec25730";  // RuntimeHostAndPath
        
        public enum LabelStatus
        {
            Correct,
            Missing,
        }
        
        struct GroupCheckResult
        {
            public string GroupName;
            public int PathPairIndex;
            public List<AssetCheckResult> AssetResults;
            public bool HasProblems;
        }
        
        struct AssetCheckResult
        {
            public string AssetPath;
            public string AssetAddress;
            public LabelStatus Status;
            public List<string> Labels;
        }
        
        Vector2 _scrollPosition;
        List<GroupCheckResult> _checkResults = new List<GroupCheckResult>();
        bool _showOnlyProblems = true;
        
        [MenuItem("GLOW/Check/FastFollow Label Check/Run Check")]
        public static void CheckFastFollowLabelFromMenu()
        {
            var settings = AddressableAssetSettingsDefaultObject.Settings;
            if (settings == null)
            {
                Debug.LogError("Addressable Asset Settings not found!");
                return;
            }
            
            var groupsWithTargetIndex = new List<string>();
            var missingFastFollowLabel = new List<string>();
            var correctlyLabeled = new List<string>();
            var warnings = new List<string>();
            var checkedGroupCount = 0;
            var checkedAssetCount = 0;

            foreach (var group in settings.groups)
            {
                if (group == null) continue;
                
                // Schemaファイルからm_SelectedPathPairIndexを確認
                var schemaPath = ZString.Format(
                    "Assets/AddressableAssetsData/AssetGroups/Schemas/{0}_BundledAssetGroupSchema.asset",
                    group.Name);
                
                var (pathPairIndex, loadPathId) = GetSchemaInfo(schemaPath);
                
                if (loadPathId == TargetLoadPathId)
                {
                    checkedGroupCount++;
                    groupsWithTargetIndex.Add(group.Name);
                    
                    foreach (var entry in group.entries)
                    {
                        if (entry == null) continue;
                        checkedAssetCount++;
                        
                        var hasFastFollowLabel = entry.labels.Contains(FastFollowLabel);
                        
                        if (hasFastFollowLabel)
                        {
                            correctlyLabeled.Add(ZString.Format("{0} - {1}", group.Name, entry.address));
                        }
                        else
                        {
                            missingFastFollowLabel.Add(ZString.Format("{0} - {1}", group.Name, entry.address));
                        }
                    }
                }
            }

            ShowResultsInLog(
                checkedGroupCount,
                checkedAssetCount,
                groupsWithTargetIndex,
                correctlyLabeled,
                missingFastFollowLabel,
                warnings);
        }
        
        [MenuItem("GLOW/Check/FastFollow Label Check/Show Window")]
        static void ShowWindow()
        {
            var window = GetWindow<FastFollowLabelChecker>();
            window.titleContent = new GUIContent("FastFollow Label Checker");
            window.Show();
        }
        
        static (int pathPairIndex, string loadPathId) GetSchemaInfo(string schemaPath)
        {
            if (!File.Exists(schemaPath))
            {
                return (-1, null);
            }
            
            try
            {
                var content = File.ReadAllText(schemaPath);
                var lines = content.Split('\n');
                var pathPairIndex = -1;
                string loadPathId = null;
                
                for (int i = 0; i < lines.Length; i++)
                {
                    var line = lines[i];
                    
                    if (line.Contains("m_SelectedPathPairIndex:"))
                    {
                        var parts = line.Split(':');
                        if (parts.Length >= 2)
                        {
                            int.TryParse(parts[1].Trim(), out pathPairIndex);
                        }
                    }
                    else if (line.Contains("m_LoadPath:") && i + 1 < lines.Length)
                    {
                        var nextLine = lines[i + 1];
                        if (nextLine.Contains("m_Id:"))
                        {
                            var parts = nextLine.Split(':');
                            if (parts.Length >= 2)
                            {
                                loadPathId = parts[1].Trim();
                            }
                        }
                    }
                }
                
                return (pathPairIndex, loadPathId);
            }
            catch (System.Exception ex)
            {
                Debug.LogError(ZString.Format("Failed to read schema file: {0} - {1}", schemaPath, ex.Message));
            }
            
            return (-1, null);
        }

        static void ShowResultsInLog(
            int checkedGroupCount,
            int checkedAssetCount,
            List<string> groupsWithTargetIndex,
            List<string> correctlyLabeled,
            List<string> missingFastFollowLabel,
            List<string> warnings)
        {
            using (var sb = ZString.CreateStringBuilder())
            {
                sb.Append(ZString.Format(
                    "Checked {0} groups with LoadPath = RuntimeHostAndPath.\n", 
                    checkedGroupCount));
                sb.Append(ZString.Format("Total assets checked: {0}\n\n", checkedAssetCount));

                if (warnings.Count > 0)
                {
                    sb.Append(ZString.Format("Warnings ({0}):\n", warnings.Count));
                    sb.AppendJoin("\n", warnings);
                    sb.Append("\n\n");
                }
                
                if (groupsWithTargetIndex.Count > 0)
                {
                    sb.Append(ZString.Format(
                        "Groups with LoadPath = RuntimeHostAndPath ({0}):\n", 
                        groupsWithTargetIndex.Count));
                    sb.AppendJoin("\n", groupsWithTargetIndex);
                    sb.Append("\n\n");
                }

                sb.Append(ZString.Format("Assets with '{0}' label: {1}\n", FastFollowLabel, correctlyLabeled.Count));
                sb.Append(ZString.Format("Assets missing '{0}' label: {1}\n\n", FastFollowLabel, missingFastFollowLabel.Count));

                if (missingFastFollowLabel.Count == 0 && warnings.Count == 0)
                {
                    sb.Append(ZString.Format(
                        "All assets in groups with LoadPath = RuntimeHostAndPath have the '{0}' label!", 
                        FastFollowLabel));
                    var message = sb.ToString();
                    Debug.Log(message);
                }
                else
                {
                    if (missingFastFollowLabel.Count > 0)
                    {
                        sb.Append(ZString.Format(
                            "Assets missing '{0}' label ({1}):\n", 
                            FastFollowLabel,
                            missingFastFollowLabel.Count));
                        
                        sb.AppendJoin("\n", missingFastFollowLabel);
                    }
                    
                    var message = sb.ToString();
                    if (missingFastFollowLabel.Count > 0) 
                    { 
                        Debug.LogError(message); 
                    } 
                    else if (warnings.Count > 0)
                    { 
                        Debug.LogWarning(message); 
                    }
                    else
                    {
                        Debug.Log(message);
                    }
                }
            }
        }
        
        void OnGUI()
        {
            EditorGUILayout.LabelField("FastFollow Label Checker", EditorStyles.boldLabel);
            EditorGUILayout.Space();
            
            EditorGUILayout.HelpBox(
                ZString.Format(
                    "リモートのGroupのアセットに '{0}' ラベルがあるかチェックします。", 
                    FastFollowLabel), 
                MessageType.Info);
            EditorGUILayout.Space();
            
            _showOnlyProblems = EditorGUILayout.Toggle("問題があるもののみ表示", _showOnlyProblems);
            
            if (GUILayout.Button("チェックを実行"))
            {
                RunCheck();
            }
            
            EditorGUILayout.Space();
            
            if (_checkResults.Count > 0)
            {
                var filteredResults = _showOnlyProblems
                    ? _checkResults.Where(r => r.HasProblems).ToList()
                    : _checkResults;
                    
                EditorGUILayout.LabelField(ZString.Format("チェック結果: {0} グループ", filteredResults.Count));
                
                var totalAssets = _checkResults.Sum(r => r.AssetResults.Count);
                var correctCount = _checkResults.Sum(r => r.AssetResults.Count(a => a.Status == LabelStatus.Correct));
                var missingCount = _checkResults.Sum(r => r.AssetResults.Count(a => a.Status == LabelStatus.Missing));
                
                EditorGUILayout.BeginHorizontal();
                GUI.color = Color.green;
                EditorGUILayout.LabelField(ZString.Format("正常: {0}", correctCount), GUILayout.Width(100));
                GUI.color = Color.red;
                EditorGUILayout.LabelField(ZString.Format("ラベル不足: {0}", missingCount), GUILayout.Width(120));
                GUI.color = Color.white;
                EditorGUILayout.LabelField(ZString.Format("合計: {0}", totalAssets), GUILayout.Width(100));
                EditorGUILayout.EndHorizontal();
                
                EditorGUILayout.Space();
                
                _scrollPosition = EditorGUILayout.BeginScrollView(_scrollPosition);
                
                foreach (var groupResult in filteredResults)
                {
                    EditorGUILayout.BeginVertical(GUI.skin.box);
                    
                    EditorGUILayout.BeginHorizontal();
                    EditorGUILayout.LabelField("Group:", GUILayout.Width(50));
                    EditorGUILayout.LabelField(groupResult.GroupName, EditorStyles.boldLabel);
                    EditorGUILayout.LabelField(
                        ZString.Format("(m_SelectedPathPairIndex: {0})", groupResult.PathPairIndex), 
                        GUILayout.Width(200));
                    EditorGUILayout.EndHorizontal();
                    
                    var groupMissingCount = groupResult.AssetResults.Count(a => a.Status == LabelStatus.Missing);
                    if (groupMissingCount > 0)
                    {
                        GUI.color = Color.red;
                        EditorGUILayout.LabelField(
                            ZString.Format("{0} assets missing '{1}' label", groupMissingCount, FastFollowLabel));
                        GUI.color = Color.white;
                    }
                    
                    if (_showOnlyProblems)
                    {
                        var problemAssets = groupResult.AssetResults.Where(a => a.Status == LabelStatus.Missing).ToList();
                        foreach (var asset in problemAssets)
                        {
                            ShowAssetResult(asset);
                        }
                    }
                    else
                    {
                        foreach (var asset in groupResult.AssetResults)
                        {
                            ShowAssetResult(asset);
                        }
                    }
                    
                    EditorGUILayout.EndVertical();
                    EditorGUILayout.Space();
                }
                
                EditorGUILayout.EndScrollView();
            }
            else
            {
                EditorGUILayout.LabelField("チェックを実行してください");
            }
        }
        
        void ShowAssetResult(AssetCheckResult asset)
        {
            EditorGUILayout.BeginHorizontal();
            EditorGUILayout.Space(20, false);
            
            if (asset.Status == LabelStatus.Missing)
            {
                GUI.color = Color.red;
                EditorGUILayout.LabelField("✗", GUILayout.Width(20));
            }
            else
            {
                GUI.color = Color.green;
                EditorGUILayout.LabelField("✓", GUILayout.Width(20));
            }
            
            GUI.color = Color.white;
            
            if (GUILayout.Button(asset.AssetAddress, EditorStyles.linkLabel))
            {
                var assetObj = AssetDatabase.LoadAssetAtPath<Object>(asset.AssetPath);
                if (assetObj != null)
                {
                    EditorGUIUtility.PingObject(assetObj);
                    Selection.activeObject = assetObj;
                }
            }
            
            if (asset.Labels != null && asset.Labels.Count > 0)
            {
                EditorGUILayout.LabelField(
                    ZString.Format("[{0}]", string.Join(", ", asset.Labels)), 
                    GUILayout.Width(200));
            }
            
            EditorGUILayout.EndHorizontal();
        }
        
        void RunCheck()
        {
            _checkResults.Clear();
            
            var settings = AddressableAssetSettingsDefaultObject.Settings;
            if (settings == null)
            {
                Debug.LogError("Addressable Asset Settings not found!");
                return;
            }
            
            foreach (var group in settings.groups)
            {
                if (group == null) continue;
                
                var schemaPath = ZString.Format(
                    "Assets/AddressableAssetsData/AssetGroups/Schemas/{0}_BundledAssetGroupSchema.asset",
                    group.Name);
                
                var (pathPairIndex, loadPathId) = GetSchemaInfo(schemaPath);
                
                if (loadPathId == TargetLoadPathId)
                {
                    var assetResults = new List<AssetCheckResult>();
                    var hasProblems = false;
                    
                    foreach (var entry in group.entries)
                    {
                        if (entry == null) continue;
                        
                        var assetPath = AssetDatabase.GUIDToAssetPath(entry.guid);
                        var hasFastFollowLabel = entry.labels.Contains(FastFollowLabel);
                        
                        var result = new AssetCheckResult
                        {
                            AssetPath = assetPath,
                            AssetAddress = entry.address,
                            Status = hasFastFollowLabel ? LabelStatus.Correct : LabelStatus.Missing,
                            Labels = entry.labels.ToList()
                        };
                        
                        if (result.Status == LabelStatus.Missing)
                        {
                            hasProblems = true;
                        }
                        
                        assetResults.Add(result);
                    }
                    
                    _checkResults.Add(new GroupCheckResult
                    {
                        GroupName = group.Name,
                        PathPairIndex = pathPairIndex,
                        AssetResults = assetResults,
                        HasProblems = hasProblems
                    });
                }
            }
            
            var totalGroups = _checkResults.Count;
            var totalAssets = _checkResults.Sum(r => r.AssetResults.Count);
            var missingCount = _checkResults.Sum(r => r.AssetResults.Count(a => a.Status == LabelStatus.Missing));
            
            Debug.Log(ZString.Format(
                "チェック完了: {0} グループ, {1} アセットをチェックしました。ラベル不足: {2}",
                totalGroups, 
                totalAssets,
                missingCount));
        }
    }
}