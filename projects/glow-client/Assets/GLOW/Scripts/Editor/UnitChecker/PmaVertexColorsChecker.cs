using System.Collections.Generic;
using System.IO;
using System.Linq;
using Cysharp.Text;
using Spine.Unity;
using UnityEditor;
using UnityEngine;

namespace GLOW.Editor.UnitChecker
{
    public class PmaVertexColorsChecker : EditorWindow
    {
        Vector2 _scrollPosition;
        List<PmaVertexColorsCheckResult> _checkResults = new List<PmaVertexColorsCheckResult>();
        string _targetFolderPath = "Assets/GLOW/AssetBundles/unit_sd_prefab";

        struct PmaVertexColorsCheckResult
        {
            public string PrefabPath;
            public string CharacterName;
            public string PrefabName;
            public bool IsCorrect;
            public string Message;
            public bool IsWarning;
            public bool IsError;
        }

        struct CheckResult
        {
            public bool Found;
            public bool IsCorrect;
            public string Message;
        }

        [MenuItem("GLOW/Check/PmaVertexColors/Show Window")]
        static void ShowWindow()
        {
            var window = GetWindow<PmaVertexColorsChecker>();
            window.titleContent = new GUIContent("PmaVertexColors Checker");
            window.Show();
        }

        [MenuItem("GLOW/Check/PmaVertexColors/Run Check")]
        public static void CheckPmaVertexColorsFromMenu()
        {
            // 初期化
            var incorrectPrefabs = new List<string>();
            var warnings = new List<string>();
            var errors = new List<string>();
            var checkedCount = 0;

            var targetFolderPath = "Assets/GLOW/AssetBundles/unit_sd_prefab";

            // フォルダの存在確認
            if (!Directory.Exists(targetFolderPath))
            {
                Debug.LogError(ZString.Format("Target folder does not exist: {0}", targetFolderPath));
                return;
            }

            // 指定フォルダ内の全プレハブファイルを再帰的に検索
            var prefabFiles = Directory.GetFiles(targetFolderPath, "*.prefab", SearchOption.AllDirectories);

            foreach (var prefabFile in prefabFiles)
            {
                var prefabPath = prefabFile.Replace("\\", "/");
                var prefab = AssetDatabase.LoadAssetAtPath<GameObject>(prefabPath);

                if (prefab == null)
                {
                    var error = ZString.Format("Failed to load prefab: {0}", prefabPath);
                    errors.Add(error);
                    Debug.LogError(error);
                    continue;
                }

                // checkedCount更新
                checkedCount++;

                var checkResult = CheckPmaVertexColorsValue(prefab);

                // incorrectPrefabsリスト更新
                if (!checkResult.Found)
                {
                    incorrectPrefabs.Add(ZString.Format("{0} (pmaVertexColorsプロパティが見つかりません)", prefabPath));
                }
                else if (!checkResult.IsCorrect)
                {
                    incorrectPrefabs.Add(ZString.Format("{0} ({1})", prefabPath, checkResult.Message));
                }
            }

            // 結果をコンソールウィンドウに表示
            ShowConsoleWindowMessage(checkedCount, incorrectPrefabs, warnings, errors);
        }

        static void ShowConsoleWindowMessage(
            int checkedCount,
            List<string> incorrectPrefabs,
            List<string> warnings,
            List<string> errors)
        {
            Debug.Log(ZString.Format(
                "チェック完了: {0} 件のプレハブをチェックしました。問題のあるもの: {1}, 警告: {2}, エラー: {3}",
                checkedCount,
                incorrectPrefabs.Count,
                warnings.Count,
                errors.Count));
        }

        static CheckResult CheckPmaVertexColorsValue(GameObject prefab)
        {
            var components = prefab.GetComponentsInChildren<SkeletonAnimation>(true);

            foreach (var component in components)
            {
                if (component == null)
                {
                    continue;
                }

                var serializedObject = new SerializedObject(component);
                var property = serializedObject.GetIterator();

                while (property.Next(true))
                {
                    if (property.name == "pmaVertexColors" && property.propertyType == SerializedPropertyType.Boolean)
                    {
                        var isCorrect = property.boolValue; // true = 1, false = 0
                        return new CheckResult
                        {
                            Found = true,
                            IsCorrect = isCorrect,
                            Message = isCorrect ? "pmaVertexColors = 1" : "pmaVertexColors = 0 (要修正)"
                        };
                    }
                }
            }

            return new CheckResult
            {
                Found = false,
                IsCorrect = false,
                Message = "pmaVertexColorsプロパティが見つかりません"
            };
        }

        void OnGUI()
        {
            EditorGUILayout.LabelField("PmaVertexColors Checker", EditorStyles.boldLabel);
            EditorGUILayout.Space();

            EditorGUILayout.BeginHorizontal();
            EditorGUILayout.LabelField("Target Folder:", GUILayout.Width(100));
            _targetFolderPath = EditorGUILayout.TextField(_targetFolderPath);
            if (GUILayout.Button("Browse", GUILayout.Width(70)))
            {
                var selectedPath = EditorUtility.OpenFolderPanel("Select Target Folder", _targetFolderPath, "");
                if (!string.IsNullOrEmpty(selectedPath))
                {
                    _targetFolderPath = "Assets" + selectedPath.Substring(Application.dataPath.Length);
                }
            }

            EditorGUILayout.EndHorizontal();

            EditorGUILayout.Space();


            if (GUILayout.Button("チェックを実行"))
            {
                RunCheck();
            }

            EditorGUILayout.Space();

            if (_checkResults.Count > 0)
            {
                var resultText = ZString.Format(
                        "チェック結果: {0} 件。問題のあるもの: {1}, 警告: {2}, エラー: {3}",
                        _checkResults.Count,
                        _checkResults.Count(r => !r.IsCorrect),
                        _checkResults.Count(r => r.IsWarning),
                        _checkResults.Count(r => r.IsError));
                EditorGUILayout.LabelField(resultText, EditorStyles.boldLabel);

                _scrollPosition = EditorGUILayout.BeginScrollView(_scrollPosition);

                foreach (var result in _checkResults)
                {
                    EditorGUILayout.BeginVertical(GUI.skin.box);

                    EditorGUILayout.BeginHorizontal();
                    EditorGUILayout.LabelField("Character:", GUILayout.Width(80));
                    EditorGUILayout.LabelField(result.CharacterName);
                    EditorGUILayout.EndHorizontal();

                    EditorGUILayout.BeginHorizontal();
                    EditorGUILayout.LabelField("Prefab:", GUILayout.Width(80));
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
                    EditorGUILayout.LabelField("Status:", GUILayout.Width(80));

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
                        EditorGUILayout.LabelField("pmaVertexColors property not checked.", EditorStyles.wordWrappedLabel);
                    }
                    else
                    {
                        GUI.color = Color.green;
                        EditorGUILayout.LabelField("OK - pmaVertexColors checked.", EditorStyles.wordWrappedLabel);
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

            if (!Directory.Exists(_targetFolderPath))
            {
                Debug.LogError(ZString.Format("Target folder does not exist: {0}", _targetFolderPath));
                return;
            }

            // 指定フォルダ内の全プレハブファイルを再帰的に検索
            var prefabFiles = Directory.GetFiles(_targetFolderPath, "*.prefab", SearchOption.AllDirectories);

            foreach (var prefabFile in prefabFiles)
            {
                var prefabPath = prefabFile.Replace("\\", "/");
                var prefabName = Path.GetFileNameWithoutExtension(prefabPath);

                // フォルダパスから相対的な親フォルダ名を取得
                var relativePath = prefabPath.Substring(_targetFolderPath.Length + 1);
                var folderName = Path.GetDirectoryName(relativePath);
                if (string.IsNullOrEmpty(folderName))
                {
                    folderName = "Root";
                }

                var result = new PmaVertexColorsCheckResult
                {
                    CharacterName = folderName,
                    PrefabName = prefabName,
                    PrefabPath = prefabPath,
                    IsCorrect = false,
                    IsWarning = false,
                    IsError = false
                };

                var prefab = AssetDatabase.LoadAssetAtPath<GameObject>(prefabPath);
                if (prefab == null)
                {
                    result.IsError = true;
                    result.Message = "Failed to load prefab";
                }
                else
                {
                    var checkResult = CheckPmaVertexColorsValue(prefab);
                    result.IsCorrect = checkResult.Found && checkResult.IsCorrect;
                    result.Message = checkResult.Message;
                }

                _checkResults.Add(result);
            }

            var incorrectCount = _checkResults.Count(r => !r.IsCorrect);
            var warningCount = _checkResults.Count(r => r.IsWarning);
            var errorCount = _checkResults.Count(r => r.IsError);

            Debug.Log(ZString.Format(
                "チェック完了: {0} 件のプレハブをチェックしました。問題のあるもの: {1}, 警告: {2}, エラー: {3}",
                _checkResults.Count,
                incorrectCount,
                warningCount,
                errorCount));
        }
    }
}
