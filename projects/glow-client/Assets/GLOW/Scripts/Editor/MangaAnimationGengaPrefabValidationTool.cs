using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Text.RegularExpressions;
using UnityEditor;
using UnityEngine;

namespace GLOW.Editor
{
    public class MangaAnimationGengaPrefabValidationTool : EditorWindow
    {
        string _targetFolderPath = "Assets/GLOW/AssetBundles/manga_animation/";
        Vector2 _scrollPosition;
        readonly List<string> _validationResults = new List<string>();
        bool _hasErrors;

        [MenuItem("GLOW/Check/Manga Animation Genga Prefab Validation Tool")]
        static void ShowWindow()
        {
            GetWindow<MangaAnimationGengaPrefabValidationTool>("Manga Animation Genga Prefab Validation Tool");
        }

        void OnGUI()
        {
            GUILayout.Label("Prefab Validation Settings", EditorStyles.boldLabel);

            EditorGUILayout.Space();

            GUILayout.Label("検証対象フォルダ:");
            EditorGUILayout.BeginHorizontal();
            _targetFolderPath = EditorGUILayout.TextField(_targetFolderPath);
            if (GUILayout.Button("Select Folder", GUILayout.Width(100)))
            {
                string selectedPath = EditorUtility.OpenFolderPanel("Select Folder", "Assets", "");
                if (!string.IsNullOrEmpty(selectedPath))
                {
                    if (selectedPath.StartsWith(Application.dataPath))
                    {
                        _targetFolderPath = "Assets" + selectedPath.Substring(Application.dataPath.Length);
                    }
                    else
                    {
                        Debug.LogWarning("プロジェクト内のフォルダを選択してください");
                    }
                }
            }
            EditorGUILayout.EndHorizontal();

            EditorGUILayout.Space();

            if (GUILayout.Button("検証実行", GUILayout.Height(30)))
            {
                RunValidation();
            }

            EditorGUILayout.Space();

            if (_validationResults.Count > 0)
            {
                GUILayout.Label("検証結果:", EditorStyles.boldLabel);

                if (_hasErrors)
                {
                    EditorGUILayout.HelpBox("エラーが見つかりました", MessageType.Error);
                }
                else
                {
                    EditorGUILayout.HelpBox("すべての検証に合格しました", MessageType.Info);
                }

                _scrollPosition = EditorGUILayout.BeginScrollView(_scrollPosition);
                foreach (string result in _validationResults)
                {
                    if (result.Contains("[OK]"))
                    {
                        EditorGUILayout.BeginHorizontal();
                        GUIStyle greenStyle = new GUIStyle(EditorStyles.label);
                        greenStyle.normal.textColor = Color.green;
                        GUILayout.Label("●", greenStyle, GUILayout.Width(15));
                        EditorGUILayout.LabelField(result.Replace("[OK]", "").TrimStart(), EditorStyles.wordWrappedLabel);
                        EditorGUILayout.EndHorizontal();
                    }
                    else if (result.Contains("[エラー]"))
                    {
                        EditorGUILayout.BeginHorizontal();
                        GUIStyle redStyle = new GUIStyle(EditorStyles.label);
                        redStyle.normal.textColor = Color.red;
                        GUILayout.Label("●", redStyle, GUILayout.Width(15));
                        EditorGUILayout.LabelField(result.Replace("[エラー]", "").TrimStart(), EditorStyles.wordWrappedLabel);
                        EditorGUILayout.EndHorizontal();
                    }
                    else
                    {
                        EditorGUILayout.LabelField(result, EditorStyles.wordWrappedLabel);
                    }
                }
                EditorGUILayout.EndScrollView();
            }
        }

        void RunValidation()
        {
            _validationResults.Clear();
            _hasErrors = false;

            if (!Directory.Exists(_targetFolderPath))
            {
                _validationResults.Add("指定されたフォルダが存在しません: " + _targetFolderPath);
                _hasErrors = true;
                return;
            }

            _validationResults.Add("=== 検証開始 ===");
            _validationResults.Add($"対象フォルダ: {_targetFolderPath}");
            _validationResults.Add("");

            string[] prefabPaths = Directory.GetFiles(_targetFolderPath, "*.prefab", SearchOption.AllDirectories);

            _validationResults.Add($"検出されたPrefab数: {prefabPaths.Length}");
            _validationResults.Add("");

            bool isStartPrefabsValid = ValidateStartPrefabs(prefabPaths);
            bool isAppearAndVictoryPrefabsValid = ValidateAppearAndVictoryPrefabs(prefabPaths);
            bool isSceneBindingsValid = ValidateSceneBindings(prefabPaths);
            bool isComicAnimDamValid = ValidateComicAnimDam(prefabPaths);

            _hasErrors = !isStartPrefabsValid || !isAppearAndVictoryPrefabsValid || !isSceneBindingsValid || !isComicAnimDamValid;

            _validationResults.Add("");
            _validationResults.Add("=== 検証完了 ===");
        }

        bool ValidateStartPrefabs(string[] prefabPaths)
        {
            _validationResults.Add("【検証1】_start.prefabにMangaAnimationBackgroundが含まれていないこと");

            string[] startPrefabs = prefabPaths
                .Where(path => Path.GetFileName(path).EndsWith("_start.prefab"))
                .ToArray();

            if (startPrefabs.Length == 0)
            {
                _validationResults.Add("  _start.prefabが見つかりませんでした");
            }

            bool hasValidationError = false;

            foreach (string prefabPath in startPrefabs)
            {
                string prefabContent = File.ReadAllText(prefabPath);

                if (prefabContent.Contains("MangaAnimationBackground"))
                {
                    _validationResults.Add($"  [エラー] {prefabPath} にMangaAnimationBackgroundが含まれています");
                    hasValidationError = true;
                }
                else
                {
                    _validationResults.Add($"  [OK] {prefabPath}");
                }
            }

            _validationResults.Add("");
            return !hasValidationError;
        }

        bool ValidateAppearAndVictoryPrefabs(string[] prefabPaths)
        {
            _validationResults.Add("【検証2】_appear.prefab、_victory.prefabにMangaAnimationBackgroundが入っていること");

            string[] appearPrefabs = prefabPaths
                .Where(path => Path.GetFileName(path).EndsWith("_appear.prefab"))
                .ToArray();

            string[] victoryPrefabs = prefabPaths
                .Where(path => Path.GetFileName(path).EndsWith("_victory.prefab"))
                .ToArray();

            if (appearPrefabs.Length == 0 && victoryPrefabs.Length == 0)
            {
                _validationResults.Add("  _appear.prefabまたは_victory.prefabが見つかりませんでした");
            }

            bool hasValidationError = false;

            foreach (string prefabPath in appearPrefabs)
            {
                string prefabContent = File.ReadAllText(prefabPath);

                if (!prefabContent.Contains("MangaAnimationBackground"))
                {
                    _validationResults.Add($"  [エラー] {prefabPath} にMangaAnimationBackgroundが含まれていません");
                    hasValidationError = true;
                }
                else
                {
                    _validationResults.Add($"  [OK] {prefabPath}");
                }
            }

            foreach (string prefabPath in victoryPrefabs)
            {
                string prefabContent = File.ReadAllText(prefabPath);

                if (!prefabContent.Contains("MangaAnimationBackground"))
                {
                    _validationResults.Add($"  [エラー] {prefabPath} にMangaAnimationBackgroundが含まれていません");
                    hasValidationError = true;
                }
                else
                {
                    _validationResults.Add($"  [OK] {prefabPath}");
                }
            }

            _validationResults.Add("");
            return !hasValidationError;
        }

        bool ValidateSceneBindings(string[] prefabPaths)
        {
            _validationResults.Add("【検証3】m_SceneBindingsの中のguidが1種類のみであること");

            bool hasValidationError = false;

            foreach (string prefabPath in prefabPaths)
            {
                string prefabContent = File.ReadAllText(prefabPath);

                // m_SceneBindingsセクションを探す
                Regex sceneBindingsRegex = new Regex(
                    @"m_SceneBindings:.*?(?=\n[a-zA-Z]|\n---|\Z)",
                    RegexOptions.Singleline
                );

                Match sceneBindingsMatch = sceneBindingsRegex.Match(prefabContent);

                if (sceneBindingsMatch.Success)
                {
                    string sceneBindingsSection = sceneBindingsMatch.Value;

                    // guid行を抽出
                    Regex guidRegex = new Regex(@"guid:\s*([a-f0-9]+)");
                    MatchCollection guidMatches = guidRegex.Matches(sceneBindingsSection);

                    if (guidMatches.Count > 0)
                    {
                        HashSet<string> uniqueGuids = new HashSet<string>();

                        foreach (Match guidMatch in guidMatches)
                        {
                            uniqueGuids.Add(guidMatch.Groups[1].Value);
                        }

                        if (uniqueGuids.Count > 1)
                        {
                            _validationResults.Add(
                                $"  [エラー] {prefabPath} のm_SceneBindingsに複数のguidが含まれています ({uniqueGuids.Count}種類)"
                            );
                            hasValidationError = true;
                        }
                        else
                        {
                            _validationResults.Add($"  [OK] {prefabPath} ({uniqueGuids.Count}種類のguid)");
                        }
                    }
                }
            }

            _validationResults.Add("");
            return !hasValidationError;
        }

        bool ValidateComicAnimDam(string[] prefabPaths)
        {
            _validationResults.Add("【検証4】ComicAnimDam.pngが含まれていないこと");

            bool hasValidationError = false;
            const string comicAnimDamGuid = "07951964e465e49f889623a513bbf43d";

            foreach (string prefabPath in prefabPaths)
            {
                bool containsComicAnimDam = CheckPrefabForComicAnimDam(prefabPath, comicAnimDamGuid);

                if (containsComicAnimDam)
                {
                    _validationResults.Add($"  [エラー] {prefabPath} にComicAnimDam.pngが含まれています");
                    hasValidationError = true;
                }
            }

            if (!hasValidationError)
            {
                _validationResults.Add("  [OK] すべてのPrefabでComicAnimDam.pngが含まれていません");
            }

            _validationResults.Add("");
            return !hasValidationError;
        }

        bool CheckPrefabForComicAnimDam(string prefabPath, string comicAnimDamGuid)
        {
            if (!File.Exists(prefabPath))
            {
                return false;
            }

            string prefabContent = File.ReadAllText(prefabPath);

            // 直接ComicAnimDamのGUIDが含まれているかチェック
            if (prefabContent.Contains(comicAnimDamGuid))
            {
                return true;
            }

            // PrefabInstanceで参照されている他のPrefabを探す
            // PrefabInstanceのセクションを抽出
            Regex prefabInstanceRegex = new Regex(
                @"--- !u!1001 &\d+\s+PrefabInstance:.*?(?=\n---|\Z)",
                RegexOptions.Singleline
            );

            MatchCollection prefabInstances = prefabInstanceRegex.Matches(prefabContent);

            foreach (Match prefabInstance in prefabInstances)
            {
                string instanceSection = prefabInstance.Value;

                // m_SourcePrefabのGUIDを取得
                Regex sourcePrefabRegex = new Regex(@"m_SourcePrefab:.*?guid:\s*([a-f0-9]+)", RegexOptions.Singleline);
                Match sourcePrefabMatch = sourcePrefabRegex.Match(instanceSection);

                if (sourcePrefabMatch.Success)
                {
                    string referencedGuid = sourcePrefabMatch.Groups[1].Value;
                    string referencedPrefabPath = AssetDatabase.GUIDToAssetPath(referencedGuid);

                    if (!string.IsNullOrEmpty(referencedPrefabPath) && referencedPrefabPath.EndsWith(".prefab"))
                    {
                        // 参照先のPrefabにComicAnimDamが含まれているかチェック
                        if (!File.Exists(referencedPrefabPath))
                        {
                            continue;
                        }

                        string referencedContent = File.ReadAllText(referencedPrefabPath);

                        if (referencedContent.Contains(comicAnimDamGuid))
                        {
                            // m_Modificationsセクション内でm_Spriteが変更されているかチェック
                            // propertyPath: m_Spriteが含まれているかを確認
                            bool hasSpriteModification = instanceSection.Contains("propertyPath: m_Sprite");

                            if (!hasSpriteModification)
                            {
                                // m_Spriteが変更されていない = ComicAnimDamがそのまま使われている
                                return true;
                            }
                        }
                    }
                }
            }

            return false;
        }
    }
}

