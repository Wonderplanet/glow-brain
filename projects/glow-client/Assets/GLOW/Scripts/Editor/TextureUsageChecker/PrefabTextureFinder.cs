using System.Collections.Generic;
using System.IO;
using System.Linq;
using UnityEditor;
using UnityEditor.AddressableAssets;
using UnityEditor.AddressableAssets.Settings;
using UnityEditor.AddressableAssets.Settings.GroupSchemas;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Editor.TextureUsageChecker
{
    /// <summary>
    /// 現在開いているプレハブ内で特定のテクスチャ（PNG等）が使用されているかを検索し、
    /// ヒエラルキー上での位置表示や別のテクスチャへの差し替えを行うエディタ拡張
    /// </summary>
    public class PrefabTextureFinder : EditorWindow
    {
        enum SearchMode
        {
            MissingAssets,
            AllDependencies
        }

        [SerializeField] SearchMode _searchMode = SearchMode.AllDependencies;
        [SerializeField] bool _detectMissing = true;
        [SerializeField] bool _showOnlyRemoteAddressable;
        [SerializeField] DefaultAsset _replacementFolderForDependencies;

        Vector2 _scrollPosition;
        List<DependencyInfo> _dependencyInfoList = new();
        List<MissingReferenceInfo> _missingReferenceList = new();
        bool _isSearched;
        GameObject _currentPrefabRoot;

        [MenuItem("GLOW/FindSupport/Prefab Texture Finder", false, 307)]
        static void ShowWindow()
        {
            GetWindow<PrefabTextureFinder>("Prefab Texture Finder");
        }

        void OnGUI()
        {
            var so = new SerializedObject(this);
            so.Update();

            EditorGUILayout.LabelField("■ プレハブ内テクスチャ検索・置換ツール", EditorStyles.boldLabel);
            EditorGUILayout.Space();

            // 現在開いているプレハブの状態を表示
            DisplayCurrentPrefabStatus();

            EditorGUILayout.Space();

            // 検索モード選択
            EditorGUILayout.LabelField("検索モード", EditorStyles.boldLabel);
            EditorGUILayout.PropertyField(so.FindProperty("_searchMode"), GUIContent.none);

            EditorGUILayout.Space();

            if (_searchMode == SearchMode.MissingAssets)
            {
                DrawMissingAssetsMode();
            }
            else if (_searchMode == SearchMode.AllDependencies)
            {
                DrawAllDependenciesMode();
            }

            so.ApplyModifiedProperties();

            EditorGUILayout.Space();

            if (_isSearched)
            {
                if (_searchMode == SearchMode.MissingAssets)
                {
                    DisplayMissingResults();
                }
                else if (_searchMode == SearchMode.AllDependencies)
                {
                    DisplayAllDependenciesResults();
                }
            }
        }

        void DrawMissingAssetsMode()
        {
            EditorGUILayout.HelpBox(
                "プレハブ内でMissing参照になっているアセットを検索します。\n" +
                "参照先のアセットが削除または移動されている場合に検出されます。",
                MessageType.Info);

            EditorGUILayout.Space();

            using (new EditorGUI.DisabledScope(_currentPrefabRoot == null))
            {
                if (GUILayout.Button("Missing参照を検索", GUILayout.Height(30)))
                {
                    SearchMissingAssets();
                }
            }
        }

        void DrawAllDependenciesMode()
        {
            var so = new SerializedObject(this);
            so.Update();

            EditorGUILayout.HelpBox(
                "AssetDatabase.GetDependenciesを使用して、プレハブが依存している全てのアセットを検索します。\n" +
                "マテリアル経由の参照、スクリプトのSerializedField参照など、間接的な参照も全て検出します。",
                MessageType.Info);

            EditorGUILayout.Space();

            _showOnlyRemoteAddressable = EditorGUILayout.Toggle(
                new GUIContent("リモートAddressableのみ表示", "依存先がリモート配信のAddressableで管理されているアセットのみを表示します"),
                _showOnlyRemoteAddressable);

            EditorGUILayout.Space();

            EditorGUILayout.LabelField("置換先のフォルダ（任意）", EditorStyles.boldLabel);
            EditorGUILayout.PropertyField(so.FindProperty("_replacementFolderForDependencies"), GUIContent.none);
            EditorGUILayout.HelpBox(
                "リモートアセットを置換先フォルダ内の同名アセットに置き換えます。",
                MessageType.None);

            so.ApplyModifiedProperties();

            EditorGUILayout.Space();

            using (new EditorGUI.DisabledScope(_currentPrefabRoot == null))
            {
                if (GUILayout.Button("全ての依存アセットを検索", GUILayout.Height(30)))
                {
                    SearchAllDependencies();
                }
            }
        }


        void OnEnable()
        {
            EditorApplication.hierarchyChanged += OnHierarchyChanged;
            RefreshCurrentPrefabRoot();
        }

        void OnDisable()
        {
            EditorApplication.hierarchyChanged -= OnHierarchyChanged;
        }

        void OnHierarchyChanged()
        {
            RefreshCurrentPrefabRoot();
            Repaint();
        }

        void RefreshCurrentPrefabRoot()
        {
            var prefabStage = UnityEditor.SceneManagement.PrefabStageUtility.GetCurrentPrefabStage();
            _currentPrefabRoot = prefabStage != null ? prefabStage.prefabContentsRoot : null;
        }

        void DisplayCurrentPrefabStatus()
        {
            EditorGUILayout.LabelField("現在開いているプレハブ", EditorStyles.boldLabel);

            if (_currentPrefabRoot != null)
            {
                EditorGUILayout.BeginHorizontal(EditorStyles.helpBox);
                EditorGUILayout.LabelField(_currentPrefabRoot.name, EditorStyles.label);
                EditorGUILayout.EndHorizontal();
            }
            else
            {
                EditorGUILayout.HelpBox("プレハブが開かれていません。プレハブを開いてください。", MessageType.Warning);
            }
        }

        void SearchAllDependencies()
        {
            _dependencyInfoList.Clear();
            _isSearched = false;

            if (_currentPrefabRoot == null)
            {
                EditorUtility.DisplayDialog(
                    "プレハブが開かれていません",
                    "プレハブを開いてから検索を実行してください。",
                    "確認");
                return;
            }

            // プレハブのアセットパスを取得
            var prefabStage = UnityEditor.SceneManagement.PrefabStageUtility.GetCurrentPrefabStage();
            if (prefabStage == null) return;

            var prefabPath = prefabStage.assetPath;

            // 置換先フォルダ内のアセットを収集
            var replacementAssetMap = new Dictionary<string, string>();
            if (_replacementFolderForDependencies != null)
            {
                var folderPath = AssetDatabase.GetAssetPath(_replacementFolderForDependencies);
                if (Directory.Exists(folderPath))
                {
                    var assetPaths = Directory.GetFiles(folderPath, "*.*", SearchOption.AllDirectories)
                        .Where(p => !p.EndsWith(".meta"))
                        .ToArray();

                    foreach (var assetPath in assetPaths)
                    {
                        var fileName = Path.GetFileName(assetPath);
                        replacementAssetMap[fileName] = assetPath.Replace("\\", "/");
                    }
                }
            }

            // AssetDatabase.GetDependenciesを使用して全ての依存アセットを取得
            var dependencies = AssetDatabase.GetDependencies(prefabPath, true);

            // 依存元を特定するための辞書を作成
            var dependencySourceMap = new Dictionary<string, List<string>>();

            foreach (var dependencyPath in dependencies)
            {
                if (dependencyPath == prefabPath) continue;
                if (dependencyPath.EndsWith(".cs")) continue;

                dependencySourceMap[dependencyPath] = new List<string>();
            }

            // 各アセットの依存先を調べて、依存元を特定
            foreach (var sourcePath in dependencies)
            {
                if (sourcePath.EndsWith(".cs")) continue;

                var sourceDependencies = AssetDatabase.GetDependencies(sourcePath, false);
                foreach (var targetPath in sourceDependencies)
                {
                    if (targetPath == sourcePath) continue;
                    if (dependencySourceMap.ContainsKey(targetPath))
                    {
                        var sourceName = sourcePath == prefabPath 
                            ? "[Prefab直接参照]" 
                            : Path.GetFileName(sourcePath);
                        if (!dependencySourceMap[targetPath].Contains(sourceName))
                        {
                            dependencySourceMap[targetPath].Add(sourceName);
                        }
                    }
                }
            }

            foreach (var dependencyPath in dependencies)
            {
                // プレハブ自体は除外
                if (dependencyPath == prefabPath) continue;

                // スクリプト（.cs）は除外
                if (dependencyPath.EndsWith(".cs")) continue;

                // アセットの種類を判定
                var assetType = AssetDatabase.GetMainAssetTypeAtPath(dependencyPath);
                if (assetType == null) continue;

                // リモートAddressableかどうかを判定
                var isRemoteAddressable = IsRemoteAddressableAsset(dependencyPath);

                // リモートAddressableのみ表示する場合、リモートでないアセットはスキップ
                if (_showOnlyRemoteAddressable && !isRemoteAddressable) continue;

                var assetTypeName = GetAssetTypeName(assetType, dependencyPath);
                var referencedBy = dependencySourceMap.ContainsKey(dependencyPath) 
                    ? dependencySourceMap[dependencyPath] 
                    : new List<string>();

                // 置換先アセットの有無を確認
                var fileName = Path.GetFileName(dependencyPath);
                var hasReplacement = replacementAssetMap.ContainsKey(fileName);
                var replacementPath = hasReplacement ? replacementAssetMap[fileName] : null;

                _dependencyInfoList.Add(new DependencyInfo
                {
                    AssetPath = dependencyPath,
                    AssetName = fileName,
                    AssetType = assetTypeName,
                    AssetTypeCategory = GetAssetTypeCategory(assetType, dependencyPath),
                    ReferencedBy = referencedBy,
                    IsRemoteAddressable = isRemoteAddressable,
                    HasReplacement = hasReplacement,
                    ReplacementPath = replacementPath
                });
            }

            // 種類別にソート
            _dependencyInfoList = _dependencyInfoList
                .OrderBy(x => x.AssetTypeCategory)
                .ThenBy(x => x.AssetName)
                .ToList();

            _isSearched = true;

            Debug.Log($"依存アセット検索が完了しました。{_dependencyInfoList.Count}件の依存アセットが見つかりました。");
        }

        string GetAssetTypeName(System.Type assetType, string path)
        {
            if (assetType == typeof(Texture2D)) return "Texture";
            if (assetType == typeof(Sprite)) return "Sprite";
            if (assetType == typeof(Material)) return "Material";
            if (assetType == typeof(Shader)) return "Shader";
            if (assetType == typeof(AnimationClip)) return "Animation";
            if (assetType == typeof(RuntimeAnimatorController)) return "Animator";
            if (assetType == typeof(AudioClip)) return "Audio";
            if (assetType == typeof(Font)) return "Font";
            if (assetType == typeof(GameObject)) return "Prefab";
            if (assetType == typeof(ScriptableObject)) return "ScriptableObject";
            if (path.EndsWith(".prefab")) return "Prefab";
            if (path.EndsWith(".mat")) return "Material";
            if (path.EndsWith(".png") || path.EndsWith(".jpg") || path.EndsWith(".tga")) return "Texture";
            if (path.EndsWith(".anim")) return "Animation";
            if (path.EndsWith(".controller")) return "Animator";
            if (path.EndsWith(".asset")) return "Asset";

            return assetType.Name;
        }

        int GetAssetTypeCategory(System.Type assetType, string path)
        {
            // 表示順序のカテゴリ
            if (assetType == typeof(Texture2D) || path.EndsWith(".png") || path.EndsWith(".jpg") || path.EndsWith(".tga")) return 0;
            if (assetType == typeof(Sprite)) return 1;
            if (assetType == typeof(Material) || path.EndsWith(".mat")) return 2;
            if (assetType == typeof(Shader)) return 3;
            if (assetType == typeof(AnimationClip) || path.EndsWith(".anim")) return 4;
            if (assetType == typeof(RuntimeAnimatorController) || path.EndsWith(".controller")) return 5;
            if (assetType == typeof(GameObject) || path.EndsWith(".prefab")) return 6;
            if (assetType == typeof(AudioClip)) return 7;
            if (assetType == typeof(Font)) return 8;

            return 99;
        }

        bool IsRemoteAddressableAsset(string assetPath)
        {
            var settings = AddressableAssetSettingsDefaultObject.Settings;
            if (settings == null) return false;

            var guid = AssetDatabase.AssetPathToGUID(assetPath);
            var entry = settings.FindAssetEntry(guid);
            if (entry == null) return false;

            var group = entry.parentGroup;
            return IsRemoteAddressableAssetGroup(group);
        }

        void SearchMissingAssets()
        {
            _missingReferenceList.Clear();
            _isSearched = false;

            if (_currentPrefabRoot == null)
            {
                EditorUtility.DisplayDialog(
                    "プレハブが開かれていません",
                    "プレハブを開いてから検索を実行してください。",
                    "確認");
                return;
            }

            SearchMissingReferences(_currentPrefabRoot);

            _isSearched = true;

            Debug.Log($"Missing参照検索が完了しました。{_missingReferenceList.Count}件のMissing参照が見つかりました。");
        }

        void SearchMissingReferences(GameObject root)
        {
            // Image
            var images = root.GetComponentsInChildren<Image>(true);
            foreach (var image in images)
            {
                var so = new SerializedObject(image);
                var spriteProp = so.FindProperty("m_Sprite");

                if (IsMissingReference(spriteProp))
                {
                    _missingReferenceList.Add(new MissingReferenceInfo
                    {
                        GameObject = image.gameObject,
                        HierarchyPath = GetGameObjectPath(image.transform),
                        ComponentType = "Image",
                        PropertyName = "Sprite"
                    });
                }
            }

            // RawImage
            var rawImages = root.GetComponentsInChildren<RawImage>(true);
            foreach (var rawImage in rawImages)
            {
                var so = new SerializedObject(rawImage);
                var textureProp = so.FindProperty("m_Texture");

                if (IsMissingReference(textureProp))
                {
                    _missingReferenceList.Add(new MissingReferenceInfo
                    {
                        GameObject = rawImage.gameObject,
                        HierarchyPath = GetGameObjectPath(rawImage.transform),
                        ComponentType = "RawImage",
                        PropertyName = "Texture"
                    });
                }
            }

            // SpriteRenderer
            var spriteRenderers = root.GetComponentsInChildren<SpriteRenderer>(true);
            foreach (var spriteRenderer in spriteRenderers)
            {
                var so = new SerializedObject(spriteRenderer);
                var spriteProp = so.FindProperty("m_Sprite");

                if (IsMissingReference(spriteProp))
                {
                    _missingReferenceList.Add(new MissingReferenceInfo
                    {
                        GameObject = spriteRenderer.gameObject,
                        HierarchyPath = GetGameObjectPath(spriteRenderer.transform),
                        ComponentType = "SpriteRenderer",
                        PropertyName = "Sprite"
                    });
                }
            }

            // Renderer (Materials)
            var renderers = root.GetComponentsInChildren<Renderer>(true);
            foreach (var renderer in renderers)
            {
                var so = new SerializedObject(renderer);
                var materialsProp = so.FindProperty("m_Materials");

                for (int i = 0; i < materialsProp.arraySize; i++)
                {
                    var materialProp = materialsProp.GetArrayElementAtIndex(i);
                    if (IsMissingReference(materialProp))
                    {
                        _missingReferenceList.Add(new MissingReferenceInfo
                        {
                            GameObject = renderer.gameObject,
                            HierarchyPath = GetGameObjectPath(renderer.transform),
                            ComponentType = renderer.GetType().Name,
                            PropertyName = $"Material[{i}]"
                        });
                    }
                }
            }
        }

        bool IsMissingReference(SerializedProperty property)
        {
            if (property == null) return false;
            if (property.propertyType != SerializedPropertyType.ObjectReference) return false;

            // objectReferenceValueがnullで、objectReferenceInstanceIDValueが0でない場合はMissing
            return property.objectReferenceValue == null && property.objectReferenceInstanceIDValue != 0;
        }

        void DisplayAllDependenciesResults()
        {
            EditorGUILayout.LabelField($"■ 依存アセット検索結果: {_dependencyInfoList.Count}件", EditorStyles.boldLabel);

            if (_dependencyInfoList.Count == 0)
            {
                EditorGUILayout.HelpBox("依存アセットが見つかりませんでした。", MessageType.Info);
                return;
            }

            EditorGUILayout.HelpBox(
                "AssetDatabase.GetDependenciesを使用して検出した全ての依存アセットです。\n" +
                "マテリアル経由の参照、スクリプトのSerializedField参照など、間接的な参照も含まれます。\n" +
                "「参照元」は、そのアセットを参照しているアセットを示します。",
                MessageType.Info);

            EditorGUILayout.Space();

            // 一括置換ボタン
            var replaceableCount = _dependencyInfoList.Count(x => x.IsRemoteAddressable && x.HasReplacement);
            if (replaceableCount > 0)
            {
                EditorGUILayout.BeginHorizontal();
                EditorGUILayout.LabelField($"置換可能なリモートアセット: {replaceableCount}件", EditorStyles.boldLabel);
                if (GUILayout.Button("すべて置換", GUILayout.Width(100)))
                {
                    ReplaceAllDependencies();
                }
                EditorGUILayout.EndHorizontal();
                EditorGUILayout.Space();
            }

            _scrollPosition = EditorGUILayout.BeginScrollView(_scrollPosition);

            // 種類ごとにグループ化して表示
            var groupedByType = _dependencyInfoList.GroupBy(x => x.AssetType);

            foreach (var group in groupedByType)
            {
                EditorGUILayout.LabelField($"【{group.Key}】 ({group.Count()}件)", EditorStyles.boldLabel);

                foreach (var info in group)
                {
                    EditorGUILayout.BeginVertical(EditorStyles.helpBox);

                    EditorGUILayout.BeginHorizontal();

                    // リモートAddressableの場合はラベルを追加
                    if (info.IsRemoteAddressable)
                    {
                        var remoteStyle = new GUIStyle(EditorStyles.miniLabel)
                        {
                            normal = { textColor = Color.red },
                            fontStyle = FontStyle.Bold
                        };
                        EditorGUILayout.LabelField("[Remote]", remoteStyle, GUILayout.Width(55));
                    }

                    // 置換可能の場合はラベルを追加
                    if (info.HasReplacement)
                    {
                        var replaceableStyle = new GUIStyle(EditorStyles.miniLabel)
                        {
                            normal = { textColor = Color.green },
                            fontStyle = FontStyle.Bold
                        };
                        EditorGUILayout.LabelField("[置換可]", replaceableStyle, GUILayout.Width(50));
                    }

                    // アセット名を表示
                    var nameWidth = 250;
                    if (info.IsRemoteAddressable) nameWidth -= 55;
                    if (info.HasReplacement) nameWidth -= 50;
                    EditorGUILayout.LabelField(info.AssetName, EditorStyles.boldLabel, GUILayout.Width(nameWidth));

                    // アセット選択ボタン
                    if (GUILayout.Button("選択", GUILayout.Width(50)))
                    {
                        var asset = AssetDatabase.LoadAssetAtPath<Object>(info.AssetPath);
                        if (asset != null)
                        {
                            Selection.activeObject = asset;
                            EditorGUIUtility.PingObject(asset);
                        }
                    }

                    // 置換ボタン
                    using (new EditorGUI.DisabledScope(!info.HasReplacement))
                    {
                        if (GUILayout.Button("置換", GUILayout.Width(50)))
                        {
                            ReplaceDependencyAsset(info);
                        }
                    }

                    EditorGUILayout.EndHorizontal();

                    // パスを表示
                    EditorGUILayout.LabelField($"Path: {info.AssetPath}", EditorStyles.miniLabel);

                    // 置換先パスを表示
                    if (info.HasReplacement)
                    {
                        EditorGUILayout.LabelField($"置換先: {info.ReplacementPath}", EditorStyles.miniLabel);
                    }

                    // 参照元を表示
                    if (info.ReferencedBy != null && info.ReferencedBy.Count > 0)
                    {
                        var referencedByText = string.Join(", ", info.ReferencedBy);
                        EditorGUILayout.LabelField($"参照元: {referencedByText}", EditorStyles.miniLabel);
                    }
                    else
                    {
                        EditorGUILayout.LabelField("参照元: 不明（間接参照の可能性）", EditorStyles.miniLabel);
                    }

                    EditorGUILayout.EndVertical();
                }

                EditorGUILayout.Space();
            }

            EditorGUILayout.EndScrollView();
        }

        void ReplaceAllDependencies()
        {
            var replaceableItems = _dependencyInfoList.Where(x => x.IsRemoteAddressable && x.HasReplacement).ToList();

            if (replaceableItems.Count == 0)
            {
                EditorUtility.DisplayDialog(
                    "置換対象なし",
                    "置換可能なリモートアセットがありません。",
                    "確認");
                return;
            }

            var confirm = EditorUtility.DisplayDialog(
                "一括置換の確認",
                $"{replaceableItems.Count}件のリモートアセットを置換しますか？\n\n" +
                "注意: この操作はプレハブファイルを直接編集します。",
                "置換",
                "キャンセル");

            if (!confirm) return;

            var successCount = 0;
            foreach (var info in replaceableItems)
            {
                if (ReplaceDependencyAssetInternal(info))
                {
                    successCount++;
                }
            }

            AssetDatabase.SaveAssets();
            AssetDatabase.Refresh();

            EditorUtility.DisplayDialog(
                "置換完了",
                $"{successCount}件のアセットを置換しました。",
                "確認");

            // 検索結果を更新
            SearchAllDependencies();
        }

        void ReplaceDependencyAsset(DependencyInfo info)
        {
            if (!info.HasReplacement)
            {
                EditorUtility.DisplayDialog(
                    "置換不可",
                    "置換先フォルダに同名のアセットが見つかりません。",
                    "確認");
                return;
            }

            if (ReplaceDependencyAssetInternal(info))
            {
                AssetDatabase.SaveAssets();
                AssetDatabase.Refresh();

                Debug.Log($"アセットを置換しました: {info.AssetPath} → {info.ReplacementPath}");

                // 検索結果を更新
                SearchAllDependencies();
            }
        }

        bool ReplaceDependencyAssetInternal(DependencyInfo info)
        {
            if (string.IsNullOrEmpty(info.ReplacementPath)) return false;

            // 置換先のアセットを読み込む
            var replacementAsset = AssetDatabase.LoadAssetAtPath<Object>(info.ReplacementPath);

            if (replacementAsset == null)
            {
                Debug.LogWarning($"置換先アセットの読み込みに失敗しました: {info.ReplacementPath}");
                return false;
            }

            // 参照元アセットを更新
            foreach (var referencedByName in info.ReferencedBy)
            {
                if (referencedByName == "[Prefab直接参照]")
                {
                    // プレハブ内の直接参照を置換
                    ReplacePrefabDirectReference(info, replacementAsset);
                }
                else
                {
                    // 他のアセット（マテリアル等）内の参照を置換
                    ReplaceAssetReference(referencedByName, info, replacementAsset);
                }
            }

            return true;
        }

        void ReplacePrefabDirectReference(DependencyInfo info, Object replacementAsset)
        {
            if (_currentPrefabRoot == null) return;

            // テクスチャ/スプライトの場合
            if (info.AssetType == "Texture" || info.AssetType == "Sprite")
            {
                var sprite = replacementAsset as Sprite;
                if (sprite == null)
                {
                    var texture = replacementAsset as Texture2D;
                    if (texture != null)
                    {
                        sprite = GetSpriteFromTexture(texture);
                    }
                }

                if (sprite != null)
                {
                    // Image
                    var images = _currentPrefabRoot.GetComponentsInChildren<Image>(true);
                    foreach (var image in images)
                    {
                        if (image.sprite != null)
                        {
                            var spritePath = AssetDatabase.GetAssetPath(image.sprite);
                            if (spritePath == info.AssetPath)
                            {
                                Undo.RecordObject(image, "Replace Sprite");
                                image.sprite = sprite;
                                EditorUtility.SetDirty(image);
                            }
                        }
                    }

                    // SpriteRenderer
                    var spriteRenderers = _currentPrefabRoot.GetComponentsInChildren<SpriteRenderer>(true);
                    foreach (var sr in spriteRenderers)
                    {
                        if (sr.sprite != null)
                        {
                            var spritePath = AssetDatabase.GetAssetPath(sr.sprite);
                            if (spritePath == info.AssetPath)
                            {
                                Undo.RecordObject(sr, "Replace Sprite");
                                sr.sprite = sprite;
                                EditorUtility.SetDirty(sr);
                            }
                        }
                    }
                }

                // RawImage
                if (replacementAsset is Texture texture2)
                {
                    var rawImages = _currentPrefabRoot.GetComponentsInChildren<RawImage>(true);
                    foreach (var rawImage in rawImages)
                    {
                        if (rawImage.texture != null)
                        {
                            var texturePath = AssetDatabase.GetAssetPath(rawImage.texture);
                            if (texturePath == info.AssetPath)
                            {
                                Undo.RecordObject(rawImage, "Replace Texture");
                                rawImage.texture = texture2;
                                EditorUtility.SetDirty(rawImage);
                            }
                        }
                    }
                }
            }
            // AnimationClipの場合
            else if (info.AssetType == "Animation")
            {
                var replacementClip = replacementAsset as AnimationClip;
                if (replacementClip != null)
                {
                    // Animator
                    var animators = _currentPrefabRoot.GetComponentsInChildren<Animator>(true);
                    foreach (var animator in animators)
                    {
                        if (animator.runtimeAnimatorController != null)
                        {
                            var controller = animator.runtimeAnimatorController;
                            var controllerPath = AssetDatabase.GetAssetPath(controller);

                            // AnimatorController内のAnimationClipを検索
                            var clips = controller.animationClips;
                            foreach (var clip in clips)
                            {
                                if (clip != null)
                                {
                                    var clipPath = AssetDatabase.GetAssetPath(clip);
                                    if (clipPath == info.AssetPath)
                                    {
                                        // AnimatorController自体を変更する必要がある
                                        Debug.LogWarning($"AnimatorController内のAnimationClipは手動で置換してください: {controllerPath}");
                                    }
                                }
                            }
                        }
                    }

                    // Animation
                    var animations = _currentPrefabRoot.GetComponentsInChildren<Animation>(true);
                    foreach (var animation in animations)
                    {
                        var clipNames = new List<string>();
                        foreach (AnimationState state in animation)
                        {
                            if (state.clip != null)
                            {
                                var clipPath = AssetDatabase.GetAssetPath(state.clip);
                                if (clipPath == info.AssetPath)
                                {
                                    clipNames.Add(state.name);
                                }
                            }
                        }

                        foreach (var clipName in clipNames)
                        {
                            Undo.RecordObject(animation, "Replace AnimationClip");
                            animation.RemoveClip(clipName);
                            animation.AddClip(replacementClip, clipName);
                            EditorUtility.SetDirty(animation);
                        }
                    }
                }
            }
            // AudioClipの場合
            else if (info.AssetType == "Audio")
            {
                var replacementClip = replacementAsset as AudioClip;
                if (replacementClip != null)
                {
                    // AudioSource
                    var audioSources = _currentPrefabRoot.GetComponentsInChildren<AudioSource>(true);
                    foreach (var audioSource in audioSources)
                    {
                        if (audioSource.clip != null)
                        {
                            var clipPath = AssetDatabase.GetAssetPath(audioSource.clip);
                            if (clipPath == info.AssetPath)
                            {
                                Undo.RecordObject(audioSource, "Replace AudioClip");
                                audioSource.clip = replacementClip;
                                EditorUtility.SetDirty(audioSource);
                            }
                        }
                    }
                }
            }
            // Fontの場合
            else if (info.AssetType == "Font")
            {
                var replacementFont = replacementAsset as Font;
                if (replacementFont != null)
                {
                    // Text (Legacy)
                    var texts = _currentPrefabRoot.GetComponentsInChildren<Text>(true);
                    foreach (var text in texts)
                    {
                        if (text.font != null)
                        {
                            var fontPath = AssetDatabase.GetAssetPath(text.font);
                            if (fontPath == info.AssetPath)
                            {
                                Undo.RecordObject(text, "Replace Font");
                                text.font = replacementFont;
                                EditorUtility.SetDirty(text);
                            }
                        }
                    }
                }
            }
            // Materialの場合
            else if (info.AssetType == "Material")
            {
                var replacementMaterial = replacementAsset as Material;
                if (replacementMaterial != null)
                {
                    // Renderer
                    var renderers = _currentPrefabRoot.GetComponentsInChildren<Renderer>(true);
                    foreach (var renderer in renderers)
                    {
                        var materials = renderer.sharedMaterials;
                        var modified = false;
                        for (int i = 0; i < materials.Length; i++)
                        {
                            if (materials[i] != null)
                            {
                                var matPath = AssetDatabase.GetAssetPath(materials[i]);
                                if (matPath == info.AssetPath)
                                {
                                    Undo.RecordObject(renderer, "Replace Material");
                                    materials[i] = replacementMaterial;
                                    modified = true;
                                }
                            }
                        }
                        if (modified)
                        {
                            renderer.sharedMaterials = materials;
                            EditorUtility.SetDirty(renderer);
                        }
                    }

                    // Image
                    var images = _currentPrefabRoot.GetComponentsInChildren<Image>(true);
                    foreach (var image in images)
                    {
                        if (image.material != null && image.material != image.defaultMaterial)
                        {
                            var matPath = AssetDatabase.GetAssetPath(image.material);
                            if (matPath == info.AssetPath)
                            {
                                Undo.RecordObject(image, "Replace Material");
                                image.material = replacementMaterial;
                                EditorUtility.SetDirty(image);
                            }
                        }
                    }
                }
            }
        }

        void ReplaceAssetReference(string referencedByFileName, DependencyInfo info, Object replacementAsset)
        {
            // 参照元アセットを検索
            var guids = AssetDatabase.FindAssets(Path.GetFileNameWithoutExtension(referencedByFileName));
            foreach (var guid in guids)
            {
                var assetPath = AssetDatabase.GUIDToAssetPath(guid);
                if (Path.GetFileName(assetPath) != referencedByFileName) continue;

                // マテリアルの場合
                if (assetPath.EndsWith(".mat"))
                {
                    var material = AssetDatabase.LoadAssetAtPath<Material>(assetPath);
                    if (material != null)
                    {
                        // Texture置換の場合
                        if (info.AssetType == "Texture" || info.AssetType == "Sprite")
                        {
                            ReplaceMaterialTexture(material, info.AssetPath, replacementAsset as Texture);
                        }
                        // Shader置換の場合
                        else if (info.AssetType == "Shader")
                        {
                            var replacementShader = replacementAsset as Shader;
                            if (replacementShader != null && material.shader != null)
                            {
                                var shaderPath = AssetDatabase.GetAssetPath(material.shader);
                                if (shaderPath == info.AssetPath)
                                {
                                    Undo.RecordObject(material, "Replace Shader");
                                    material.shader = replacementShader;
                                    EditorUtility.SetDirty(material);
                                }
                            }
                        }
                    }
                }
                // AnimatorControllerの場合
                else if (assetPath.EndsWith(".controller"))
                {
                    if (info.AssetType == "Animation")
                    {
                        Debug.LogWarning(
                            $"AnimatorController内のAnimationClipは手動で置換してください: {assetPath}\n" +
                            $"置換元: {info.AssetPath}\n" +
                            $"置換先: {AssetDatabase.GetAssetPath(replacementAsset)}");
                    }
                }
                // Animationアセットの場合（.animファイルがTextureやSpriteを参照しているケース）
                else if (assetPath.EndsWith(".anim"))
                {
                    if (info.AssetType == "Texture" || info.AssetType == "Sprite")
                    {
                        ReplaceAnimationClipSprite(assetPath, info.AssetPath, replacementAsset);
                    }
                }
            }
        }

        void ReplaceAnimationClipSprite(string animationClipPath, string sourceSpriteAssetPath, Object replacementAsset)
        {
            var animationClip = AssetDatabase.LoadAssetAtPath<AnimationClip>(animationClipPath);
            if (animationClip == null) return;

            // 置換先のSpriteを取得
            var replacementSprite = replacementAsset as Sprite;
            if (replacementSprite == null)
            {
                var texture = replacementAsset as Texture2D;
                if (texture != null)
                {
                    replacementSprite = GetSpriteFromTexture(texture);
                }
            }

            if (replacementSprite == null)
            {
                Debug.LogWarning($"置換先のSpriteが取得できませんでした: {AssetDatabase.GetAssetPath(replacementAsset)}");
                return;
            }

            // AnimationClipの全バインディングを取得
            var bindings = AnimationUtility.GetObjectReferenceCurveBindings(animationClip);
            var modified = false;

            foreach (var binding in bindings)
            {
                // Image.spriteまたはSpriteRenderer.spriteのバインディングを確認
                if (binding.propertyName == "m_Sprite")
                {
                    var curve = AnimationUtility.GetObjectReferenceCurve(animationClip, binding);
                    if (curve == null) continue;

                    var newCurve = new ObjectReferenceKeyframe[curve.Length];
                    for (int i = 0; i < curve.Length; i++)
                    {
                        newCurve[i] = curve[i];
                        if (newCurve[i].value != null)
                        {
                            var spritePath = AssetDatabase.GetAssetPath(newCurve[i].value);
                            if (spritePath == sourceSpriteAssetPath)
                            {
                                newCurve[i].value = replacementSprite;
                                modified = true;
                            }
                        }
                    }

                    if (modified)
                    {
                        AnimationUtility.SetObjectReferenceCurve(animationClip, binding, newCurve);
                    }
                }
            }

            if (modified)
            {
                EditorUtility.SetDirty(animationClip);
                Debug.Log($"AnimationClip内のSpriteを置換しました: {animationClipPath}");
            }
        }

        void ReplaceMaterialTexture(Material material, string sourceTexturePath, Texture replacementTexture)
        {
            if (material == null || replacementTexture == null) return;

            var shader = material.shader;
            var propertyCount = ShaderUtil.GetPropertyCount(shader);

            for (int i = 0; i < propertyCount; i++)
            {
                if (ShaderUtil.GetPropertyType(shader, i) != ShaderUtil.ShaderPropertyType.TexEnv) continue;

                var propertyName = ShaderUtil.GetPropertyName(shader, i);
                var texture = material.GetTexture(propertyName);
                if (texture == null) continue;

                var texturePath = AssetDatabase.GetAssetPath(texture);
                if (texturePath == sourceTexturePath)
                {
                    Undo.RecordObject(material, "Replace Material Texture");
                    material.SetTexture(propertyName, replacementTexture);
                    EditorUtility.SetDirty(material);
                }
            }
        }

        Sprite GetSpriteFromTexture(Texture2D texture)
        {
            var path = AssetDatabase.GetAssetPath(texture);
            var allAssets = AssetDatabase.LoadAllAssetsAtPath(path);
            foreach (var asset in allAssets)
            {
                if (asset is Sprite sprite)
                {
                    return sprite;
                }
            }
            return null;
        }

        string GetGameObjectPath(Transform transform)
        {
            var path = transform.name;
            while (transform.parent != null)
            {
                transform = transform.parent;
                path = transform.name + "/" + path;
            }
            return path;
        }

        static bool IsRemoteAddressableAssetGroup(AddressableAssetGroup group)
        {
            if (group == null) return false;

            var bundledAssetGroupSchema = group.GetSchema<BundledAssetGroupSchema>();
            if (bundledAssetGroupSchema == null) return false;

            var buildName = bundledAssetGroupSchema.BuildPath.GetName(group.Settings);
            var loadName = bundledAssetGroupSchema.LoadPath.GetName(group.Settings);

            return buildName.Equals(AddressableAssetSettings.kRemoteBuildPath) &&
                   loadName.Equals(AddressableAssetSettings.kRemoteLoadPath);
        }

        void DisplayMissingResults()
        {
            EditorGUILayout.Space();
            EditorGUILayout.LabelField($"■ Missing参照: {_missingReferenceList.Count}件", EditorStyles.boldLabel);

            EditorGUILayout.HelpBox(
                "以下のコンポーネントでMissing参照が検出されました。\n参照先のアセットが削除または移動されている可能性があります。",
                MessageType.Error);

            foreach (var info in _missingReferenceList)
            {
                EditorGUILayout.BeginVertical(EditorStyles.helpBox);

                EditorGUILayout.BeginHorizontal();

                // ヒエラルキーパスを表示
                EditorGUILayout.LabelField($"[MISSING] {info.HierarchyPath}", EditorStyles.wordWrappedLabel);

                // 選択ボタン
                if (GUILayout.Button("選択", GUILayout.Width(50)))
                {
                    Selection.activeGameObject = info.GameObject;
                    EditorGUIUtility.PingObject(info.GameObject);
                }

                EditorGUILayout.EndHorizontal();

                // コンポーネント情報
                EditorGUILayout.LabelField(
                    $"コンポーネント: {info.ComponentType}, プロパティ: {info.PropertyName}",
                    EditorStyles.miniLabel);

                EditorGUILayout.EndVertical();
            }
        }
    }


    /// <summary>
    /// Missing参照情報を保持するクラス
    /// </summary>
    class MissingReferenceInfo
    {
        public GameObject GameObject { get; set; }
        public string HierarchyPath { get; set; }
        public string ComponentType { get; set; }
        public string PropertyName { get; set; }
    }

    /// <summary>
    /// 依存アセット情報を保持するクラス
    /// </summary>
    class DependencyInfo
    {
        public string AssetPath { get; set; }
        public string AssetName { get; set; }
        public string AssetType { get; set; }
        public int AssetTypeCategory { get; set; }
        public List<string> ReferencedBy { get; set; } = new();
        public bool IsRemoteAddressable { get; set; }
        public bool HasReplacement { get; set; }
        public string ReplacementPath { get; set; }
    }
}
