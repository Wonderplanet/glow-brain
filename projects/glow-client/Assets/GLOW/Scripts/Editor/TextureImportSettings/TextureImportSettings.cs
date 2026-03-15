using System.Collections.Generic;
using System.IO;
using UnityEditor;
using UnityEngine;

namespace GLOW.Editor.TextureImportSettings
{
    /// <summary>
    /// UI_NoAtlas以下のPNGファイルのインポート設定を一括変更するエディタ拡張
    /// </summary>
    public class UI_NoAtlasTextureImporter : EditorWindow
    {
        enum CompressionQualityOption
        {
            NormalQuality = 50,
            HighQuality = 100,
        }

        enum TargetPathOption
        {
            UI_NoAtlas,
            InGame,
        }

        static readonly Dictionary<TargetPathOption, string> TargetPathMap = new Dictionary<TargetPathOption, string>
        {
            { TargetPathOption.UI_NoAtlas, "Assets/GLOW/Graphics/UI_NoAtlas" },
            { TargetPathOption.InGame, "Assets/GLOW/Graphics/Textures/InGame" },
        };

        Vector2 scrollPosition;
        List<string> targetTextures = new List<string>();
        bool isScanned = false;
        CompressionQualityOption selectedQuality = CompressionQualityOption.NormalQuality;
        bool applyAndroidAstc4x4 = false;
        TargetPathOption selectedTargetPath = TargetPathOption.UI_NoAtlas;

        [MenuItem("GLOW/Texture Tools/UI_NoAtlas Texture Importer")]
        static void Open()
        {
            var window = GetWindow<UI_NoAtlasTextureImporter>("UI_NoAtlas Texture Importer");
            window.Show();
        }

        void OnGUI()
        {
            EditorGUILayout.LabelField("UI_NoAtlas Texture Import Settings", EditorStyles.boldLabel);
            EditorGUILayout.Space();

            EditorGUILayout.HelpBox(
                "このツールは以下の設定を適用します:\n" +
                "- Use Crunch Compression: ON\n" +
                $"- Compression Quality: {selectedQuality} ({(int)selectedQuality})\n" +
                "- Texture Compression: Compressed\n" +
                (applyAndroidAstc4x4 ? "- [Android] textureFormat: ASTC 4x4 (overridden: ON)" : ""),
                MessageType.Info
            );

            EditorGUILayout.Space();

            selectedQuality = (CompressionQualityOption)EditorGUILayout.EnumPopup("Compression Quality", selectedQuality);
            applyAndroidAstc4x4 = EditorGUILayout.Toggle("Android: ASTC 4x4 に設定", applyAndroidAstc4x4);
            selectedTargetPath = (TargetPathOption)EditorGUILayout.EnumPopup("対象パス", selectedTargetPath);
            EditorGUILayout.LabelField(TargetPathMap[selectedTargetPath], EditorStyles.miniLabel);

            EditorGUILayout.Space();

            if (GUILayout.Button("対象テクスチャをスキャン", GUILayout.Height(30)))
            {
                ScanTextures();
            }

            if (isScanned)
            {
                EditorGUILayout.Space();
                EditorGUILayout.LabelField($"対象ファイル数: {targetTextures.Count}件");

                scrollPosition = EditorGUILayout.BeginScrollView(scrollPosition, GUILayout.Height(300));
                foreach (var texturePath in targetTextures)
                {
                    EditorGUILayout.LabelField(texturePath);
                }
                EditorGUILayout.EndScrollView();

                EditorGUILayout.Space();

                if (targetTextures.Count > 0)
                {
                    if (GUILayout.Button("設定を適用", GUILayout.Height(40)))
                    {
                        ApplySettings();
                    }
                }
            }
        }

        void ScanTextures()
        {
            targetTextures.Clear();

            var targetPath = TargetPathMap[selectedTargetPath];

            if (!Directory.Exists(targetPath))
            {
                EditorUtility.DisplayDialog("エラー", $"指定されたパスが存在しません:\n{targetPath}", "OK");
                return;
            }

            // 対象パス以下のすべてのPNGファイルを検索
            var guids = AssetDatabase.FindAssets("t:Texture2D", new[] { targetPath });

            foreach (var guid in guids)
            {
                var path = AssetDatabase.GUIDToAssetPath(guid);
                if (path.EndsWith(".png", System.StringComparison.OrdinalIgnoreCase))
                {
                    targetTextures.Add(path);
                }
            }

            isScanned = true;
            Repaint();
        }

        void ApplySettings()
        {
            if (targetTextures.Count == 0)
            {
                EditorUtility.DisplayDialog("エラー", "対象のテクスチャがありません", "OK");
                return;
            }

            var result = EditorUtility.DisplayDialog(
                "確認",
                $"{targetTextures.Count}個のテクスチャに設定を適用します。\nよろしいですか？",
                "はい",
                "キャンセル"
            );

            if (!result)
            {
                return;
            }

            var successCount = 0;
            var failCount = 0;

            try
            {
                AssetDatabase.StartAssetEditing();

                for (var i = 0; i < targetTextures.Count; i++)
                {
                    var texturePath = targetTextures[i];

                    EditorUtility.DisplayProgressBar(
                        "テクスチャ設定を適用中",
                        $"{i + 1}/{targetTextures.Count}: {Path.GetFileName(texturePath)}",
                        (float)i / targetTextures.Count
                    );

                    if (ApplyTextureSettings(texturePath))
                    {
                        successCount++;
                    }
                    else
                    {
                        failCount++;
                    }
                }
            }
            finally
            {
                AssetDatabase.StopAssetEditing();
                EditorUtility.ClearProgressBar();
            }

            AssetDatabase.SaveAssets();
            AssetDatabase.Refresh();

            EditorUtility.DisplayDialog(
                "完了",
                $"設定の適用が完了しました。\n成功: {successCount}件\n失敗: {failCount}件",
                "OK"
            );
        }

        bool ApplyTextureSettings(string assetPath)
        {
            try
            {
                var importer = AssetImporter.GetAtPath(assetPath) as TextureImporter;
                if (importer == null)
                {
                    Debug.LogWarning($"TextureImporterを取得できませんでした: {assetPath}");
                    return false;
                }

                // Use Crunch Compression を ON に設定
                importer.crunchedCompression = true;

                // Compression Quality を選択された品質に設定
                importer.compressionQuality = (int)selectedQuality;

                // textureCompression: 1 (Compressed) を DefaultTexturePlatform に設定
                var platformSettings = importer.GetDefaultPlatformTextureSettings();
                platformSettings.textureCompression = TextureImporterCompression.Compressed;
                importer.SetPlatformTextureSettings(platformSettings);

                // Android: RGB(A) Compressed ASTC 4x4 に設定
                if (applyAndroidAstc4x4)
                {
                    var androidSettings = importer.GetPlatformTextureSettings("Android");
                    androidSettings.overridden = true;
                    androidSettings.maxTextureSize = 2048;
                    androidSettings.format = TextureImporterFormat.ASTC_4x4;
                    androidSettings.textureCompression = TextureImporterCompression.Compressed;
                    androidSettings.compressionQuality = (int)selectedQuality;
                    androidSettings.crunchedCompression = false;
                    androidSettings.allowsAlphaSplitting = false;
                    importer.SetPlatformTextureSettings(androidSettings);
                }

                // 設定を保存
                importer.SaveAndReimport();

                return true;
            }
            catch (System.Exception e)
            {
                Debug.LogError($"テクスチャ設定の適用に失敗しました: {assetPath}\n{e.Message}");
                return false;
            }
        }
    }
}
