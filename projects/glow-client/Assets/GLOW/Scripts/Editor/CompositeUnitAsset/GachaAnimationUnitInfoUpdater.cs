using System.Collections.Generic;
using System.IO;
using System.Text;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using UnityEditor;
using UnityEngine;

namespace GLOW.Editor.CompositeUnitAsset
{
    public class GachaAnimationUnitInfoUpdater : UnityEditor.Editor
    {
        [MenuItem("GLOW/GachaAnimationUnitInfo一括更新/TechNameSprite設定")]
        static void UpdateAllTechNameSprites()
        {
            var guids = AssetDatabase.FindAssets("t:GachaAnimationUnitInfo");
            var updatedCount = 0;
            var skippedCount = 0;
            var multipleImagesAssets = new Dictionary<string, List<string>>();

            foreach (var guid in guids)
            {
                var path = AssetDatabase.GUIDToAssetPath(guid);
                var asset = AssetDatabase.LoadAssetAtPath<GachaAnimationUnitInfo>(path);

                if (asset == null)
                {
                    continue;
                }

                var assetKey = ExtractAssetKeyFromPath(path);

                if (string.IsNullOrEmpty(assetKey))
                {
                    Debug.LogWarning($"[GachaAnimationUnitInfoUpdater] Could not extract assetKey from: {path}");
                    skippedCount++;
                    continue;
                }

                var spritePath = GachaAnimationUnitInfoGenerator.GetTechNameSpritePathWithFallback(
                    assetKey,
                    out var fallbackImages);

                // 複数画像が見つかった場合は記録してスキップ
                if (fallbackImages != null && fallbackImages.Count > 1)
                {
                    multipleImagesAssets[assetKey] = fallbackImages;
                    skippedCount++;
                    continue;
                }

                if (string.IsNullOrEmpty(spritePath))
                {
                    Debug.LogWarning($"[GachaAnimationUnitInfoUpdater] TechNameSprite not found for: {assetKey}");
                    skippedCount++;
                    continue;
                }

                var sprite = AssetDatabase.LoadAssetAtPath<Sprite>(spritePath);

                if (sprite != null)
                {
                    asset.TechNameSprite = sprite;
                    EditorUtility.SetDirty(asset);
                    updatedCount++;
                    Debug.Log($"[GachaAnimationUnitInfoUpdater] Updated: {path} with {spritePath}");
                }
                else
                {
                    skippedCount++;
                }
            }

            AssetDatabase.SaveAssets();
            AssetDatabase.Refresh();

            Debug.Log($"[GachaAnimationUnitInfoUpdater] 完了 - 更新: {updatedCount}件, スキップ: {skippedCount}件");

            // 複数画像が見つかったアセットをまとめて出力
            if (multipleImagesAssets.Count > 0)
            {
                var sb = new StringBuilder();
                sb.AppendLine("[GachaAnimationUnitInfoUpdater] 以下のアセットは複数のTechName画像が見つかったため手動設定が必要です:");

                foreach (var kvp in multipleImagesAssets)
                {
                    sb.AppendLine($"\n  assetKey: {kvp.Key}");
                    sb.AppendLine("  候補:");

                    foreach (var imagePath in kvp.Value)
                    {
                        sb.AppendLine($"    - {imagePath}");
                    }
                }

                Debug.LogWarning(sb.ToString());
            }
        }

        static string ExtractAssetKeyFromPath(string path)
        {
            var fileName = Path.GetFileNameWithoutExtension(path);
            var prefix = "gacha_animation_unit_info_";

            if (fileName.StartsWith(prefix))
            {
                return fileName.Substring(prefix.Length);
            }

            return string.Empty;
        }
    }
}

