using System.Collections.Generic;
using System.IO;
using System.Linq;
using Cysharp.Text;
using UnityEditor;
using UnityEngine;

namespace GLOW.Editor.UnitChecker
{
    public class RenderExistingUnitMeshChecker : EditorWindow
    {
        const string RenderExistingUnitMeshGuid = "961b01cf0ad949229db7dccc3620850d";
        const string RenderExistingMeshGuid = "6f4dc11f3bb554f8ea05d2dccbe104a7";
        static readonly string[] PrefabFolders = 
        {
            "Assets/GLOW/AssetBundles/unit_sd_prefab",
            "Assets/GLOW/AssetBundles/unit_sd_prefab_tutorial",
            "Assets/GLOW/AssetBundles/outpost_prefab"
        };
        
        static readonly HashSet<string> ExcludedPrefabs = new HashSet<string>
        {
            "Assets/GLOW/AssetBundles/outpost_prefab/outpost_prefab/outpost_enemy_gatemark.prefab",
            "Assets/GLOW/AssetBundles/outpost_prefab/outpost_prefab/outpost_pvpplayer_default.prefab",
            "Assets/GLOW/AssetBundles/outpost_prefab/outpost_prefab/outpost_sd_shadow.prefab",
            "Assets/GLOW/AssetBundles/outpost_prefab/outpost_prefab/outpost_player_gatemark.prefab"
        };
        
        Vector2 _scrollPosition;
        List<PrefabCheckResult> _checkResults = new List<PrefabCheckResult>();
        bool _showOnlyProblems = true;
        
        public enum ComponentStatus
        {
            Correct,
            Missing,
            OldComponent
        }
        
        struct PrefabCheckResult
        {
            public string PrefabPath;
            public string PrefabName;
            public ComponentStatus Status;
            public bool IsWarning;
            public bool IsError;
            public string Message;
        }

        [MenuItem("GLOW/Check/Unit RenderExistingUnitMesh Attachment/Show Window")]
        static void ShowWindow()
        {
            var window = GetWindow<RenderExistingUnitMeshChecker>();
            window.titleContent = new GUIContent("RenderExistingUnitMesh Checker");
            window.Show();
        }
        
        [MenuItem("GLOW/Check/Unit RenderExistingUnitMesh Attachment/Run Check")]
        public static void CheckRenderExistingUnitMeshAttachmentFromMenu()
        {
            var missingRenderExistingUnitMesh = new List<string>();
            var stillUsingOldComponent = new List<string>();
            var correctlyAttached = new List<string>();
            var warnings = new List<string>();
            var errors = new List<string>();
            var checkedCount = 0;

            foreach (var folderPath in PrefabFolders)
            {
                if (!Directory.Exists(folderPath))
                {
                    var warning = ZString.Format("Folder not found: {0}", folderPath);
                    warnings.Add(warning);
                    Debug.LogWarning(warning);
                    continue;
                }

                var prefabFiles = Directory.GetFiles(folderPath, "*.prefab", SearchOption.AllDirectories);

                foreach (var prefabPath in prefabFiles)
                {
                    if (ExcludedPrefabs.Contains(prefabPath))
                    {
                        continue;
                    }
                    
                    checkedCount++;

                    try
                    {
                        var prefabContent = File.ReadAllText(prefabPath);

                        var hasRenderExistingUnitMesh = prefabContent.Contains(RenderExistingUnitMeshGuid);
                        var hasOldRenderExistingMesh = prefabContent.Contains(RenderExistingMeshGuid);

                        if (hasOldRenderExistingMesh)
                        {
                            stillUsingOldComponent.Add(prefabPath);
                        }
                        else if (hasRenderExistingUnitMesh)
                        {
                            correctlyAttached.Add(prefabPath);
                        }
                        else
                        {
                            missingRenderExistingUnitMesh.Add(prefabPath);
                        }
                    }
                    catch (System.Exception ex)
                    {
                        var error = ZString.Format("Failed to check prefab: {0} - {1}", prefabPath, ex.Message);
                        errors.Add(error);
                        Debug.LogError(error);
                    }
                }
            }

            ShowResultsInLog(
                checkedCount, 
                correctlyAttached, 
                missingRenderExistingUnitMesh, 
                stillUsingOldComponent, 
                warnings, 
                errors);
        }

        static void ShowResultsInLog(
            int checkedCount, 
            List<string> correctlyAttached, 
            List<string> missingRenderExistingUnitMesh, 
            List<string> stillUsingOldComponent,
            List<string> warnings, 
            List<string> errors)
        {
            using (var sb = ZString.CreateStringBuilder())
            {
                sb.Append(ZString.Format("Checked {0} unit prefabs.\n\n", checkedCount));

                if (warnings.Count > 0)
                {
                    sb.Append(ZString.Format("Warnings ({0}):\n", warnings.Count));
                    sb.AppendJoin("\n", warnings);
                    sb.Append("\n\n");
                }

                if (errors.Count > 0)
                {
                    sb.Append(ZString.Format("Errors ({0}):\n", errors.Count));
                    sb.AppendJoin("\n", errors);
                    sb.Append("\n\n");
                }

                sb.Append(ZString.Format("Correctly using RenderExistingUnitMesh: {0}\n", correctlyAttached.Count));
                sb.Append(ZString.Format("Missing RenderExistingUnitMesh: {0}\n", missingRenderExistingUnitMesh.Count));
                sb.Append(ZString.Format("Still using old RenderExistingMesh: {0}\n\n", stillUsingOldComponent.Count));

                if (missingRenderExistingUnitMesh.Count == 0 
                    && stillUsingOldComponent.Count == 0 
                    && warnings.Count == 0 && errors.Count == 0)
                {
                    sb.Append("All prefabs are correctly using RenderExistingUnitMesh!");
                    var message = sb.ToString();
                    Debug.Log(message);
                }
                else
                {
                    if (stillUsingOldComponent.Count > 0)
                    {
                        sb.Append(ZString.Format(
                            "Prefabs still using old RenderExistingMesh ({0}):\n", 
                            stillUsingOldComponent.Count));
                        
                        sb.AppendJoin("\n", stillUsingOldComponent);
                        sb.Append("\n\n");
                    }

                    if (missingRenderExistingUnitMesh.Count > 0)
                    {
                        sb.Append(ZString.Format(
                            "Prefabs missing RenderExistingUnitMesh ({0}):\n", 
                            missingRenderExistingUnitMesh.Count));
                        
                        sb.AppendJoin("\n", missingRenderExistingUnitMesh);
                    }
                    
                    var message = sb.ToString();
                    if (errors.Count > 0 || stillUsingOldComponent.Count > 0) 
                    { 
                        Debug.LogError(message); 
                    } 
                    else if (warnings.Count > 0 || missingRenderExistingUnitMesh.Count > 0)
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
            EditorGUILayout.LabelField("RenderExistingUnitMesh Checker", EditorStyles.boldLabel);
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
                    ? _checkResults.Where(r => r.Status != ComponentStatus.Correct || r.IsWarning || r.IsError).ToList()
                    : _checkResults;
                    
                EditorGUILayout.LabelField(ZString.Format("チェック結果: {0} 件", filteredResults.Count));
                
                var correctCount = _checkResults.Count(r => r.Status == ComponentStatus.Correct && !r.IsWarning && !r.IsError);
                var missingCount = _checkResults.Count(r => r.Status == ComponentStatus.Missing);
                var oldComponentCount = _checkResults.Count(r => r.Status == ComponentStatus.OldComponent);
                
                EditorGUILayout.BeginHorizontal();
                GUI.color = Color.green;
                EditorGUILayout.LabelField(ZString.Format("正常: {0}", correctCount), GUILayout.Width(100));
                GUI.color = Color.yellow;
                EditorGUILayout.LabelField(ZString.Format("未実装: {0}", missingCount), GUILayout.Width(100));
                GUI.color = Color.red;
                EditorGUILayout.LabelField(ZString.Format("旧コンポーネント: {0}", oldComponentCount), GUILayout.Width(150));
                GUI.color = Color.white;
                EditorGUILayout.EndHorizontal();
                
                EditorGUILayout.Space();
                
                _scrollPosition = EditorGUILayout.BeginScrollView(_scrollPosition);
                
                foreach (var result in filteredResults)
                {
                    EditorGUILayout.BeginVertical(GUI.skin.box);
                    
                    EditorGUILayout.BeginHorizontal();
                    EditorGUILayout.LabelField("Prefab:", GUILayout.Width(50));
                    if (GUILayout.Button(result.PrefabName, EditorStyles.linkLabel))
                    {
                        var prefab = AssetDatabase.LoadAssetAtPath<GameObject>(result.PrefabPath);
                        if (prefab != null)
                        {
                            EditorGUIUtility.PingObject(prefab);
                            Selection.activeObject = prefab;
                        }
                    }
                    EditorGUILayout.EndHorizontal();
                    
                    EditorGUILayout.BeginHorizontal();
                    EditorGUILayout.LabelField("Status:", GUILayout.Width(50));
                    
                    if (result.IsError)
                    {
                        GUI.color = Color.red;
                        EditorGUILayout.LabelField(result.Message, EditorStyles.wordWrappedLabel);
                    }
                    else if (result.IsWarning)
                    {
                        GUI.color = Color.yellow;
                        EditorGUILayout.LabelField(result.Message, EditorStyles.wordWrappedLabel);
                    }
                    else
                    {
                        switch (result.Status)
                        {
                            case ComponentStatus.Correct:
                                GUI.color = Color.green;
                                EditorGUILayout.LabelField("RenderExistingUnitMesh使用中", EditorStyles.wordWrappedLabel);
                                break;
                            case ComponentStatus.Missing:
                                GUI.color = Color.yellow;
                                EditorGUILayout.LabelField("RenderExistingUnitMesh未実装", EditorStyles.wordWrappedLabel);
                                break;
                            case ComponentStatus.OldComponent:
                                GUI.color = Color.red;
                                EditorGUILayout.LabelField("旧RenderExistingMesh使用中", EditorStyles.wordWrappedLabel);
                                break;
                        }
                    }
                    GUI.color = Color.white;
                    EditorGUILayout.EndHorizontal();
                    
                    EditorGUILayout.BeginHorizontal();
                    EditorGUILayout.LabelField("Path:", GUILayout.Width(50));
                    EditorGUILayout.SelectableLabel(
                        result.PrefabPath, 
                        EditorStyles.textField, 
                        GUILayout.Height(EditorGUIUtility.singleLineHeight));
                    EditorGUILayout.EndHorizontal();
                    
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
        
        void RunCheck()
        {
            _checkResults.Clear();
            
            foreach (var folderPath in PrefabFolders)
            {
                if (!Directory.Exists(folderPath))
                {
                    continue;
                }
                
                var prefabFiles = Directory.GetFiles(folderPath, "*.prefab", SearchOption.AllDirectories);
                
                foreach (var prefabPath in prefabFiles)
                {
                    if (ExcludedPrefabs.Contains(prefabPath))
                    {
                        continue;
                    }
                    
                    var result = new PrefabCheckResult
                    {
                        PrefabPath = prefabPath,
                        PrefabName = Path.GetFileName(prefabPath),
                        Status = ComponentStatus.Missing,
                        IsWarning = false,
                        IsError = false
                    };
                    
                    try
                    {
                        var prefabContent = File.ReadAllText(prefabPath);
                        
                        var hasRenderExistingUnitMesh = prefabContent.Contains(RenderExistingUnitMeshGuid);
                        var hasOldRenderExistingMesh = prefabContent.Contains(RenderExistingMeshGuid);
                        
                        if (hasOldRenderExistingMesh)
                        {
                            result.Status = ComponentStatus.OldComponent;
                        }
                        else if (hasRenderExistingUnitMesh)
                        {
                            result.Status = ComponentStatus.Correct;
                        }
                        else
                        {
                            result.Status = ComponentStatus.Missing;
                        }
                    }
                    catch (System.Exception ex)
                    {
                        result.IsError = true;
                        result.Message = ZString.Format("Failed to check: {0}", ex.Message);
                    }
                    
                    _checkResults.Add(result);
                }
            }
            
            var correctCount = _checkResults.Count(r => r.Status == ComponentStatus.Correct && !r.IsError);
            var missingCount = _checkResults.Count(r => r.Status == ComponentStatus.Missing && !r.IsError);
            var oldCount = _checkResults.Count(r => r.Status == ComponentStatus.OldComponent && !r.IsError);
            var errorCount = _checkResults.Count(r => r.IsError);
            
            Debug.Log(ZString.Format(
                "チェック完了: {0} 件のプレハブをチェックしました。正常: {1}, 未実装: {2}, 旧コンポーネント: {3}, エラー: {4}",
                _checkResults.Count, 
                correctCount, 
                missingCount,
                oldCount, 
                errorCount));
        }
    }
}