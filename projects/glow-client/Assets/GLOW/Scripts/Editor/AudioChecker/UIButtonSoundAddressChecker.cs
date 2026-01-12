using System.Collections.Generic;
using System.IO;
using System.Linq;
using Cysharp.Text;
using UnityEditor;
using UnityEditor.AddressableAssets;
using UnityEngine;
using WPFramework.Presentation.Components;

namespace GLOW.Editor.AudioChecker
{
    public class UIButtonSoundAddressChecker : EditorWindow
    {
        Vector2 _scrollPosition;
        List<PrefabCheckResult> _checkResults = new List<PrefabCheckResult>();
        bool _showOnlyProblems = true;
        HashSet<string> _addressableAddresses;
        
        struct PrefabCheckResult
        {
            public string PrefabPath;
            public string PrefabName;
            public List<ComponentCheckResult> ComponentResults;
            public bool HasProblems;
        }
        
        struct ComponentCheckResult
        {
            public string GameObjectPath;
            public string SoundIdentifier;
            public bool IsValid;
            public string Message;
        }

        [MenuItem("GLOW/Check/UIButtonSound Address Checker/Show Window")]
        static void ShowWindow()
        {
            var window = GetWindow<UIButtonSoundAddressChecker>();
            window.titleContent = new GUIContent("UIButtonSound Address Checker");
            window.Show();
        }
        
        [MenuItem("GLOW/Check/UIButtonSound Address Checker/Run Check")]
        public static void CheckUIButtonSoundAddressesFromMenu()
        {
            var instance = CreateInstance<UIButtonSoundAddressChecker>();
            instance.LoadAddressableAddresses();
            instance.RunCheckAndShowResults();
        }

        void OnGUI()
        {
            EditorGUILayout.LabelField("UIButtonSound Address Checker", EditorStyles.boldLabel);
            EditorGUILayout.Space();
            
            _showOnlyProblems = EditorGUILayout.Toggle("問題があるもののみ表示", _showOnlyProblems);
            
            if (GUILayout.Button("チェックを実行"))
            {
                LoadAddressableAddresses();
                RunCheck();
            }
            
            EditorGUILayout.Space();
            
            if (_checkResults.Count > 0)
            {
                var filteredResults = _showOnlyProblems
                    ? _checkResults.Where(r => r.HasProblems).ToList()
                    : _checkResults;
                    
                EditorGUILayout.LabelField(ZString.Format("チェック結果: {0} 件", filteredResults.Count));
                
                var totalComponents = _checkResults.Sum(r => r.ComponentResults.Count);
                var invalidComponents = _checkResults.Sum(r => r.ComponentResults.Count(c => !c.IsValid));
                
                EditorGUILayout.BeginHorizontal();
                GUI.color = Color.green;
                EditorGUILayout.LabelField(ZString.Format("正常: {0}", totalComponents - invalidComponents), GUILayout.Width(100));
                GUI.color = Color.red;
                EditorGUILayout.LabelField(ZString.Format("無効: {0}", invalidComponents), GUILayout.Width(100));
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
                    
                    foreach (var componentResult in result.ComponentResults)
                    {
                        if (!_showOnlyProblems || !componentResult.IsValid)
                        {
                            EditorGUILayout.BeginHorizontal();
                            EditorGUILayout.LabelField("  GameObject:", GUILayout.Width(100));
                            EditorGUILayout.LabelField(componentResult.GameObjectPath);
                            EditorGUILayout.EndHorizontal();
                            
                            EditorGUILayout.BeginHorizontal();
                            EditorGUILayout.LabelField("  Sound ID:", GUILayout.Width(100));
                            GUI.color = componentResult.IsValid ? Color.green : Color.red;
                            EditorGUILayout.LabelField(
                                string.IsNullOrEmpty(componentResult.SoundIdentifier) 
                                    ? "(empty)" 
                                    : componentResult.SoundIdentifier);
                            GUI.color = Color.white;
                            EditorGUILayout.EndHorizontal();
                            
                            if (!componentResult.IsValid)
                            {
                                EditorGUILayout.BeginHorizontal();
                                EditorGUILayout.LabelField("  Status:", GUILayout.Width(100));
                                GUI.color = Color.red;
                                EditorGUILayout.LabelField(componentResult.Message);
                                GUI.color = Color.white;
                                EditorGUILayout.EndHorizontal();
                            }
                            
                            EditorGUILayout.Space(5);
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
        
        void LoadAddressableAddresses()
        {
            _addressableAddresses = new HashSet<string>();
            
            var settings = AddressableAssetSettingsDefaultObject.Settings;
            if (settings == null)
            {
                Debug.LogError("Addressable Asset Settings not found!");
                return;
            }
            
            foreach (var group in settings.groups)
            {
                if (group == null) continue;
                
                foreach (var entry in group.entries)
                {
                    if (entry != null && !string.IsNullOrEmpty(entry.address))
                    {
                        _addressableAddresses.Add(entry.address);
                    }
                }
            }
            
            Debug.Log(ZString.Format("Loaded {0} Addressable addresses", _addressableAddresses.Count));
        }
        
        void RunCheck()
        {
            _checkResults.Clear();
            
            var prefabGuids = AssetDatabase.FindAssets("t:Prefab", new[] { "Assets" });
            var checkedCount = 0;
            var totalComponentCount = 0;
            var invalidComponentCount = 0;
            
            foreach (var guid in prefabGuids)
            {
                var prefabPath = AssetDatabase.GUIDToAssetPath(guid);
                var prefab = AssetDatabase.LoadAssetAtPath<GameObject>(prefabPath);
                
                if (prefab == null) continue;
                
                var uiButtonSounds = prefab.GetComponentsInChildren<UIButtonSound>(true);
                
                if (uiButtonSounds.Length == 0) continue;
                
                checkedCount++;
                var componentResults = new List<ComponentCheckResult>();
                
                foreach (var uiButtonSound in uiButtonSounds)
                {
                    totalComponentCount++;
                    var soundIdentifier = GetSoundIdentifier(uiButtonSound);
                    var addressToCheck = string.IsNullOrEmpty(soundIdentifier) 
                        ? string.Empty 
                        : ZString.Format("audio_se_{0}", soundIdentifier);
                    var isValid = string.IsNullOrEmpty(soundIdentifier) || 
                                  _addressableAddresses.Contains(addressToCheck);
                    
                    if (!isValid) invalidComponentCount++;
                    
                    componentResults.Add(new ComponentCheckResult
                    {
                        GameObjectPath = GetGameObjectPath(uiButtonSound.transform),
                        SoundIdentifier = soundIdentifier,
                        IsValid = isValid,
                        Message = isValid 
                            ? "Valid" 
                            : string.IsNullOrEmpty(soundIdentifier)
                                ? "Empty (Warning)"
                                : ZString.Format("Address 'audio_se_{0}' not found in Addressables", soundIdentifier)
                    });
                }
                
                _checkResults.Add(new PrefabCheckResult
                {
                    PrefabPath = prefabPath,
                    PrefabName = Path.GetFileName(prefabPath),
                    ComponentResults = componentResults,
                    HasProblems = componentResults.Any(c => !c.IsValid)
                });
            }
            
            Debug.Log(ZString.Format(
                "チェック完了: {0} 件のプレハブ、{1} 個のUIButtonSoundをチェックしました。無効: {2}",
                checkedCount,
                totalComponentCount,
                invalidComponentCount));
        }
        
        void RunCheckAndShowResults()
        {
            _checkResults.Clear();
            
            var prefabGuids = AssetDatabase.FindAssets("t:Prefab", new[] { "Assets" });
            var invalidResults = new List<string>();
            var warningResults = new List<string>();
            
            foreach (var guid in prefabGuids)
            {
                var prefabPath = AssetDatabase.GUIDToAssetPath(guid);
                var prefab = AssetDatabase.LoadAssetAtPath<GameObject>(prefabPath);
                
                if (prefab == null) continue;
                
                var uiButtonSounds = prefab.GetComponentsInChildren<UIButtonSound>(true);
                
                foreach (var uiButtonSound in uiButtonSounds)
                {
                    var soundIdentifier = GetSoundIdentifier(uiButtonSound);
                    
                    if (string.IsNullOrEmpty(soundIdentifier))
                    {
                        warningResults.Add(ZString.Format(
                            "{0} - {1}: Empty sound identifier",
                            prefabPath,
                            GetGameObjectPath(uiButtonSound.transform)));
                    }
                    else
                    {
                        var addressToCheck = ZString.Format("audio_se_{0}", soundIdentifier);
                        if (!_addressableAddresses.Contains(addressToCheck))
                        {
                            invalidResults.Add(ZString.Format(
                                "{0} - {1}: '{2}' (checked as '{3}') not found in Addressables",
                                prefabPath,
                                GetGameObjectPath(uiButtonSound.transform),
                                soundIdentifier,
                                addressToCheck));
                        }
                    }
                }
            }
            
            ShowResultsInLog(invalidResults, warningResults);
        }
        
        static void ShowResultsInLog(List<string> invalidResults, List<string> warningResults)
        {
            using (var sb = ZString.CreateStringBuilder())
            {
                sb.Append("UIButtonSound Address Check Results:\n\n");
                
                if (invalidResults.Count == 0 && warningResults.Count == 0)
                {
                    sb.Append("All UIButtonSound components have valid addresses!");
                    Debug.Log(sb.ToString());
                }
                else
                {
                    if (invalidResults.Count > 0)
                    {
                        sb.Append(ZString.Format("Invalid addresses ({0}):\n", invalidResults.Count));
                        sb.AppendJoin("\n", invalidResults);
                        sb.Append("\n\n");
                    }
                    
                    if (warningResults.Count > 0)
                    {
                        sb.Append(ZString.Format("Warnings ({0}):\n", warningResults.Count));
                        sb.AppendJoin("\n", warningResults);
                    }
                    
                    if (invalidResults.Count > 0)
                    {
                        Debug.LogError(sb.ToString());
                    }
                    else
                    {
                        Debug.LogWarning(sb.ToString());
                    }
                }
            }
        }
        
        static string GetSoundIdentifier(UIButtonSound uiButtonSound)
        {
            var serializedObject = new SerializedObject(uiButtonSound);
            var soundIdentifierProperty = serializedObject.FindProperty("_soundIdentifier");
            return soundIdentifierProperty?.stringValue ?? string.Empty;
        }
        
        static string GetGameObjectPath(Transform transform)
        {
            var path = transform.name;
            var parent = transform.parent;
            
            while (parent != null)
            {
                path = parent.name + "/" + path;
                parent = parent.parent;
            }
            
            return path;
        }
    }
}