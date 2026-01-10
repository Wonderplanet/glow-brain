using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Text.RegularExpressions;
using Cysharp.Text;
using UnityEditor;
using UnityEngine;
using WPFramework.AssetReleaseKeySystem;

namespace GLOW.Editor.AssetReleaseKeySystem
{
    /// <summary>
    /// AssetBundleフォルダでリリースキーごとにアセットのフォルダを分ける場合、
    /// そのフォルダ構造がルールに沿ってないとAddressableGroupに意図しない形で登録されるので、ルールに沿った構造になってるかチェックする
    ///
    /// item_icon
    ///    ├ item_icon
    ///    ├ item_icon!202501010
    ///    ├ item_icon_tutorial  ← NG
    ///    └ item_icon_001.png   ← NG
    /// </summary>
    public class AssetReleaseKeySystemFolderChecker : EditorWindow
    {
        enum FolderStatus
        {
            Valid,
            Invalid
        }
        
        struct FolderCheckResult
        {
            public string FolderPath;
            public string InvalidItemPath;
            public string ParentFolderName;
            public FolderStatus Status;
            public string Message;
        }

        Vector2 _scrollPosition;
        List<FolderCheckResult> _checkResults = new List<FolderCheckResult>();
        bool _showOnlyProblems = true;
        AssetReleaseKeySystemSettingsScriptableObject _settings;

        [MenuItem("GLOW/Check/AssetReleaseKeySystem Folder Check/Show Window")]
        static void ShowWindow()
        {
            var window = GetWindow<AssetReleaseKeySystemFolderChecker>();
            window.titleContent = new GUIContent("AssetReleaseKeySystem Folder Checker");
            window.Show();
        }
        
        [MenuItem("GLOW/Check/AssetReleaseKeySystem Folder Check/Run Check")]
        public static void CheckAssetReleaseKeySystemFoldersFromMenu()
        {
            var invalidItems = new List<string>();
            var validFolders = new List<string>();
            var warnings = new List<string>();
            var errors = new List<string>();
            var checkedCount = 0;
            
            var settings = AssetReleaseKeySystemSettingsScriptableObject.GetDefaultObject();
            if (settings == null)
            {
                Debug.LogError("AssetReleaseKeySystemSettings.assetが見つかりません");
                return;
            }
            
            foreach (var importAssetInfo in settings.ImportAssetInfos)
            {
                var folderPath = importAssetInfo.Path;
                
                if (!Directory.Exists(folderPath))
                {
                    var warning = ZString.Format("Folder not found: {0}", folderPath);
                    warnings.Add(warning);
                    Debug.LogWarning(warning);
                    continue;
                }
                
                var topLevelItems = Directory.GetFileSystemEntries(folderPath, "*", SearchOption.TopDirectoryOnly);
                checkedCount++;
                
                foreach (var item in topLevelItems)
                {
                    var itemPath = item.Replace("\\", "/");
                    var itemName = Path.GetFileName(itemPath);
                    var parentFolderName = Path.GetFileName(folderPath);
                    
                    // .metaファイルは除外
                    if (itemName.EndsWith(".meta"))
                    {
                        continue;
                    }
                    
                    var isDirectory = Directory.Exists(itemPath);
                    
                    if (isDirectory)
                    {
                        if (!IsValidFolderName(itemName, parentFolderName))
                        {
                            invalidItems.Add(
                                ZString.Format("Invalid folder: {0} (Parent: {1})", itemPath, parentFolderName));
                        }
                    }
                    else
                    {
                        invalidItems.Add(
                            ZString.Format("Invalid file: {0} (Parent: {1})", itemPath, parentFolderName));
                    }
                }
                
                if (invalidItems.Count == 0 || !invalidItems.Any(x => x.Contains(folderPath)))
                {
                    validFolders.Add(folderPath);
                }
            }
            
            ShowResultsInLog(checkedCount, validFolders, invalidItems, warnings, errors);
        }
        
        static bool IsValidFolderName(string folderName, string parentFolderName)
        {
            if (folderName == parentFolderName)
            {
                return true;
            }
            
            var pattern = ZString.Format(@"^{0}!\d+$", Regex.Escape(parentFolderName));
            return Regex.IsMatch(folderName, pattern);
        }
        
        static void ShowResultsInLog(
            int checkedCount, 
            List<string> validFolders, 
            List<string> invalidItems, 
            List<string> warnings, 
            List<string> errors)
        {
            using (var sb = ZString.CreateStringBuilder())
            {
                sb.Append(ZString.Format("Checked {0} folders.\n\n", checkedCount));

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

                sb.Append(ZString.Format("Valid folders: {0}\n", validFolders.Count));
                sb.Append(ZString.Format("Invalid items found: {0}\n\n", invalidItems.Count));

                if (invalidItems.Count == 0 && warnings.Count == 0 && errors.Count == 0)
                {
                    sb.Append("All folders are valid!");
                    var message = sb.ToString();
                    Debug.Log(message);
                }
                else
                {
                    if (invalidItems.Count > 0)
                    {
                        sb.Append(ZString.Format("Invalid items ({0}):\n", invalidItems.Count));
                        sb.AppendJoin("\n", invalidItems);
                    }
                    
                    var message = sb.ToString();
                    if (errors.Count > 0 || invalidItems.Count > 0) 
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
            EditorGUILayout.LabelField("AssetReleaseKeySystem Folder Checker", EditorStyles.boldLabel);
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
                    ? _checkResults.Where(r => r.Status != FolderStatus.Valid).ToList()
                    : _checkResults;
                    
                EditorGUILayout.LabelField(ZString.Format("チェック結果: {0} 件", filteredResults.Count));
                
                var validCount = _checkResults.Count(r => r.Status == FolderStatus.Valid);
                var invalidCount = _checkResults.Count(r => r.Status == FolderStatus.Invalid);
                
                EditorGUILayout.BeginHorizontal();
                GUI.color = Color.green;
                EditorGUILayout.LabelField(ZString.Format("正常: {0}", validCount), GUILayout.Width(100));
                GUI.color = Color.red;
                EditorGUILayout.LabelField(ZString.Format("不正: {0}", invalidCount), GUILayout.Width(100));
                GUI.color = Color.white;
                EditorGUILayout.EndHorizontal();
                
                EditorGUILayout.Space();
                
                _scrollPosition = EditorGUILayout.BeginScrollView(_scrollPosition);
                
                foreach (var result in filteredResults)
                {
                    EditorGUILayout.BeginVertical(GUI.skin.box);
                    
                    EditorGUILayout.BeginHorizontal();
                    EditorGUILayout.LabelField("Folder:", GUILayout.Width(50));
                    if (GUILayout.Button(Path.GetFileName(result.FolderPath), EditorStyles.linkLabel))
                    {
                        var folderObject = AssetDatabase.LoadAssetAtPath<Object>(result.FolderPath);
                        if (folderObject != null)
                        {
                            EditorGUIUtility.PingObject(folderObject);
                            Selection.activeObject = folderObject;
                        }
                    }
                    EditorGUILayout.EndHorizontal();
                    
                    EditorGUILayout.BeginHorizontal();
                    EditorGUILayout.LabelField("Status:", GUILayout.Width(50));
                    
                    switch (result.Status)
                    {
                        case FolderStatus.Valid:
                            GUI.color = Color.green;
                            EditorGUILayout.LabelField("正常", EditorStyles.wordWrappedLabel);
                            break;
                        case FolderStatus.Invalid:
                            GUI.color = Color.red;
                            EditorGUILayout.LabelField(result.Message, EditorStyles.wordWrappedLabel);
                            break;
                    }
                    GUI.color = Color.white;
                    EditorGUILayout.EndHorizontal();
                    
                    if (result.Status == FolderStatus.Invalid && !string.IsNullOrEmpty(result.InvalidItemPath))
                    {
                        EditorGUILayout.BeginHorizontal();
                        EditorGUILayout.LabelField("Invalid Item:", GUILayout.Width(80));
                        EditorGUILayout.SelectableLabel(
                            result.InvalidItemPath, 
                            EditorStyles.textField, 
                            GUILayout.Height(EditorGUIUtility.singleLineHeight));
                        EditorGUILayout.EndHorizontal();
                    }
                    
                    EditorGUILayout.BeginHorizontal();
                    EditorGUILayout.LabelField("Path:", GUILayout.Width(50));
                    EditorGUILayout.SelectableLabel(
                        result.FolderPath, 
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
            
            _settings = AssetReleaseKeySystemSettingsScriptableObject.GetDefaultObject();
            if (_settings == null)
            {
                Debug.LogError("AssetReleaseKeySystemSettings.assetが見つかりません");
                return;
            }
            
            foreach (var importAssetInfo in _settings.ImportAssetInfos)
            {
                var folderPath = importAssetInfo.Path;
                
                if (!Directory.Exists(folderPath))
                {
                    continue;
                }
                
                var result = new FolderCheckResult
                {
                    FolderPath = folderPath,
                    ParentFolderName = Path.GetFileName(folderPath),
                    Status = FolderStatus.Valid
                };
                
                try
                {
                    var topLevelItems = Directory.GetFileSystemEntries(folderPath, "*", SearchOption.TopDirectoryOnly);
                    
                    foreach (var item in topLevelItems)
                    {
                        var itemPath = item.Replace("\\", "/");
                        var itemName = Path.GetFileName(itemPath);
                        var parentFolderName = Path.GetFileName(folderPath);
                        
                        // .metaファイルは除外
                        if (itemName.EndsWith(".meta"))
                        {
                            continue;
                        }
                        
                        var isDirectory = Directory.Exists(itemPath);
                        
                        if (isDirectory)
                        {
                            if (!IsValidFolderName(itemName, parentFolderName))
                            {
                                result.Status = FolderStatus.Invalid;
                                result.InvalidItemPath = itemPath;
                                result.Message = ZString.Format("不正なフォルダ: {0}", itemName);
                                break;
                            }
                        }
                        else
                        {
                            result.Status = FolderStatus.Invalid;
                            result.InvalidItemPath = itemPath;
                            result.Message = ZString.Format("不正なファイル: {0}", itemName);
                            break;
                        }
                    }
                }
                catch (System.Exception ex)
                {
                    result.Status = FolderStatus.Invalid;
                    result.Message = ZString.Format("チェック失敗: {0}", ex.Message);
                }
                
                _checkResults.Add(result);
            }
            
            var validCount = _checkResults.Count(r => r.Status == FolderStatus.Valid);
            var invalidCount = _checkResults.Count(r => r.Status == FolderStatus.Invalid);
            
            Debug.Log(ZString.Format(
                "チェック完了: {0} 件のフォルダをチェックしました。正常: {1}, 不正: {2}",
                _checkResults.Count, 
                validCount, 
                invalidCount));
        }
    }
}