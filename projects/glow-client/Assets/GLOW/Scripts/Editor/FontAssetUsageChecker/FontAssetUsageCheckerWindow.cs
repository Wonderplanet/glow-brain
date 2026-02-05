using System.Collections.Generic;
using System.IO;
using System.Linq;
using TMPro;
using UnityEditor;
using UnityEngine;

namespace GLOW.Editor.FontAssetUsageChecker
{
    /// <summary>
    /// Assets/GLOW/Graphics/Fonts以下のフォントアセットがどのプレハブで使用されているかを検索するEditor拡張
    /// </summary>
    public class FontAssetUsageCheckerWindow : EditorWindow
    {
        const string DefaultFontPath = "Assets/GLOW/Graphics/Fonts";
        const string DefaultSearchPath = "Assets";

        [SerializeField] string _fontAssetPath = DefaultFontPath;
        [SerializeField] string _searchPath = DefaultSearchPath;

        Vector2 _scrollPosition;
        List<FontAssetUsageInfo> _usageInfoList = new();
        List<FontAssetUsageInfo> UsageInfoList => _usageInfoList;
        bool _isSearched;
        readonly Dictionary<string, bool> _foldoutStates = new();
        Dictionary<string, bool> FoldoutStates => _foldoutStates;

        [MenuItem("GLOW/FindSupport/TextMeshPro Font利用先一覧", false, 306)]
        static void ShowWindow()
        {
            GetWindow<FontAssetUsageCheckerWindow>("Font Asset Usage Checker");
        }

        void OnGUI()
        {
            var so = new SerializedObject(this);
            so.Update();

            EditorGUILayout.LabelField("■ フォントアセット使用状況チェッカー", EditorStyles.boldLabel);
            EditorGUILayout.Space();

            EditorGUILayout.LabelField("フォントアセットのフォルダパス");
            EditorGUILayout.PropertyField(so.FindProperty("_fontAssetPath"), GUIContent.none);

            EditorGUILayout.Space();

            EditorGUILayout.LabelField("検索対象のフォルダパス");
            EditorGUILayout.PropertyField(so.FindProperty("_searchPath"), GUIContent.none);

            so.ApplyModifiedProperties();

            EditorGUILayout.Space();

            if (GUILayout.Button("検索開始", GUILayout.Height(30)))
            {
                SearchFontAssetUsage();
            }

            EditorGUILayout.Space();

            if (_isSearched)
            {
                DisplaySearchResults();
            }
        }

        void SearchFontAssetUsage()
        {
            if (string.IsNullOrEmpty(_fontAssetPath) || string.IsNullOrEmpty(_searchPath))
            {
                EditorUtility.DisplayDialog(
                    "パス未設定",
                    "フォントアセットのパスと検索対象のパスを両方設定してください",
                    "確認");
                return;
            }

            if (!Directory.Exists(_fontAssetPath))
            {
                EditorUtility.DisplayDialog(
                    "フォルダが見つかりません",
                    $"フォントアセットのパスが存在しません\n{_fontAssetPath}",
                    "確認");
                return;
            }

            if (!Directory.Exists(_searchPath))
            {
                EditorUtility.DisplayDialog(
                    "フォルダが見つかりません",
                    $"検索対象のパスが存在しません\n{_searchPath}",
                    "確認");
                return;
            }

            _usageInfoList.Clear();
            _isSearched = false;

            // フォントアセットを取得
            var fontAssets = GetFontAssets(_fontAssetPath);

            if (fontAssets.Count == 0)
            {
                EditorUtility.DisplayDialog(
                    "フォントアセットが見つかりません",
                    $"指定されたパスにフォントアセット(.asset)が見つかりませんでした\n{_fontAssetPath}",
                    "確認");
                return;
            }

            // プレハブを検索
            var prefabPaths = Directory.GetFiles(_searchPath, "*.prefab", SearchOption.AllDirectories);

            int totalPrefabs = prefabPaths.Length;
            int currentIndex = 0;

            foreach (var prefabPath in prefabPaths)
            {
                currentIndex++;
                EditorUtility.DisplayProgressBar(
                    "フォント使用状況を検索中",
                    $"検索中... ({currentIndex}/{totalPrefabs})",
                    (float)currentIndex / totalPrefabs);

                SearchInPrefab(prefabPath, fontAssets);
            }

            EditorUtility.ClearProgressBar();

            _usageInfoList = UsageInfoList.OrderBy(x => x.FontAssetName).ThenBy(x => x.PrefabPath).ToList();
            _isSearched = true;

            Debug.Log($"フォント使用状況の検索が完了しました。{UsageInfoList.Count}件の使用箇所が見つかりました。");
        }

        List<TMP_FontAsset> GetFontAssets(string folderPath)
        {
            var fontAssets = new List<TMP_FontAsset>();
            var assetPaths = Directory.GetFiles(folderPath, "*.asset", SearchOption.TopDirectoryOnly);

            foreach (var assetPath in assetPaths)
            {
                var fontAsset = AssetDatabase.LoadAssetAtPath<TMP_FontAsset>(assetPath);
                if (fontAsset != null)
                {
                    fontAssets.Add(fontAsset);
                }
            }

            return fontAssets;
        }

        void SearchInPrefab(string prefabPath, List<TMP_FontAsset> fontAssets)
        {
            var prefab = AssetDatabase.LoadAssetAtPath<GameObject>(prefabPath);
            if (prefab == null)
            {
                return;
            }

            var textComponents = prefab.GetComponentsInChildren<TextMeshProUGUI>(true);

            foreach (var textComponent in textComponents)
            {
                if (textComponent.font == null)
                {
                    continue;
                }

                foreach (var fontAsset in fontAssets)
                {
                    if (textComponent.font == fontAsset)
                    {
                        _usageInfoList.Add(new FontAssetUsageInfo
                        {
                            FontAssetName = fontAsset.name,
                            FontAsset = fontAsset,
                            PrefabPath = prefabPath,
                            PrefabName = prefab.name,
                            GameObjectName = GetGameObjectPath(textComponent.transform),
                            TextComponent = textComponent
                        });
                    }
                }
            }
        }

        string GetGameObjectPath(Transform transform)
        {
            var path = transform.name;
            var parent = transform.parent;

            while (parent != null)
            {
                path = $"{parent.name}/{path}";
                parent = parent.parent;
            }

            return path;
        }

        void DisplaySearchResults()
        {
            EditorGUILayout.LabelField($"■ 検索結果: {UsageInfoList.Count}件", EditorStyles.boldLabel);

            if (UsageInfoList.Count == 0)
            {
                EditorGUILayout.HelpBox("フォントアセットの使用箇所が見つかりませんでした。", MessageType.Info);
                return;
            }

            EditorGUILayout.Space();

            _scrollPosition = EditorGUILayout.BeginScrollView(_scrollPosition);

            // フォントごとにグループ化
            var groupedByFont = UsageInfoList.GroupBy(x => x.FontAssetName);

            // 各フォントグループを表示
            foreach (var fontGroup in groupedByFont)
            {
                var fontName = fontGroup.Key;
                var usageCount = fontGroup.Count();

                // フォント名が登録されていない場合は初期化
                if (!FoldoutStates.ContainsKey(fontName))
                {
                    // 展開状態(true)で新規登録
                    _foldoutStates[fontName] = true;
                }

                // 折りたたみ可能なヘッダーを表示（使用箇所数も表示）
                _foldoutStates[fontName] = EditorGUILayout.Foldout(
                    FoldoutStates[fontName],
                    $"【{fontName}】 ({usageCount}件)",
                    true,
                    EditorStyles.foldoutHeader);

                // 展開されている場合のみ詳細を表示
                if (FoldoutStates[fontName])
                {
                    EditorGUI.indentLevel++;

                    foreach (var info in fontGroup)
                    {
                        EditorGUILayout.BeginHorizontal(EditorStyles.helpBox);

                        // プレハブ名を表示（クリックで選択）
                        if (GUILayout.Button(info.PrefabName, EditorStyles.linkLabel, GUILayout.Width(200)))
                        {
                            var prefab = AssetDatabase.LoadAssetAtPath<GameObject>(info.PrefabPath);
                            Selection.activeObject = prefab;
                            EditorGUIUtility.PingObject(prefab);
                        }

                        // GameObject名を表示
                        EditorGUILayout.LabelField(info.GameObjectName);

                        EditorGUILayout.EndHorizontal();
                    }

                    EditorGUI.indentLevel--;
                }

                EditorGUILayout.Space();
            }

            EditorGUILayout.EndScrollView();
        }
    }

    /// <summary>
    /// フォントアセットの使用情報を保持するクラス
    /// </summary>
    class FontAssetUsageInfo
    {
        public string FontAssetName { get; set; }
        public TMP_FontAsset FontAsset { get; set; }
        public string PrefabPath { get; set; }
        public string PrefabName { get; set; }
        public string GameObjectName { get; set; }
        public TextMeshProUGUI TextComponent { get; set; }
    }
}

