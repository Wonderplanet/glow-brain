using System.Collections.Generic;
using System.IO;
using System.Linq;
using Cysharp.Text;
using UnityEditor;
using UnityEngine;

namespace GLOW.Editor.UnitChecker
{
    public class SpineMaterialShaderChecker : EditorWindow
    {
        const string CorrectShaderGuid = "1a951750ced14a08a18a8af46d48313e";
        const string MaterialPathPattern = "Assets/GLOW/Graphics/Characters/{0}/Spine/{0}_Material.mat";
        
        Vector2 _scrollPosition;
        List<MaterialCheckResult> _checkResults = new List<MaterialCheckResult>();
        bool _showOnlyIncorrect = true;
        
        struct MaterialCheckResult
        {
            public string MaterialPath;
            public string CharacterName;
            public bool IsCorrect;
            public string CurrentShaderGuid;
            public string Message;
            public bool IsWarning;
            public bool IsError;
        }

        [MenuItem("GLOW/Check/Unit Spine Material Shaders/Show Window")]
        static void ShowWindow()
        {
            var window = GetWindow<SpineMaterialShaderChecker>();
            window.titleContent = new GUIContent("Spine Material Shader Checker");
            window.Show();
        }
        
        [MenuItem("GLOW/Check/Unit Spine Material Shaders/Run Check")]
        public static void CheckUnitSpineMaterialShadersFromMenu()
        {
            var incorrectMaterials = new List<string>();
            var warnings = new List<string>();
            var errors = new List<string>();
            var checkedCount = 0;

            var characterFolders = Directory.GetDirectories("Assets/GLOW/Graphics/Characters");

            foreach (var folder in characterFolders)
            {
                var characterName = Path.GetFileName(folder);
                var spineFolderPath = Path.Combine(folder, "Spine");
                
                if (!Directory.Exists(spineFolderPath))
                    continue;

                var materialPath = ZString.Format(MaterialPathPattern, characterName);

                if (!File.Exists(materialPath))
                {
                    var warning = ZString.Format("Material not found: {0}", materialPath);
                    warnings.Add(warning);
                    Debug.LogWarning(warning);
                    continue;
                }

                var material = AssetDatabase.LoadAssetAtPath<Material>(materialPath);
                if (material == null)
                {
                    var error = ZString.Format("Failed to load material: {0}", materialPath);
                    errors.Add(error);
                    Debug.LogError(error);
                    continue;
                }

                checkedCount++;

                if (material.shader != null)
                {
                    var shaderPath = AssetDatabase.GetAssetPath(material.shader);
                    var shaderGuid = AssetDatabase.AssetPathToGUID(shaderPath);

                    if (shaderGuid != CorrectShaderGuid)
                    {
                        incorrectMaterials.Add(ZString.Format("{0} (Current GUID: {1})", materialPath, shaderGuid));
                    }
                }
                else
                {
                    incorrectMaterials.Add(ZString.Format("{0} (Shader is null)", materialPath));
                }
            }

            ShowResultsInLog(checkedCount, incorrectMaterials, warnings, errors);
        }

        static void ShowResultsInLog(
            int checkedCount, 
            List<string> incorrectMaterials, 
            List<string> warnings, 
            List<string> errors)
        {
            using (var sb = ZString.CreateStringBuilder())
            {
                sb.Append(ZString.Format("Checked {0} Spine materials.\n\n", checkedCount));

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

                if (incorrectMaterials.Count == 0 && warnings.Count == 0 && errors.Count == 0)
                {
                    sb.Append("All materials have the correct shader!");
                    var message = sb.ToString();
                    Debug.Log(message);
                }
                else
                {
                    if (incorrectMaterials.Count > 0)
                    {
                        sb.Append(ZString.Format("Found {0} materials with incorrect shaders:\n", incorrectMaterials.Count));
                        sb.AppendJoin("\n", incorrectMaterials);
                    }
                    
                    var message = sb.ToString();
                    if (errors.Count > 0)
                    {
                        Debug.LogError(message);
                    }
                    else if (warnings.Count > 0 || incorrectMaterials.Count > 0)
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
            EditorGUILayout.LabelField("Spine Material Shader Checker", EditorStyles.boldLabel);
            EditorGUILayout.Space();
            
            _showOnlyIncorrect = EditorGUILayout.Toggle("不正なもののみ表示", _showOnlyIncorrect);
            
            EditorGUILayout.BeginHorizontal();
            if (GUILayout.Button("チェックを実行"))
            {
                RunCheck();
            }
            
            var incorrectResults = _checkResults.Where(r => !r.IsCorrect && !r.IsWarning && !r.IsError).ToList();
            if (incorrectResults.Count > 0)
            {
                GUI.color = Color.yellow;
                if (GUILayout.Button("不正なシェーダーを全て修正"))
                {
                    if (EditorUtility.DisplayDialog("確認",
                        ZString.Format("{0} 個の不正なシェーダーを正しいシェーダーに置き換えますか？", incorrectResults.Count),
                        "実行", "キャンセル"))
                    {
                        FixAllIncorrectShaders();
                    }
                }
                GUI.color = Color.white;
            }
            EditorGUILayout.EndHorizontal();
            
            EditorGUILayout.Space();
            
            if (_checkResults.Count > 0)
            {
                var filteredResults = _showOnlyIncorrect
                    ? _checkResults.Where(r => !r.IsCorrect || r.IsWarning || r.IsError).ToList()
                    : _checkResults;
                    
                EditorGUILayout.LabelField(ZString.Format("チェック結果: {0} 件", filteredResults.Count));
                
                _scrollPosition = EditorGUILayout.BeginScrollView(_scrollPosition);
                
                foreach (var result in filteredResults)
                {
                    EditorGUILayout.BeginVertical(GUI.skin.box);
                    
                    EditorGUILayout.BeginHorizontal();
                    EditorGUILayout.LabelField("Character:", GUILayout.Width(70));
                    EditorGUILayout.LabelField(result.CharacterName);
                    EditorGUILayout.EndHorizontal();
                    
                    EditorGUILayout.BeginHorizontal();
                    EditorGUILayout.LabelField("Material:", GUILayout.Width(70));
                    if (GUILayout.Button(result.MaterialPath, EditorStyles.linkLabel))
                    {
                        var material = AssetDatabase.LoadAssetAtPath<Material>(result.MaterialPath);
                        if (material != null)
                        {
                            EditorGUIUtility.PingObject(material);
                            Selection.activeObject = material;
                        }
                    }
                    EditorGUILayout.EndHorizontal();
                    
                    EditorGUILayout.BeginHorizontal();
                    EditorGUILayout.LabelField("Status:", GUILayout.Width(70));
                    
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
                    else if (!result.IsCorrect)
                    {
                        GUI.color = Color.yellow;
                        EditorGUILayout.LabelField(
                            ZString.Format("Incorrect shader (GUID: {0})", result.CurrentShaderGuid), 
                            EditorStyles.wordWrappedLabel);
                        
                        if (GUILayout.Button("修正", GUILayout.Width(50)))
                        {
                            FixShader(result.MaterialPath);
                            RunCheck();
                        }
                    }
                    else
                    {
                        GUI.color = Color.green;
                        EditorGUILayout.LabelField("OK", EditorStyles.wordWrappedLabel);
                    }
                    GUI.color = Color.white;
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
            var characterFolders = Directory.GetDirectories("Assets/GLOW/Graphics/Characters");
            
            foreach (var folder in characterFolders)
            {
                var characterName = Path.GetFileName(folder);
                var spineFolderPath = Path.Combine(folder, "Spine");
                
                if (!Directory.Exists(spineFolderPath))
                    continue;
                    
                var materialPath = ZString.Format(MaterialPathPattern, characterName);
                var result = new MaterialCheckResult
                {
                    CharacterName = characterName,
                    MaterialPath = materialPath,
                    IsCorrect = false,
                    IsWarning = false,
                    IsError = false
                };
                
                if (!File.Exists(materialPath))
                {
                    result.IsWarning = true;
                    result.Message = "Material not found";
                }
                else
                {
                    var material = AssetDatabase.LoadAssetAtPath<Material>(materialPath);
                    if (material == null)
                    {
                        result.IsError = true;
                        result.Message = "Failed to load material";
                    }
                    else if (material.shader != null)
                    {
                        var shaderPath = AssetDatabase.GetAssetPath(material.shader);
                        var shaderGuid = AssetDatabase.AssetPathToGUID(shaderPath);
                        result.CurrentShaderGuid = shaderGuid;
                        result.IsCorrect = shaderGuid == CorrectShaderGuid;
                    }
                    else
                    {
                        result.CurrentShaderGuid = "null";
                        result.Message = "Shader is null";
                    }
                }
                
                _checkResults.Add(result);
            }
            
            var incorrectCount = _checkResults.Count(r => !r.IsCorrect);
            var warningCount = _checkResults.Count(r => r.IsWarning);
            var errorCount = _checkResults.Count(r => r.IsError);
            
            Debug.Log(ZString.Format(
                "チェック完了: {0} 件のマテリアルをチェックしました。不正: {1}, 警告: {2}, エラー: {3}", 
                _checkResults.Count, 
                incorrectCount, 
                warningCount, 
                errorCount));
        }
        
        void FixShader(string materialPath)
        {
            var material = AssetDatabase.LoadAssetAtPath<Material>(materialPath);
            if (material == null) return;
            
            var correctShaderPath = AssetDatabase.GUIDToAssetPath(CorrectShaderGuid);
            var correctShader = AssetDatabase.LoadAssetAtPath<Shader>(correctShaderPath);
            if (correctShader == null)
            {
                Debug.LogError(ZString.Format("正しいシェーダーが見つかりません: GUID={0}", CorrectShaderGuid));
                return;
            }
            
            material.shader = correctShader;
            EditorUtility.SetDirty(material);
            AssetDatabase.SaveAssets();
            
            Debug.Log(ZString.Format("シェーダーを修正しました: {0}", materialPath));
        }
        
        void FixAllIncorrectShaders()
        {
            var incorrectResults = _checkResults.Where(r => !r.IsCorrect && !r.IsWarning && !r.IsError).ToList();
            var fixedCount = 0;
            
            foreach (var result in incorrectResults)
            {
                FixShader(result.MaterialPath);
                fixedCount++;
            }
            
            AssetDatabase.Refresh();
            Debug.Log(ZString.Format("{0} 個のシェーダーを修正しました。", fixedCount));
            RunCheck();
        }
    }
}